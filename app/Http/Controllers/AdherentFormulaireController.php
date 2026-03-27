<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Activite;
use App\Models\Ressourcerie;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\RecupNumeroMail;
use App\Models\Adherent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\HelloAssoService;

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
        $isClubMaker      = ($activite === 'club_maker');
        $isRessourcerie   = ($activite === 'ressourcerie');
        $needsActivite    = in_array($activite, ['atelier', 'stage', 'ressourcerie']);

        if ($isAdherent) {
            $path = [1, 12, 2];
            if ($needsActivite) $path[] = 6;
            if (! $isClubMaker) $path[] = 10;
            $path[] = 11;
            return $path;
        }

        $path = [1, 12, 2, 3];
        if ($isMineur) $path[] = 4;
        $path[] = 5;
        if ($needsActivite) $path[] = 6;
        $path[] = 7;
        if ($isMineur) $path[] = 8;
        $path[] = 9;
        if (! $isClubMaker) $path[] = 10;
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

    private function classeDepuisAge(array $formData): ?string
    {
        $dateNaiss = $formData['date_naiss'] ?? null;
        if (empty($dateNaiss)) return null;

        $anneeNaissance = (int) Carbon::parse($dateNaiss)->format('Y');
        $now = now();
        $anneeScolaire = $now->month >= 9 ? $now->year : $now->year - 1;
        $ageScolaire   = $anneeScolaire - $anneeNaissance;

        return match(true) {
            $ageScolaire === 3  => 'PS',
            $ageScolaire === 4  => 'MS',
            $ageScolaire === 5  => 'GS',
            $ageScolaire === 6  => 'CP',
            $ageScolaire === 7  => 'CE1',
            $ageScolaire === 8  => 'CE2',
            $ageScolaire === 9  => 'CM1',
            $ageScolaire === 10 => 'CM2',
            $ageScolaire === 11 => '6ème',
            $ageScolaire === 12 => '5ème',
            $ageScolaire === 13 => '4ème',
            $ageScolaire === 14 => '3ème',
            $ageScolaire === 15 => 'Seconde',
            $ageScolaire === 16 => 'Première',
            $ageScolaire === 17 => 'Terminale',
            $ageScolaire >= 18  => 'Adulte',
            default             => null,
        };
    }

    private function classesFiltrer(array $formData): \Closure
    {
        $classe = $this->classeDepuisAge($formData);
        if ($classe === null) return fn() => true;

        $classesEligibles = $classe === 'Adulte' ? ['Adulte', 'Senior'] : [$classe];
        return function ($activite) use ($classesEligibles) {
            if (empty($activite->classes)) return true;
            return count(array_intersect($activite->classes, $classesEligibles)) > 0;
        };
    }

    private function stepMeta(): array
    {
        return [
            1  => ['label' => 'Bienvenue',      'icon' => '👤'],
            12 => ['label' => 'Profil',         'icon' => '🏢'],
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

        $rawStep  = (int) $request->query('step', 1);
        $step     = in_array($rawStep, array_keys($this->stepMeta())) ? $rawStep : 1;
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

        $activitesDejaInscritesIds = [];
        if (($formData['is_adherent'] ?? 'non') === 'oui' && !empty($formData['numero_adherent'])) {
            $adherentExistant = Adherent::where('numero_adherent', $formData['numero_adherent'])->first();
            if ($adherentExistant) {
                if (empty($formData['date_naiss'])) {
                    $formData['date_naiss'] = $adherentExistant->date_naiss?->format('Y-m-d');
                }
                $year   = now()->month >= 9 ? now()->year : now()->year - 1;
                $saison = $year . '-' . ($year + 1);
                $activitesDejaInscritesIds = $adherentExistant->activites()
                    ->wherePivot('saison', $saison)
                    ->wherePivot('est_un_abandon', 0)
                    ->pluck('activites.id')
                    ->toArray();
            }
        }

        $classeAdherent = $this->classeDepuisAge($formData);
        $filtre         = $this->classesFiltrer($formData);

        $activites = Activite::where('is_archived', false)->get();
        $ateliers  = $activites->where('type', 'activite')->values()
            ->filter($filtre)
            ->filter(fn($a) => !in_array($a->id, $activitesDejaInscritesIds))
            ->values();
        $stages    = $activites->where('type', 'stage')->values()
            ->filter($filtre)
            ->filter(fn($a) => !in_array($a->id, $activitesDejaInscritesIds))
            ->values();

        $statutJuridique = $formData['statut_juridique'] ?? null;
        $tarifsRessourcerie = match($statutJuridique) {
            'personne_physique' => ['tarif_particulier'],
            'tpe_asso', 'esr_pme' => ['tarif_structure', 'tarif_scolaire'],
            default => ['tarif_particulier', 'tarif_structure', 'tarif_scolaire'],
        };
        $ressourcerie = Ressourcerie::actifs()
            ->whereIn('type_tarif', $tarifsRessourcerie)
            ->get();

        $stepMeta    = $this->stepMeta();
        $isMineur    = $this->isMineur($formData['date_naiss'] ?? null);
        $currentNum  = $requestedIdx + 1;
        $totalSteps  = count($path);
        $prevStep    = $this->getPrevStep($step, $formData);
        $hasPrev     = ($step !== 1);
        $paiement1Done = (bool) $request->session()->pull("paiement1_done_{$token}", false);

        return view('adhesion', compact(
            'step', 'formData', 'token', 'ateliers', 'stages', 'ressourcerie',
            'path', 'stepMeta', 'isMineur', 'currentNum', 'totalSteps',
            'prevStep', 'hasPrev', 'classeAdherent', 'paiement1Done'
        ));
    }

    public function envoyerCodeRecup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'L\'adresse email n\'est pas valide.',
                'status'  => 'error'
            ], 422);
        }

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
        }

        if ($step === 10 && $request->input('mode_paiement') === 'helloasso') {
            $service = new HelloAssoService();

            $activitesIds      = $formData['activites_selectionnees'] ?? [];
            $hasActivites      = !empty($activitesIds);
            $estNouvelAdherent = ($formData['is_adherent'] ?? 'non') === 'non';

            $totalActiviteEuros = 0;
            if ($hasActivites) {
                $activites = \App\Models\Activite::whereIn('id', $activitesIds)->get();
                $totalActiviteEuros = $activites->sum('tarif');
            }

            $formData['mode_paiement']   = 'helloasso';
            $formData['_last_completed'] = 10;
            $request->session()->put("adhesion_{$token}", $formData);

            $payerInfo = [
                'prenom' => $formData['prenom'] ?? 'Prénom',
                'nom'    => $formData['nom'] ?? 'Nom',
                'mail'   => $formData['mail'] ?? 'email@defaut.fr',
            ];

            if ($totalActiviteEuros > 0) {
                try {
                    $urlPaiement = $service->createCheckout(
                        (int) round($totalActiviteEuros * 100),
                        $payerInfo,
                        $token,
                        'adhesion.helloasso.return',
                        'Inscription Activité - Savoirs Vivants'
                    );
                    return redirect($urlPaiement);
                } catch (\Exception $e) {
                    Log::error('HelloAsso Error (Checkout 1): ' . $e->getMessage());
                    return back()->withErrors(['helloasso' => 'Le service de paiement est indisponible pour le moment.']);
                }
            }
            elseif ($estNouvelAdherent) {
                try {
                    $formSlug = env('HELLOASSO_MEMBERSHIP_FORM_SLUG');
                    $price    = $service->getBaseMembershipPrice($formSlug);

                    if ($price > 0) {
                        $urlPaiement = $service->createCheckout(
                            (int) round($price * 100),
                            $payerInfo,
                            $token,
                            'adhesion.helloasso2.return',
                            'Adhésion Annuelle - Savoirs Vivants'
                        );
                        return redirect($urlPaiement);
                    }
                } catch (\Exception $e) {
                    Log::error('HelloAsso Error (Checkout 2 direct): ' . $e->getMessage());
                    return back()->withErrors(['helloasso' => 'Le service de paiement est indisponible pour le moment.']);
                }
            }

            $formData['_last_completed'] = 11;
            $request->session()->put("adhesion_{$token}", $formData);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
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

    public function helloassoReturn(Request $request, $token, $status)
    {
        if ($status === 'cancel' || $status === 'error') {
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10])
                             ->withErrors(['helloasso' => 'Le paiement de l\'activité a été annulé ou a échoué. Vous pouvez réessayer.']);
        }

        $formData = $request->session()->get("adhesion_{$token}", []);
        $formData['_last_completed'] = max((int)($formData['_last_completed'] ?? 0), 10);
        $request->session()->put("adhesion_{$token}", $formData);

        $estNouvelAdherent = ($formData['is_adherent'] ?? 'non') === 'non';

        if ($estNouvelAdherent) {
            $request->session()->put("paiement1_done_{$token}", true);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
        }

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }

    public function helloassoCheckout2(Request $request, string $token)
    {
        abort_if(!$request->session()->has("adhesion_{$token}"), 403);

        $isSandbox  = config('services.helloasso.sandbox', true);
        $basePublic = $isSandbox
            ? 'https://www.helloasso-sandbox.com'
            : 'https://www.helloasso.com';
        $orgSlug  = config('services.helloasso.org_slug');
        $formSlug = env('HELLOASSO_MEMBERSHIP_FORM_SLUG');

        $url = "{$basePublic}/associations/{$orgSlug}/adhesions/{$formSlug}";

        Log::info("HelloAsso checkout2 : redirection vers la page officielle d'adhésion", ['url' => $url]);

        return redirect($url);
    }

    public function helloassoReturn2(Request $request, $token, $status)
    {
        if ($status === 'cancel' || $status === 'error') {
            $request->session()->put("paiement1_done_{$token}", true);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10])
                             ->withErrors(['helloasso2' => 'Le paiement de la cotisation a été annulé. Vous pouvez réessayer.']);
        }

        $formData = $request->session()->get("adhesion_{$token}", []);
        $formData['_last_completed'] = 11;
        $request->session()->put("adhesion_{$token}", $formData);

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }
    public function helloassoWebhook(Request $request)
    {
        $payload = $request->all();

        Log::info('HelloAsso webhook reçu', $payload);

        $eventType = $payload['eventType'] ?? null;
        $state     = $payload['data']['state'] ?? null;

        if ($eventType !== 'Payment' || $state !== 'Authorized') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $email     = $payload['data']['payer']['email']  ?? null;
        $firstName = $payload['data']['payer']['firstName'] ?? null;
        $lastName  = $payload['data']['payer']['lastName']  ?? null;
        $amount    = ($payload['data']['amount'] ?? 0) / 100;
        $formSlug  = $payload['data']['order']['formSlug'] ?? null;
        $orderId   = $payload['data']['order']['id'] ?? null;

        Log::info('HelloAsso paiement adhésion reçu', [
            'email'     => $email,
            'nom'       => "{$firstName} {$lastName}",
            'montant'   => $amount,
            'formSlug'  => $formSlug,
            'orderId'   => $orderId,
        ]);

        if ($email) {
            $adherent = \App\Models\Adherent::where('mail', $email)->first();
            if ($adherent) {
                Log::info('HelloAsso webhook : adhérent trouvé', [
                    'id'             => $adherent->id,
                    'numero_adherent'=> $adherent->numero_adherent,
                ]);
            } else {
                Log::warning('HelloAsso webhook : aucun adhérent trouvé pour ' . $email);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
