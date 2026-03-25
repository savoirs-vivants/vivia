<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Activite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\RecupNumeroMail;
use App\Models\Adherent;
use Illuminate\Support\Facades\Log;

class AdherentFormulaireController extends Controller
{
    public function index(Request $request)
    {
        $token = bin2hex(random_bytes(16));
        $request->session()->put("adhesion_{$token}", [
            '_last_completed' => 0,
            'created_at' => now()
        ]);

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 1]);
    }

    private function isMineur(?string $dateNaiss): bool
    {
        if (empty($dateNaiss)) {
            return false;
        }
        try {
            return Carbon::parse($dateNaiss)->age < 18;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getUserPath(array $formData): array
    {
        $isAdherent    = ($formData['is_adherent']  ?? 'non') === 'oui';
        $activite      =  $formData['type_activite'] ?? '';
        $isMineur      = $this->isMineur($formData['date_naiss'] ?? null);
        $isClubMaker   = ($activite === 'club_maker');
        $needsActivite = in_array($activite, ['atelier', 'stage']);

        if ($isAdherent) {
            $path = [1, 2];

            if ($needsActivite) {
                $path[] = 6;
            }

            if (! $isClubMaker) {
                $path[] = 10;
            }

            $path[] = 11;
            return $path;
        }

        $path = [1, 2, 3];
        if ($isMineur) {
            $path[] = 4;
        }
        $path[] = 5;
        if ($needsActivite) {
            $path[] = 6;
        }
        $path[] = 7;
        if ($isMineur) {
            $path[] = 8;
        }
        $path[] = 9;
        if (! $isClubMaker) {
            $path[] = 10;
        }
        $path[] = 11;

        return $path;
    }

    private function getNextStep(int $current, array $formData): int
    {
        $path = $this->getUserPath($formData);
        $idx  = array_search($current, $path);

        return isset($path[$idx + 1]) ? $path[$idx + 1] : 11;
    }

    private function getPrevStep(int $current, array $formData): int
    {
        $path = $this->getUserPath($formData);
        $idx  = array_search($current, $path);

        return ($idx > 0) ? $path[$idx - 1] : 1;
    }

    private function stepMeta(): array
    {
        return [
            1  => ['label' => 'Statut',        'icon' => '👤'],
            2  => ['label' => 'Activité',       'icon' => '🎯'],
            3  => ['label' => 'Informations',   'icon' => '📋'],
            4  => ['label' => 'Santé',          'icon' => '🏥'],
            5  => ['label' => 'Occupation',     'icon' => '💼'],
            6  => ['label' => 'Choix activité', 'icon' => '📅'],
            7  => ['label' => 'Bénévolat',      'icon' => '🤝'],
            8  => ['label' => 'Tuteurs',        'icon' => '👨‍👩‍👧'],
            9  => ['label' => 'Signature',      'icon' => '✍️'],
            10 => ['label' => 'Paiement',       'icon' => '💳'],
            11 => ['label' => 'Confirmation',   'icon' => '✅'],
        ];
    }

    public function show(Request $request, string $token)
    {
        if (!$request->session()->has("adhesion_{$token}")) {
            return redirect()->route('adhesion.index');
        }
        abort_if(empty($token), 403, 'Lien invalide ou expiré.');

        $step     = max(1, min(11, (int) $request->query('step', 1)));
        $formData = $request->session()->get("adhesion_{$token}", []);

        $path    = $this->getUserPath($formData);
        $maxDone = (int) ($formData['_last_completed'] ?? 0);

        $allowedIdx = 0;
        foreach ($path as $idx => $p) {
            if ($p <= $maxDone) {
                $allowedIdx = $idx + 1;
            }
        }

        $allowedIdx = min($allowedIdx, count($path) - 1);
        $requestedIdx = array_search($step, $path);

        if ($requestedIdx === false || $requestedIdx > $allowedIdx) {
            $fallbackStep = $path[$allowedIdx];
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => $fallbackStep]);
        }

        $activites = Activite::where('is_archived', false)->get();
        $ateliers  = $activites->where('type', 'activite')->values();
        $stages    = $activites->where('type', 'stage')->values();

        $stepMeta   = $this->stepMeta();
        $isMineur   = $this->isMineur($formData['date_naiss'] ?? null);
        $currentNum = $requestedIdx + 1;
        $totalSteps = count($path);
        $prevStep   = $this->getPrevStep($step, $formData);
        $hasPrev    = ($step !== 1);

        return view('adhesion', compact(
            'step',
            'formData',
            'token',
            'ateliers',
            'stages',
            'path',
            'stepMeta',
            'isMineur',
            'currentNum',
            'totalSteps',
            'prevStep',
            'hasPrev'
        ));
    }

    public function envoyerCodeRecup(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $adherent = Adherent::where('mail', $request->email)->first();

            if ($adherent) {
                $code = strtoupper(Str::random(6));
                Cache::put("recup_adherent_{$code}", $adherent->numero_adherent, now()->addMinutes(30));
                Mail::to($adherent->mail)->send(new RecupNumeroMail($adherent, $code));
            }

            return response()->json([
                'message' => 'Si cet email est associé à un compte, un code a été envoyé.',
                'status'  => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('envoyerCodeRecup error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Une erreur serveur est survenue. Veuillez réessayer.',
                'status'  => 'error'
            ], 500);
        }
    }

    public function next(Request $request, string $token)
    {
        abort_if(empty($token), 403, 'Lien invalide ou expiré.');

        $step     = (int) $request->input('current_step', 1);
        $formData = $request->session()->get("adhesion_{$token}", []);

        if ($step === 1 && $request->input('is_adherent') === 'oui') {
            $inputNumero = trim($request->input('numero_adherent'));

            $vraiNumero = Cache::get("recup_adherent_{$inputNumero}");

            $numeroRecherche = $vraiNumero ? $vraiNumero : $inputNumero;

            $adherentExistant = Adherent::where('numero_adherent', $numeroRecherche)->first();

            if (!$adherentExistant) {
                return back()->withErrors(['numero_adherent' => 'Ce numéro ou code temporaire est introuvable.']);
            }

            $request->merge(['numero_adherent' => $adherentExistant->numero_adherent]);

            $formData['nom'] = $adherentExistant->nom;
            $formData['prenom'] = $adherentExistant->prenom;
            $formData['mail'] = $adherentExistant->mail;
        }

        if ($request->hasFile('carnet_sante')) {
            $filePath = $request->file('carnet_sante')->store('carnets', 'public');
            $formData['carnet_sante_path'] = $filePath;
        }

        $exclude  = ['_token', 'current_step', 'carnet_sante'];
        $newData  = $request->except($exclude);
        $formData = array_merge($formData, $newData);

        $formData['_last_completed'] = max((int) ($formData['_last_completed'] ?? 0), $step);

        $request->session()->put("adhesion_{$token}", $formData);

        $next = $this->getNextStep($step, $formData);

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => $next]);
    }
}
