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
use App\Models\AdherentStructure;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Tuteur;
use Illuminate\Support\Facades\DB;
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

    private function isStructure(array $formData): bool
    {
        return in_array($formData['statut_juridique'] ?? '', ['tpe_asso', 'esr_pme']);
    }

    private function montantStructure(array $formData): int
    {
        return ($formData['statut_juridique'] ?? '') === 'esr_pme' ? 200 : 50;
    }

    private function getUserPath(array $formData): array
    {
        $isAdherent    = ($formData['is_adherent']  ?? 'non') === 'oui';
        $activite      =  $formData['type_activite'] ?? '';
        $isMineur      = $this->isMineur($formData['date_naiss'] ?? null);
        $isClubMaker      = ($activite === 'club_maker');
        $needsActivite    = in_array($activite, ['atelier', 'stage', 'ressourcerie']);

        if ($this->isStructure($formData)) {
            $path = [1, 12, 2];
            if ($activite === 'ressourcerie') $path[] = 6;
            $path = array_merge($path, [13, 14, 9, 10, 11]);
            return $path;
        }

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
        if (! $isClubMaker) $path[] = 7;
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

        return match (true) {
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
            13 => ['label' => 'Structure',      'icon' => '🏛️'],
            14 => ['label' => 'Autorisations',  'icon' => '📜'],
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
        $tarifsRessourcerie = match ($statutJuridique) {
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
        $paiement1Done    = (bool) $request->session()->pull("paiement1_done_{$token}", false);
        $isStructure      = $this->isStructure($formData);
        $montantStructure = $isStructure ? $this->montantStructure($formData) : null;

        $ressourcerieSelectionnees       = null;
        $totalRessourcerieStructure      = null;
        if ($isStructure && ($formData['type_activite'] ?? '') === 'ressourcerie' && empty($formData['_ressourcerie_paid'])) {
            $ids = $formData['ressourcerie_selectionnees'] ?? [];
            $ressourcerieSelectionnees  = Ressourcerie::whereIn('id', $ids)->get();
            $totalRessourcerieStructure = $ressourcerieSelectionnees->sum('prix');
        }

        if ($step === 11) {
            if ($isStructure && empty($formData['_structure_id'])) {
                $structureId = $this->sauvegarderStructure($formData);
                $formData['_structure_id'] = $structureId;
                $request->session()->put("adhesion_{$token}", $formData);
            } elseif (!$isStructure && empty($formData['_adherent_id'])) {
                $adherentId = $this->sauvegarderAdherent($formData);
                $formData['_adherent_id'] = $adherentId;
                $request->session()->put("adhesion_{$token}", $formData);
            }
        }

        return view('adhesion', compact(
            'step',
            'formData',
            'token',
            'ateliers',
            'stages',
            'ressourcerie',
            'path',
            'stepMeta',
            'isMineur',
            'currentNum',
            'totalSteps',
            'prevStep',
            'hasPrev',
            'classeAdherent',
            'paiement1Done',
            'isStructure',
            'montantStructure',
            'ressourcerieSelectionnees',
            'totalRessourcerieStructure'
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

            if ($this->isStructure($formData)) {
                if (empty($formData['_structure_id'])) {
                    $formData['_structure_id'] = $this->sauvegarderStructure($formData);
                }
            } else {
                if (empty($formData['_adherent_id'])) {
                    $formData['_adherent_id'] = $this->sauvegarderAdherent($formData);
                }
            }
            $service = new HelloAssoService();

            $formData['mode_paiement']   = 'helloasso';
            $formData['_last_completed'] = 10;
            $request->session()->put("adhesion_{$token}", $formData);

            if ($this->isStructure($formData)) {
                $typeActivite     = $formData['type_activite'] ?? '';
                $ressourceriePaid = !empty($formData['_ressourcerie_paid']);

                $correspondant = trim($formData['nom_correspondant'] ?? '');
                $parts = preg_split('/\s+/', $correspondant, 2);
                $payerInfo = [
                    'prenom' => $parts[0] ?: 'Correspondant',
                    'nom'    => ($parts[1] ?? '') ?: ($formData['nom_structure'] ?? 'Structure'),
                    'mail'   => $formData['mail_structure'] ?? 'email@defaut.fr',
                ];

                if ($typeActivite === 'ressourcerie' && !$ressourceriePaid) {
                    $ressourcerieIds   = $formData['ressourcerie_selectionnees'] ?? [];
                    $totalRessourcerie = \App\Models\Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix');
                    try {
                        $urlPaiement = $service->createCheckout(
                            (int) round($totalRessourcerie * 100),
                            $payerInfo,
                            $token,
                            'adhesion.helloasso.return',
                            'Ressourcerie - Savoirs Vivants'
                        );
                        return redirect($urlPaiement);
                    } catch (\Exception $e) {
                        Log::error('HelloAsso Error (Structure Ressourcerie): ' . $e->getMessage());
                        return back()->withErrors(['helloasso' => $e->getMessage()]);
                    }
                }

                $montantCentimes = $this->montantStructure($formData) * 100;
                try {
                    $urlPaiement = $service->createCheckout(
                        $montantCentimes,
                        $payerInfo,
                        $token,
                        'adhesion.helloasso.return',
                        'Adhésion Structure - Savoirs Vivants'
                    );
                    return redirect($urlPaiement);
                } catch (\Exception $e) {
                    Log::error('HelloAsso Error (Structure): ' . $e->getMessage());
                    return back()->withErrors(['helloasso' => $e->getMessage()]);
                }
            }

            $activitesIds      = $formData['activites_selectionnees'] ?? [];
            $hasActivites      = !empty($activitesIds);
            $estNouvelAdherent = ($formData['is_adherent'] ?? 'non') === 'non';

            $totalActiviteEuros = 0;
            if ($hasActivites) {
                $activites = \App\Models\Activite::whereIn('id', $activitesIds)->get();
                $totalActiviteEuros = $activites->sum('tarif');
            }

            // Pour les réinscriptions, prenom/nom/mail ne sont pas dans formData (step 3 sauté)
            $payerPrenom = $formData['prenom'] ?? null;
            $payerNom    = $formData['nom']    ?? null;
            $payerMail   = $formData['mail']   ?? null;
            if (($formData['is_adherent'] ?? 'non') === 'oui' && !empty($formData['numero_adherent'])) {
                $adherentExistant = Adherent::where('numero_adherent', $formData['numero_adherent'])->first();
                if ($adherentExistant) {
                    $payerPrenom = $payerPrenom ?: $adherentExistant->prenom;
                    $payerNom    = $payerNom    ?: $adherentExistant->nom;
                    $payerMail   = $payerMail   ?: $adherentExistant->mail;
                }
            }
            $payerInfo = [
                'prenom' => $payerPrenom ?: 'Prénom',
                'nom'    => $payerNom    ?: 'Nom',
                'mail'   => $payerMail   ?: 'email@defaut.fr',
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
            } elseif ($estNouvelAdherent) {
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
                ->withErrors(['helloasso' => 'Le paiement a été annulé ou a échoué. Vous pouvez réessayer.']);
        }

        $formData = $request->session()->get("adhesion_{$token}", []);
        $formData['_last_completed'] = max((int)($formData['_last_completed'] ?? 0), 10);
        $request->session()->put("adhesion_{$token}", $formData);

        if ($this->isStructure($formData)) {
            if (($formData['type_activite'] ?? '') === 'ressourcerie' && empty($formData['_ressourcerie_paid'])) {
                $formData['_ressourcerie_paid'] = true;
                $request->session()->put("adhesion_{$token}", $formData);
                $request->session()->put("paiement1_done_{$token}", true);
                return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
            }
            $formData['_helloasso_ok']   = true;
            $formData['_last_completed'] = 11;
            $request->session()->put("adhesion_{$token}", $formData);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
        }

        $estNouvelAdherent = ($formData['is_adherent'] ?? 'non') === 'non';

        if ($estNouvelAdherent) {
            $request->session()->put("paiement1_done_{$token}", true);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
        }

        $formData['_helloasso_ok'] = true;
        $request->session()->put("adhesion_{$token}", $formData);
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
        $formData['_helloasso_ok']   = true;
        $formData['_last_completed'] = 11;
        $request->session()->put("adhesion_{$token}", $formData);

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }

    private function sauvegarderAdherent(array $formData): int
    {
        $isAdherent  = ($formData['is_adherent'] ?? 'non') === 'oui';
        $typeActivite = $formData['type_activite'] ?? '';
        $activiteIds  = array_filter((array) ($formData['activites_selectionnees'] ?? []));
        $ressourcerieIds = array_filter((array) ($formData['ressourcerie_selectionnees'] ?? []));
        $year   = now()->month >= 9 ? now()->year : now()->year - 1;
        $saison = $year . '-' . ($year + 1);
        $aPaye = Inscription::EN_ATTENTE;

        $idTuteurPrincipal = null;
        $autresTouteurs    = [];

        if (!$isAdherent) {
            foreach ((array) ($formData['tuteurs'] ?? []) as $t) {
                $type = $t['type'] ?? 'parent_tuteur';
                $tuteur = Tuteur::create([
                    'type'         => $type,
                    'nom'          => $t['nom'] ?? '',
                    'prenom'       => $t['prenom'] ?? '',
                    'tel'          => $t['tel'] ?? null,
                    'mail'         => $t['mail'] ?? null,
                    'adhere'       => !empty($t['adhere']),
                    'rentre_fin'   => !empty($t['rentre_fin']),
                    'rentre_annul' => !empty($t['rentre_annul']),
                ]);
                if ($type === 'parent_tuteur' && $idTuteurPrincipal === null) {
                    $idTuteurPrincipal = $tuteur->id;
                }
                $autresTouteurs[] = $tuteur->id;
            }
        }

        if ($isAdherent) {
            $adherent = Adherent::where('numero_adherent', $formData['numero_adherent'])->firstOrFail();
        } else {
            $adherent = Adherent::create([
                'numero_adherent' => Adherent::genererNumeroUnique(),
                'id_tuteur'       => $idTuteurPrincipal,
                'nom'             => $formData['nom'] ?? '',
                'prenom'          => $formData['prenom'] ?? '',
                'genre'           => $formData['genre'] ?? null,
                'date_naiss'      => $formData['date_naiss'] ?? null,
                'adresse'         => $formData['adresse'] ?? null,
                'code_postal'     => $formData['code_postal'] ?? null,
                'ville'           => $formData['ville'] ?? null,
                'tel'             => $formData['tel'] ?? null,
                'mail'            => $formData['mail'] ?? null,
                'regime_social'   => $formData['regime_social'] ?? null,
                'occupation'      => $formData['occupation'] ?? null,
                'etablissement'   => $formData['etablissement'] ?? null,
                'carnet'          => $formData['carnet_sante_path'] ?? null,
                'bulletin'        => !empty($formData['bulletin']),
                'communication'   => !empty($formData['communication']),
                'manif'           => ($formData['participation_manif'] ?? '0') === '1',
                'actions'         => json_encode($formData['actions_benevoles'] ?? []),
                'signature'       => $formData['signature_adherent'] ?? null,
            ]);

            if (!empty($autresTouteurs)) {
                foreach ($autresTouteurs as $idTuteur) {
                    DB::table('adherent_tuteurs')->insert([
                        'id_adherent' => $adherent->id,
                        'id_tuteur'   => $idTuteur,
                    ]);
                }
            }
        }

        $montantActivites    = !empty($activiteIds)    ? Activite::whereIn('id', $activiteIds)->sum('tarif')       : 0;
        $montantRessourcerie = !empty($ressourcerieIds) ? Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix') : 0;
        $cotisation = (!$isAdherent && $typeActivite !== 'club_maker') ? 10 : 0;
        $montantTotal = (float) ($montantActivites + $montantRessourcerie + $cotisation);

        Inscription::create([
            'id_adherent'     => $adherent->id,
            'saison'          => $saison,
            'date_inscription' => now()->toDateString(),
            'type_adhesion'   => $typeActivite,
            'a_paye'          => $aPaye,
            'montant'         => $montantTotal,
            'renouvellement'  => $isAdherent,
        ]);

        foreach ($activiteIds as $idActivite) {
            DB::table('activites_adherents')->insertOrIgnore([
                'id_adherent'  => $adherent->id,
                'id_activite'  => $idActivite,
                'saison'       => $saison,
                'date_entree'  => now()->toDateString(),
                'est_un_abandon' => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        if (!empty($formData['_helloasso_ok'])) {
            $montantActivite = (float) ($montantActivites + $montantRessourcerie);
            if ($montantActivite > 0) {
                Paiement::create([
                    'id_adherent'   => $adherent->id,
                    'montant'       => $montantActivite,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Paiement activité/ressourcerie via HelloAsso',
                ]);
            }
            if ($cotisation > 0) {
                Paiement::create([
                    'id_adherent'   => $adherent->id,
                    'montant'       => $cotisation,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Cotisation annuelle via HelloAsso',
                ]);
            }
        }

        return $adherent->id;
    }

    private function sauvegarderStructure(array $formData): int
    {
        $statutActivite = match ($formData['type_activite'] ?? '') {
            'ressourcerie' => 'ressourcerie',
            'soutien'      => 'soutien',
            default        => 'participation',
        };

        $structure = AdherentStructure::create([
            'nom'              => $formData['nom_structure'] ?? '',
            'sigle'            => $formData['sigle'] ?? null,
            'adresse'          => $formData['adresse_structure'] ?? null,
            'code_postal'      => $formData['code_postal_structure'] ?? null,
            'ville'            => $formData['ville_structure'] ?? null,
            'date_creation'    => $formData['date_creation_structure'] ?? null,
            'tel'              => $formData['tel_structure'] ?? null,
            'tel_portable'     => $formData['tel_portable_structure'] ?? null,
            'mail'             => $formData['mail_structure'] ?? null,
            'site_web'         => $formData['site_web'] ?? null,
            'nom_correspondant' => $formData['nom_correspondant'] ?? null,
            'tel_correspondant' => $formData['tel_correspondant'] ?? null,
            'bulletin'         => (bool) ($formData['bulletin'] ?? false),
            'communication'    => (bool) ($formData['communication'] ?? false),
            'autorisation_photo' => (bool) ($formData['autorisation_photo'] ?? false),
            'statut'           => $statutActivite,
            'statut_juridique' => $formData['statut_juridique'] ?? null,
            'signature'        => $formData['signature_adherent'] ?? null,
        ]);

        $year   = now()->month >= 9 ? now()->year : now()->year - 1;
        $saison = $year . '-' . ($year + 1);
        $aPaye  = !empty($formData['_helloasso_ok']) ? Inscription::PAYE : Inscription::EN_ATTENTE;

        DB::table('inscriptions')->insert([
            'id_adherent'     => null,
            'id_structure'    => $structure->id,
            'saison'          => $saison,
            'date_inscription' => now()->toDateString(),
            'type_adhesion'   => $formData['type_activite'] ?? 'soutien',
            'a_paye'          => $aPaye,
            'montant'         => $this->montantStructure($formData),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return $structure->id;
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
            'email'    => $email,
            'nom'      => "{$firstName} {$lastName}",
            'montant'  => $amount,
            'formSlug' => $formSlug,
            'orderId'  => $orderId,
        ]);

        if (!$email) {
            Log::warning('HelloAsso webhook : Email manquant dans le payload.');
            return response()->json(['status' => 'missing_email'], 400);
        }

        $adherent = \App\Models\Adherent::where('mail', $email)->latest()->first();

        if ($adherent) {
            Log::info('HelloAsso webhook : adhérent trouvé', [
                'id'              => $adherent->id,
                'numero_adherent' => $adherent->numero_adherent,
            ]);

            $inscription = \App\Models\Inscription::where('id_adherent', $adherent->id)
                ->where('a_paye', \App\Models\Inscription::EN_ATTENTE)
                ->latest()
                ->first();

            if ($inscription) {
                $inscription->update(['a_paye' => \App\Models\Inscription::PAYE]);
                Log::info("Inscription de l'adhérent {$adherent->id} validée.");
            }

            \App\Models\Paiement::create([
                'id_adherent'   => $adherent->id,
                'montant'       => $amount,
                'source'        => 'HelloAsso',
                'date_paiement' => now()->toDateString(),
                'commentaire'   => "Paiement webhook (Order: {$orderId})",
            ]);

            return response()->json(['status' => 'ok'], 200);
        }

        $structure = \App\Models\AdherentStructure::where('mail', $email)->latest()->first();

        if ($structure) {
            Log::info('HelloAsso webhook : structure trouvée', [
                'id'  => $structure->id,
                'nom' => $structure->nom,
            ]);

            $inscriptionStructure = \Illuminate\Support\Facades\DB::table('inscriptions')
                ->where('id_structure', $structure->id)
                ->where('a_paye', \App\Models\Inscription::EN_ATTENTE)
                ->orderByDesc('id')
                ->first();

            if ($inscriptionStructure) {
                \Illuminate\Support\Facades\DB::table('inscriptions')
                    ->where('id', $inscriptionStructure->id)
                    ->update([
                        'a_paye'     => \App\Models\Inscription::PAYE,
                        'updated_at' => now()
                    ]);
                Log::info("Inscription de la structure {$structure->id} validée.");
            }

            return response()->json(['status' => 'ok'], 200);
        }

        // 3. Aucun compte trouvé
        Log::warning("HelloAsso webhook : Aucun compte (adhérent ou structure) trouvé pour l'email {$email}. Le paiement a réussi chez HelloAsso mais n'a pas pu être lié automatiquement dans la base.");

        return response()->json(['status' => 'not_found'], 404);
    }
}
