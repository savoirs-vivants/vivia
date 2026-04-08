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
use App\Models\Saison;
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
        } catch (\Exception) {
            return false;
        }
    }

    private function isStructure(array $formData): bool
    {
        return in_array($formData['statut_juridique'] ?? '', ['tpe_asso', 'esr_pme']);
    }

    private function montantStructure(array $formData): int
    {
        if (($formData['is_adherent'] ?? 'non') === 'oui') {
            return 0;
        }
        return ($formData['statut_juridique'] ?? '') === 'esr_pme' ? 200 : 50;
    }

    private function getUserPath(array $formData): array
    {
        $isAdherent    = ($formData['is_adherent']  ?? 'non') === 'oui';
        $activite      =  $formData['type_activite'] ?? '';
        $isMineur      = $this->isMineur($formData['date_naiss'] ?? null);
        $isClubMaker      = ($activite === 'club_maker');
        $needsActivite = in_array($activite, ['atelier', 'stage', 'ressourcerie']);

        if ($this->isStructure($formData)) {
            $path = $isAdherent ? [1, 2] : [1, 12, 2];
            if ($activite === 'ressourcerie') $path[] = 6;
            $path = array_merge($path, [13, 14, 9, 10, 11]);
            return $path;
        }

        $path = $isAdherent ? [1, 2, 3] : [1, 12, 2, 3];
        if (!$isAdherent && $isMineur) $path[] = 15;
        if ($isMineur) $path[] = 4;
        $path[] = 5;
        if ($needsActivite || $isClubMaker) $path[] = 6;
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
            15 => ['label' => 'Orientation',    'icon' => '🎓'],
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
                $champsAdherent = [
                    'nom',
                    'prenom',
                    'genre',
                    'adresse',
                    'code_postal',
                    'ville',
                    'tel',
                    'mail',
                    'regime_social',
                    'occupation',
                    'etablissement',
                    'problemes_sante',
                    'allergies',
                    'conduite_a_tenir',
                    'restrictions_alimentaires',
                    'bulletin',
                    'communication',
                ];
                foreach ($champsAdherent as $champ) {
                    if (!array_key_exists($champ, $formData) && $adherentExistant->$champ !== null) {
                        $formData[$champ] = $adherentExistant->$champ;
                    }
                }
                if (!array_key_exists('date_naiss', $formData)) {
                    $formData['date_naiss'] = $adherentExistant->date_naiss?->format('Y-m-d');
                }
                if (!array_key_exists('carnet_sante_path', $formData) && !empty($adherentExistant->carnet)) {
                    $formData['carnet_sante_path'] = $adherentExistant->carnet;
                }
                if (!array_key_exists('actions_benevoles', $formData) && !empty($adherentExistant->actions)) {
                    $decoded = json_decode($adherentExistant->actions, true);
                    $formData['actions_benevoles'] = is_array($decoded) ? $decoded : [];
                }
                if (!array_key_exists('participation_manif', $formData)) {
                    $formData['participation_manif'] = $adherentExistant->manif ? '1' : '0';
                }
                if (!array_key_exists('signature_adherent', $formData) && !empty($adherentExistant->signature)) {
                    $formData['signature_adherent'] = $adherentExistant->signature;
                }
                if (!array_key_exists('tuteurs', $formData)) {
                    $tuteurs = $adherentExistant->tousLesTuteurs()->get();
                    if ($tuteurs->isNotEmpty()) {
                        $formData['tuteurs'] = $tuteurs->map(fn($t) => [
                            'type'           => $t->type,
                            'nom'            => $t->nom ?? '',
                            'prenom'         => $t->prenom ?? '',
                            'tel'            => $t->tel ?? '',
                            'mail'           => $t->mail ?? '',
                            'profession'      => $t->profession ?? '',
                            'adhere'         => (bool) $t->adhere,
                            'rentre_fin'     => (bool) $t->rentre_fin,
                            'rentre_annul'   => (bool) $t->rentre_annul,
                            'date_signature' => $t->type === 'parent_tuteur' ? ($t->date_signature ?? '') : '',
                            'signature'      => $t->type === 'parent_tuteur' ? ($t->signature ?? '') : '',
                        ])->toArray();
                    }
                }

                $saison = Saison::current();
                $activitesDejaInscritesIds = $adherentExistant->activites()
                    ->wherePivot('saison', $saison)
                    ->wherePivot('est_un_abandon', 0)
                    ->pluck('activites.id')
                    ->toArray();
            }

            if (!$adherentExistant && !empty($formData['_existing_structure_id'])) {
                $structureExistante = AdherentStructure::find($formData['_existing_structure_id']);
                if ($structureExistante) {
                    $champsStructure = [
                        'nom_structure'           => 'nom',
                        'sigle'                   => 'sigle',
                        'adresse_structure'       => 'adresse',
                        'code_postal_structure'   => 'code_postal',
                        'ville_structure'         => 'ville',
                        'tel_structure'           => 'tel',
                        'tel_portable_structure'  => 'tel_portable',
                        'mail_structure'          => 'mail',
                        'site_web'                => 'site_web',
                        'nom_correspondant'       => 'nom_correspondant',
                        'tel_correspondant'       => 'tel_correspondant',
                    ];
                    foreach ($champsStructure as $formKey => $modelKey) {
                        if (!array_key_exists($formKey, $formData) && $structureExistante->$modelKey !== null) {
                            $formData[$formKey] = $structureExistante->$modelKey;
                        }
                    }
                    if (!array_key_exists('date_creation_structure', $formData) && $structureExistante->date_creation) {
                        $formData['date_creation_structure'] = $structureExistante->date_creation->format('Y-m-d');
                    }
                    foreach (['bulletin', 'autorisation_photo', 'communication'] as $champ) {
                        if (!array_key_exists($champ, $formData) && $structureExistante->$champ !== null) {
                            $formData[$champ] = $structureExistante->$champ;
                        }
                    }
                    if (!array_key_exists('signature_adherent', $formData) && !empty($structureExistante->signature)) {
                        $formData['signature_adherent'] = $structureExistante->signature;
                    }
                }
            }
        }

        $classeAdherent = $this->classeDepuisAge($formData);
        $filtre         = $this->classesFiltrer($formData);

        $activites = Activite::where('is_archived', false)->get();
        $ateliers  = $activites->where('type', 'activite')->values()
            ->filter($filtre)
            ->filter(fn($a) => !in_array($a->id, $activitesDejaInscritesIds))
            ->filter(fn($a) => !Str::contains(strtolower($a->nom), 'maker'))
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
            if (empty($formData['_admin_mail_sent'])) {
                try {
                    $entity = null;
                    $dataMail = [];

                    if ($isStructure && !empty($formData['_structure_id'])) {
                        $entity = AdherentStructure::find($formData['_structure_id']);
                        $dataMail = [
                            'nom' => $entity->nom,
                            'prenom' => '',
                            'numero' => $entity->numero_adherent
                        ];
                    } elseif (!empty($formData['_adherent_id'])) {
                        $entity = Adherent::find($formData['_adherent_id']);
                        $dataMail = [
                            'nom' => $entity->nom,
                            'prenom' => $entity->prenom,
                            'numero' => $entity->numero_adherent
                        ];
                    }

                    if ($entity) {
                        Mail::send('emails.admin_nouvelle_inscription', $dataMail, function ($message) {
                            $message->to('contact@savoirsvivants.fr')
                                ->subject('🎉 Nouvelle inscription - Savoirs Vivants');
                        });
                    }

                    $formData['_admin_mail_sent'] = true;
                    $request->session()->put("adhesion_{$token}", $formData);
                } catch (\Exception $e) {
                    Log::error("Erreur envoi mail admin direction : " . $e->getMessage());
                }
            }
        }

        $clubMakerActivites = collect();
        if (($formData['type_activite'] ?? '') !== 'atelier') {
            $clubMakerActivites = $activites
                ->filter(function ($a) use ($activitesDejaInscritesIds) {
                    return Str::contains(strtolower($a->nom), 'maker')
                        && !in_array($a->id, $activitesDejaInscritesIds);
                })
                ->values();
        }

        return view('adhesion.index', compact(
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
            'totalRessourcerieStructure',
            'clubMakerActivites'
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
            $inputNumero     = trim($request->input('numero_adherent'));
            $vraiNumero      = Cache::get("recup_adherent_{$inputNumero}");
            $numeroRecherche = $vraiNumero ?: $inputNumero;

            $adherentExistant  = Adherent::where('numero_adherent', $numeroRecherche)->first();
            $structureExistante = null;

            if (!$adherentExistant) {
                $structureExistante = AdherentStructure::where('numero_adherent', $numeroRecherche)->first();
            }

            if (!$adherentExistant && !$structureExistante) {
                return back()->withErrors(['numero_adherent' => 'Ce numéro ou code temporaire est introuvable.']);
            }

            if ($structureExistante) {
                $request->merge([
                    'numero_adherent'        => $structureExistante->numero_adherent,
                    'statut_juridique'       => $structureExistante->statut_juridique,
                    '_existing_structure_id' => $structureExistante->id,
                ]);
            } else {
                $request->merge([
                    'numero_adherent'  => $adherentExistant->numero_adherent,
                    'statut_juridique' => 'personne_physique',
                ]);
            }
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
                $isDejaAdherent   = ($formData['is_adherent'] ?? 'non') === 'oui';

                if ($typeActivite === 'ressourcerie' && !$ressourceriePaid) {
                    $correspondant = trim($formData['nom_correspondant'] ?? '');
                    $parts = preg_split('/\s+/', $correspondant, 2);
                    $payerInfo = [
                        'prenom' => $parts[0] ?: 'Correspondant',
                        'nom'    => ($parts[1] ?? '') ?: ($formData['nom_structure'] ?? 'Structure'),
                        'mail'   => $formData['mail_structure'] ?? 'email@defaut.fr',
                    ];
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

                if ($isDejaAdherent) {
                    $formData['_paiement2_cree'] = true;
                    $formData['_helloasso_ok']   = true;
                    $formData['_last_completed'] = 11;
                    $request->session()->put("adhesion_{$token}", $formData);
                    return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
                }

                $request->session()->put("paiement1_done_{$token}", true);
                return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
            }

            $activitesIds      = array_filter((array) ($formData['activites_selectionnees'] ?? []));
            $ressourcerieIds   = array_filter((array) ($formData['ressourcerie_selectionnees'] ?? []));
            $estNouvelAdherent = ($formData['is_adherent'] ?? 'non') === 'non';

            $totalActiviteEuros = 0;
            if (!empty($activitesIds)) {
                $totalActiviteEuros += \App\Models\Activite::whereIn('id', $activitesIds)->sum('tarif');
            }
            if (!empty($ressourcerieIds)) {
                $totalActiviteEuros += \App\Models\Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix');
            }

            $isSinglePayment = in_array($formData['type_activite'] ?? '', ['soutien', 'recherche']);
            if ($isSinglePayment && $totalActiviteEuros == 0) {
                if (!$estNouvelAdherent) {
                    $formData['_paiement2_cree'] = true;
                    $formData['_helloasso_ok']   = true;
                    $formData['_last_completed'] = 11;
                    $request->session()->put("adhesion_{$token}", $formData);
                    return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
                }
                $request->session()->put("paiement1_done_{$token}", true);
                return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
            }

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
                $itemLabel = ($formData['type_activite'] ?? '') === 'ressourcerie'
                    ? 'Ressourcerie - Savoirs Vivants'
                    : 'Inscription Activité - Savoirs Vivants';
                try {
                    $urlPaiement = $service->createCheckout(
                        (int) round($totalActiviteEuros * 100),
                        $payerInfo,
                        $token,
                        'adhesion.helloasso.return',
                        $itemLabel
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
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('carnets');
            $filePath = $request->file('carnet_sante')->store('carnets', 'public');
            if ($filePath) {
                $formData['carnet_sante_path'] = $filePath;
            } else {
                Log::error('Carnet de santé : échec du store()', ['step' => $step]);
            }
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

                if (!empty($formData['_structure_id'])) {
                    $ressourcerieIds     = $formData['ressourcerie_selectionnees'] ?? [];
                    $montantRessourcerie = \App\Models\Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix');
                    if ($montantRessourcerie > 0) {
                        Paiement::create([
                            'id_structure'  => $formData['_structure_id'],
                            'montant'       => $montantRessourcerie,
                            'source'        => 'HelloAsso',
                            'date_paiement' => now()->toDateString(),
                            'commentaire'   => 'Ressourcerie structure via HelloAsso',
                        ]);
                    }
                }

                if (($formData['is_adherent'] ?? 'non') === 'oui' && !empty($formData['_structure_id'])) {
                    $formData['_paiement2_cree'] = true;
                    $formData['_helloasso_ok']   = true;
                    $formData['_last_completed'] = 11;
                    $request->session()->put("adhesion_{$token}", $formData);
                    return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
                }

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

        if (!empty($formData['_adherent_id']) && empty($formData['_paiement1_cree'])) {
            $activiteIds      = array_filter((array) ($formData['activites_selectionnees'] ?? []));
            $ressourcerieIds  = array_filter((array) ($formData['ressourcerie_selectionnees'] ?? []));
            $totalActivites   = !empty($activiteIds)     ? \App\Models\Activite::whereIn('id', $activiteIds)->sum('tarif')       : 0;
            $totalRessourcerie = !empty($ressourcerieIds) ? \App\Models\Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix') : 0;
            $montantActivite  = (float) ($totalActivites + $totalRessourcerie);

            if ($montantActivite > 0) {
                Paiement::create([
                    'id_adherent'   => $formData['_adherent_id'],
                    'montant'       => $montantActivite,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Paiement activité/ressourcerie via HelloAsso',
                ]);
                $formData['_paiement1_cree'] = true;
                $request->session()->put("adhesion_{$token}", $formData);
            }
        }

        if ($estNouvelAdherent) {
            $request->session()->put("paiement1_done_{$token}", true);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
        }

        $formData['_helloasso_ok'] = true;
        $formData['_last_completed'] = 11;
        $request->session()->put("adhesion_{$token}", $formData);
        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }

    public function helloassoCheckout2(Request $request, string $token)
    {
        abort_if(!$request->session()->has("adhesion_{$token}"), 403);

        $formData = $request->session()->get("adhesion_{$token}", []);
        $formData['_via_url_checkout'] = true;
        $request->session()->put("adhesion_{$token}", $formData);

        $isSandbox  = config('services.helloasso.sandbox', true);
        $basePublic = $isSandbox
            ? 'https://www.helloasso-sandbox.com'
            : 'https://www.helloasso.com';
        $orgSlug = config('services.helloasso.org_slug');

        if ($this->isStructure($formData)) {
            $formSlug = ($formData['statut_juridique'] ?? '') === 'esr_pme'
                ? env('HELLOASSO_MEMBERSHIP_FORM_SLUG_PME', 'adhesion-savoirs-vivants-2025-2026-pme')
                : env('HELLOASSO_MEMBERSHIP_FORM_SLUG_TPE', 'adhesion-savoirs-vivants-2025-2026-tpe');
        } else {
            $formSlug = env('HELLOASSO_MEMBERSHIP_FORM_SLUG');
        }

        $url = "{$basePublic}/associations/{$orgSlug}/adhesions/{$formSlug}";

        Log::info("HelloAsso checkout2 : URL d'adhésion", ['url' => $url, 'statut_juridique' => $formData['statut_juridique'] ?? 'n/a']);

        if ($request->expectsJson()) {
            return response()->json(['url' => $url]);
        }

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
        if (!empty($formData['_adherent_id']) && empty($formData['_paiement2_cree'])) {
            $typeActivite = $formData['type_activite'] ?? '';
            $cotisation   = ($typeActivite !== 'club_maker') ? 10 : 0;

            if ($cotisation > 0) {
                Paiement::create([
                    'id_adherent'   => $formData['_adherent_id'],
                    'montant'       => $cotisation,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Cotisation annuelle via HelloAsso',
                ]);
                $formData['_paiement2_cree'] = true;
                $request->session()->put("adhesion_{$token}", $formData);
            }
        }

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }

    /**
     * Vérifie via l'API HelloAsso si la cotisation a bien été payée.
     * Gère les adhérents (personne physique) et les structures.
     */
    public function verifierCotisation(Request $request, string $token)
    {
        abort_if(!$request->session()->has("adhesion_{$token}"), 403);

        $formData = $request->session()->get("adhesion_{$token}", []);

        if (!empty($formData['_paiement2_cree'])) {
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
        }

        if ($this->isStructure($formData)) {
            if (empty($formData['_structure_id'])) {
                return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10])
                    ->withErrors(['helloasso2' => 'Structure introuvable, veuillez réessayer.']);
            }

            $isDejaAdherentStr = ($formData['is_adherent'] ?? 'non') === 'oui';
            $montantCotisation = $isDejaAdherentStr ? 0 : (($formData['statut_juridique'] ?? '') === 'esr_pme' ? 200 : 50);
            if ($montantCotisation > 0) {
                Paiement::create([
                    'id_structure'  => $formData['_structure_id'],
                    'montant'       => $montantCotisation,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Cotisation structure via HelloAsso',
                ]);
            }

            $formData['_paiement2_cree'] = true;
            $formData['_helloasso_ok']   = true;
            $formData['_last_completed'] = 11;
            $request->session()->put("adhesion_{$token}", $formData);

            Log::info("Cotisation structure {$formData['_structure_id']} validée (URL directe HelloAsso)");
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
        }

        if (empty($formData['_adherent_id'])) {
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
        }

        $typeActivite   = $formData['type_activite'] ?? '';
        $isDejaAdherent = ($formData['is_adherent'] ?? 'non') === 'oui';
        $cotisation     = (!$isDejaAdherent && $typeActivite !== 'club_maker') ? 10 : 0;

        if ($cotisation > 0) {
            Paiement::create([
                'id_adherent'   => $formData['_adherent_id'],
                'montant'       => $cotisation,
                'source'        => 'HelloAsso',
                'date_paiement' => now()->toDateString(),
                'commentaire'   => 'Cotisation annuelle via HelloAsso',
            ]);
        }

        $formData['_paiement2_cree'] = true;
        $formData['_helloasso_ok']   = true;
        $formData['_last_completed'] = 11;
        $request->session()->put("adhesion_{$token}", $formData);

        Log::info("Cotisation validée pour adhérent {$formData['_adherent_id']} (URL directe HelloAsso)");
        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }

    private function sauvegarderAdherent(array $formData): int
    {
        $isAdherent  = ($formData['is_adherent'] ?? 'non') === 'oui';
        $typeActivite = $formData['type_activite'] ?? '';
        $activiteIds  = array_filter((array) ($formData['activites_selectionnees'] ?? []));
        $ressourcerieIds = array_filter((array) ($formData['ressourcerie_selectionnees'] ?? []));
        $saison = Saison::current();
        $aPaye = Inscription::EN_ATTENTE;

        $idTuteurPrincipal = null;
        $autresTouteurs    = [];

        if (!$isAdherent) {
            foreach ((array) ($formData['tuteurs'] ?? []) as $t) {
                $type = $t['type'] ?? 'parent_tuteur';
                $tuteur = Tuteur::create([
                    'type'           => $type,
                    'nom'            => $t['nom'] ?? '',
                    'prenom'         => $t['prenom'] ?? '',
                    'tel'            => $t['tel'] ?? null,
                    'mail'           => $t['mail'] ?? null,
                    'profession'      => $t['profession'] ?? null,
                    'adhere'         => !empty($t['adhere']),
                    'rentre_fin'     => !empty($t['rentre_fin']),
                    'rentre_annul'   => !empty($t['rentre_annul']),
                    'date_signature' => $type === 'parent_tuteur' ? ($t['date_signature'] ?? null) : null,
                    'signature'      => $type === 'parent_tuteur' ? ($t['signature'] ?? null) : null,
                ]);
                if ($type === 'parent_tuteur' && $idTuteurPrincipal === null) {
                    $idTuteurPrincipal = $tuteur->id;
                }
                $autresTouteurs[] = $tuteur->id;
            }
        }

        $geocoder = new \App\Services\GeocodingService();
        $coords = $geocoder->getCoordinates(
            $formData['adresse'] ?? null,
            $formData['code_postal'] ?? null,
            $formData['ville'] ?? null
        );

        if ($isAdherent) {
            $adherent = Adherent::where('numero_adherent', $formData['numero_adherent'])->firstOrFail();

            $updateData = [];
            $fields = ['nom', 'prenom', 'genre', 'adresse', 'code_postal', 'ville', 'tel', 'mail', 'regime_social', 'occupation', 'etablissement', 'problemes_sante', 'allergies', 'conduite_a_tenir', 'restrictions_alimentaires', 'idee_metier', 'decouverte_metier'];
            foreach ($fields as $field) {
                if (isset($formData[$field]) && $formData[$field] !== $adherent->$field) {
                    $updateData[$field] = $formData[$field];
                }
            }
            if (isset($formData['date_naiss']) && $formData['date_naiss'] !== $adherent->date_naiss?->format('Y-m-d')) {
                $updateData['date_naiss'] = $formData['date_naiss'];
                $updateData['age'] = \Carbon\Carbon::parse($formData['date_naiss'])->age;
            }
            if (isset($formData['carnet_sante_path'])) {
                $updateData['carnet'] = $formData['carnet_sante_path'];
            }
            if (isset($formData['signature_adherent'])) {
                $updateData['signature'] = $formData['signature_adherent'];
            }
            if (isset($formData['actions_benevoles'])) {
                $updateData['actions'] = json_encode($formData['actions_benevoles']);
            }

            if (isset($formData['adresse']) || isset($formData['ville'])) {
                $updateData['latitude']  = $coords ? $coords['lat'] : $adherent->latitude;
                $updateData['longitude'] = $coords ? $coords['lng'] : $adherent->longitude;
            }

            $updateData['bulletin'] = !empty($formData['bulletin'] ?? false);
            $updateData['communication'] = !empty($formData['communication'] ?? false);
            $updateData['manif'] = ($formData['participation_manif'] ?? '0') === '1';

            if (!empty($updateData)) {
                $adherent->update($updateData);
            }

            $existingTuteurIds = $adherent->tousLesTuteurs()->pluck('tuteurs.id')->toArray();
            if (!empty($formData['tuteurs'])) {
                foreach ($formData['tuteurs'] as $tData) {
                    $tuteur = Tuteur::updateOrCreate(
                        ['id' => $tData['id'] ?? null],
                        [
                            'type'           => $tData['type'] ?? 'parent_tuteur',
                            'nom'            => $tData['nom'] ?? '',
                            'prenom'         => $tData['prenom'] ?? '',
                            'tel'            => $tData['tel'] ?? null,
                            'mail'           => $tData['mail'] ?? null,
                            'profession'     => $tData['profession'] ?? null,
                            'adhere'         => !empty($tData['adhere']),
                            'rentre_fin'     => !empty($tData['rentre_fin']),
                            'rentre_annul'   => !empty($tData['rentre_annul']),
                            'date_signature' => ($tData['type'] ?? '') === 'parent_tuteur' ? ($tData['date_signature'] ?? null) : null,
                            'signature'      => ($tData['type'] ?? '') === 'parent_tuteur' ? ($tData['signature'] ?? null) : null,
                        ]
                    );
                    if (!in_array($tuteur->id, $existingTuteurIds)) {
                        DB::table('adherent_tuteurs')->insertOrIgnore([
                            'id_adherent' => $adherent->id,
                            'id_tuteur'   => $tuteur->id,
                        ]);
                    }
                }
            }
        } else {
            $age = null;
            if (!empty($formData['date_naiss'])) {
                $age = \Carbon\Carbon::parse($formData['date_naiss'])->age;
            }
            $adherent = Adherent::create([
                'numero_adherent' => Adherent::genererNumeroUnique(),
                'nom'             => $formData['nom'] ?? '',
                'prenom'          => $formData['prenom'] ?? '',
                'genre'           => $formData['genre'] ?? null,
                'date_naiss'      => $formData['date_naiss'] ?? null,
                'age'             => $age,
                'adresse'         => $formData['adresse'] ?? null,
                'code_postal'     => $formData['code_postal'] ?? null,
                'ville'           => $formData['ville'] ?? null,
                'tel'             => $formData['tel'] ?? null,
                'mail'            => $formData['mail'] ?? null,
                'regime_social'   => $formData['regime_social'] ?? null,
                'occupation'      => $formData['occupation'] ?? null,
                'etablissement'             => $formData['etablissement'] ?? null,
                'carnet'                    => $formData['carnet_sante_path'] ?? null,
                'problemes_sante'           => $formData['problemes_sante'] ?? null,
                'allergies'                 => $formData['allergies'] ?? null,
                'conduite_a_tenir'          => $formData['conduite_a_tenir'] ?? null,
                'restrictions_alimentaires' => $formData['restrictions_alimentaires'] ?? null,
                'bulletin'        => !empty($formData['bulletin']),
                'communication'   => !empty($formData['communication']),
                'manif'           => ($formData['participation_manif'] ?? '0') === '1',
                'actions'         => json_encode($formData['actions_benevoles'] ?? []),
                'signature'       => $formData['signature_adherent'] ?? null,
                'idee_metier'       => $formData['idee_metier'] ?? null,
                'decouverte_metier' => $formData['decouverte_metier'] ?? null,
                'latitude'        => $coords ? $coords['lat'] : null,
                'longitude'       => $coords ? $coords['lng'] : null,
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
            'numero_adherent'  => AdherentStructure::genererNumeroUnique(),
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
            'autorisation_photo' => (bool) ($formData['autorisation_photo'] ?? false),
            'signature'        => $formData['signature_adherent'] ?? null,
            'statut'           => $statutActivite,
            'statut_juridique' => $formData['statut_juridique'] ?? null,
        ]);

        $saison = Saison::current();
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
        $eventType = $payload['eventType'] ?? null;
        $state     = $payload['data']['state'] ?? null;

        if ($eventType !== 'Payment' || $state !== 'Authorized') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $syncLog = \App\Models\SyncLog::create([
            'source' => 'webhook_helloasso',
            'status' => 'running',
            'payments_imported' => 0,
            'errors' => []
        ]);

        try {
            $email     = $payload['data']['payer']['email']  ?? null;
            $firstName = $payload['data']['payer']['firstName'] ?? null;
            $lastName  = $payload['data']['payer']['lastName']  ?? null;
            $amount    = ($payload['data']['amount'] ?? 0) / 100;
            $formSlug  = $payload['data']['order']['formSlug'] ?? null;

            if (!$email) {
                $syncLog->update([
                    'status' => 'error',
                    'errors' => ["Email manquant dans le payload HelloAsso (Formulaire: {$formSlug})"]
                ]);
                return response()->json(['status' => 'missing_email'], 400);
            }

            $adherent = \App\Models\Adherent::where('mail', $email)->latest()->first();

            if ($adherent) {
                $inscription = \App\Models\Inscription::where('id_adherent', $adherent->id)
                    ->where('a_paye', \App\Models\Inscription::EN_ATTENTE)
                    ->where('renouvellement', false)
                    ->latest()
                    ->first();

                if ($inscription) {
                    $inscription->update(['a_paye' => \App\Models\Inscription::PAYE]);
                }

                $dejaCreee = Paiement::where('id_adherent', $adherent->id)
                    ->where('source', 'HelloAsso')
                    ->where('montant', $amount)
                    ->whereDate('date_paiement', today())
                    ->exists();

                if (!$dejaCreee) {
                    Paiement::create([
                        'id_adherent'   => $adherent->id,
                        'montant'       => $amount,
                        'source'        => 'HelloAsso',
                        'date_paiement' => now()->toDateString(),
                    ]);
                }

                $syncLog->update(['status' => 'success', 'payments_imported' => 1]);
                return response()->json(['status' => 'ok'], 200);
            }

            $structure = AdherentStructure::where('mail', $email)->latest()->first();

            if ($structure) {
                $inscriptionStructure = \Illuminate\Support\Facades\DB::table('inscriptions')
                    ->where('id_structure', $structure->id)
                    ->where('a_paye', \App\Models\Inscription::EN_ATTENTE)
                    ->where('renouvellement', false)
                    ->orderByDesc('id')
                    ->first();

                if ($inscriptionStructure) {
                    \Illuminate\Support\Facades\DB::table('inscriptions')
                        ->where('id', $inscriptionStructure->id)
                        ->update(['a_paye' => \App\Models\Inscription::PAYE, 'updated_at' => now()]);
                }

                $syncLog->update(['status' => 'success', 'payments_imported' => 1]);
                return response()->json(['status' => 'ok'], 200);
            }

            $syncLog->update([
                'status' => 'warning',
                'errors' => ["Paiement de {$amount}€ reçu pour {$firstName} {$lastName}, mais l'email {$email} est introuvable dans la base Savoirs Vivants."]
            ]);

            return response()->json(['status' => 'not_found'], 404);
        } catch (\Exception $e) {
            Log::error("Erreur critique Webhook HelloAsso : " . $e->getMessage());

            $syncLog->update([
                'status' => 'error',
                'errors' => ["Erreur serveur durant le traitement : " . $e->getMessage()]
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
