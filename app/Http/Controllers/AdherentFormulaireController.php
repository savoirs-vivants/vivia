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
use App\Models\Saison;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Support\Facades\Storage;
use App\Services\HelloAssoService;
use App\Models\Setting;
use App\Traits\AdhesionSharedLogic;

class AdherentFormulaireController extends Controller
{
    use AdhesionSharedLogic;

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
        if (empty($dateNaiss)) return false;

        try {
            return Carbon::parse($dateNaiss)->age < 18;
        } catch (\Exception) {
            return false;
        }
    }

    private function getUserPath(array $formData): array
    {
        if (!empty($formData['_pre_inscription_id']) && empty($formData['_pre_inscription_handled'])) {
            return [1, 16];
        }

        $isAdherent    = ($formData['is_adherent']  ?? 'non') === 'oui';
        $activite      = $formData['type_activite'] ?? '';
        $isMineur      = $this->isMineur($formData['date_naiss'] ?? null);
        $isClubMaker   = ($activite === 'club_maker');
        $isRecherche   = ($activite === 'recherche');
        $needsActivite = in_array($activite, ['atelier', 'stage', 'ressourcerie']);

        if ($this->isStructure($formData)) {
            $path = $isAdherent ? [1, 2] : [1, 12, 2];
            if ($activite === 'ressourcerie') $path[] = 6;
            return array_merge($path, [13, 14, 9, 10, 11]);
        }

        $path = $isAdherent ? [1, 2, 3] : [1, 12, 2, 3];
        if (!$isAdherent && $isMineur) $path[] = 15;
        if ($isMineur) $path[] = 4;

        $path[] = 5;

        if ($needsActivite || $isClubMaker) $path[] = 6;
        if ($isRecherche) $path[] = 17;
        if (!$isClubMaker && !$isRecherche) $path[] = 7;
        if ($isMineur) $path[] = 8;

        $path[] = 9;

        if (!$isClubMaker) $path[] = 10;

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

    private function classesEligiblesDepuisOccupation(array $formData): ?array
    {
        $occupation = strtolower($formData['occupation'] ?? '');

        if (empty($occupation)) return null;

        return match (true) {
            str_contains($occupation, 'maison') => null,
            str_contains($occupation, 'maternelle') => ['PS', 'MS', 'GS'],
            str_contains($occupation, 'primaire') || str_contains($occupation, 'élémentaire') => ['CP', 'CE1', 'CE2', 'CM1', 'CM2'],
            str_contains($occupation, 'collège') || str_contains($occupation, 'college') => ['6ème', '5ème', '4ème', '3ème'],
            str_contains($occupation, 'lycée') || str_contains($occupation, 'lycee') => ['Seconde', 'Première', 'Terminale'],
            str_contains($occupation, 'étudiant') || str_contains($occupation, 'etudiant') || str_contains($occupation, 'actif') || str_contains($occupation, 'salarié') || str_contains($occupation, 'retraité') || str_contains($occupation, 'adulte') => ['Adulte', 'Senior'],
            default => null,
        };
    }

    private function classesFiltrer(array $formData): \Closure
    {
        $classesEligibles = $this->classesEligiblesDepuisOccupation($formData);
        $isMineur = $this->isMineur($formData['date_naiss'] ?? null);
        $typeActivite = $formData['type_activite'] ?? '';

        return function ($activite) use ($classesEligibles, $isMineur, $typeActivite) {

            if (!$isMineur && $typeActivite === 'atelier') {
                return empty($activite->classes);
            }

            if ($classesEligibles === null) return true;
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
            16 => ['label' => 'Finalisation',   'icon' => '✨'],
            17 => ['label' => 'Recherches',     'icon' => '🔬'],
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
            if ($p <= $maxDone) $allowedIdx = $idx + 1;
        }

        $allowedIdx = min($allowedIdx, count($path) - 1);
        $requestedIdx = array_search($step, $path);

        if ($requestedIdx === false || $requestedIdx > $allowedIdx) {
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => $path[$allowedIdx]]);
        }

        $activitesDejaInscritesIds = [];

        if (($formData['is_adherent'] ?? 'non') === 'oui' && !empty($formData['numero_adherent'])) {
            $adherentExistant = Adherent::where('numero_adherent', $formData['numero_adherent'])->first();

            if ($adherentExistant) {
                $champsAdherent = ['nom', 'prenom', 'genre', 'adresse', 'code_postal', 'ville', 'tel', 'mail', 'regime_social', 'occupation', 'etablissement', 'problemes_sante', 'allergies', 'conduite_a_tenir', 'restrictions_alimentaires', 'bulletin', 'communication'];

                foreach ($champsAdherent as $champ) {
                    if (!array_key_exists($champ, $formData) && $adherentExistant->$champ !== null) {
                        $formData[$champ] = $adherentExistant->$champ;
                    }
                }

                if (!array_key_exists('date_naiss', $formData)) $formData['date_naiss'] = $adherentExistant->date_naiss?->format('Y-m-d');
                if (!array_key_exists('carnet_sante_path', $formData) && !empty($adherentExistant->carnet)) $formData['carnet_sante_path'] = $adherentExistant->carnet;
                if (!array_key_exists('actions_benevoles', $formData) && !empty($adherentExistant->actions)) {
                    $decoded = json_decode($adherentExistant->actions, true);
                    $formData['actions_benevoles'] = is_array($decoded) ? $decoded : [];
                }
                if (!array_key_exists('participation_manif', $formData)) $formData['participation_manif'] = $adherentExistant->manif ? '1' : '0';
                if (!array_key_exists('signature_adherent', $formData) && !empty($adherentExistant->signature)) $formData['signature_adherent'] = $adherentExistant->signature;

                if (!array_key_exists('tuteurs', $formData)) {
                    $tuteurs = $adherentExistant->tousLesTuteurs()->get();
                    if ($tuteurs->isNotEmpty()) {
                        $formData['tuteurs'] = $tuteurs->map(fn($t) => [
                            'type'           => $t->type,
                            'nom'            => $t->nom ?? '',
                            'prenom'         => $t->prenom ?? '',
                            'tel'            => $t->tel ?? '',
                            'mail'           => $t->mail ?? '',
                            'profession'     => $t->profession ?? '',
                            'adhere'         => (bool) $t->adhere,
                            'rentre_fin'     => (bool) $t->rentre_fin,
                            'rentre_annul'   => (bool) $t->rentre_annul,
                            'date_signature' => $t->type === 'parent_tuteur' ? ($t->date_signature ?? '') : '',
                            'signature'      => $t->type === 'parent_tuteur' ? ($t->signature ?? '') : '',
                        ])->toArray();
                    }
                }

                $saison = $this->determinerSaisonDynamique($formData);
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

        $filtre = $this->classesFiltrer($formData);
        $classesEligibles = $this->classesEligiblesDepuisOccupation($formData);

        $saison = $this->determinerSaisonDynamique($formData);
        $activites = Activite::where('is_archived', false)->get();

        $nbInscritsParActivite = DB::table('activites_adherents')
            ->whereIn('id_activite', $activites->pluck('id'))
            ->where('saison', $saison)
            ->where('est_un_abandon', 0)
            ->groupBy('id_activite')
            ->selectRaw('id_activite, count(*) as nb')
            ->pluck('nb', 'id_activite');

        $ateliers  = $activites->where('type', 'activite')->values()
            ->filter($filtre)
            ->filter(fn($a) => !in_array($a->id, $activitesDejaInscritesIds))
            ->filter(fn($a) => !Str::contains(strtolower($a->nom), 'maker'))
            ->values();

        $stages = $activites->where('type', 'stage')->values()
            ->filter($filtre)
            ->filter(fn($a) => !in_array($a->id, $activitesDejaInscritesIds))
            ->filter(function ($stage) {
                $horaires = is_string($stage->horaires)
                    ? json_decode($stage->horaires, true)
                    : $stage->horaires;
                $dateFin = $horaires['stage']['date_fin'] ?? null;
                $dateDebut = $horaires['stage']['date_debut'] ?? null;

                $dateRef = $dateFin ?? $dateDebut;
                if (!$dateRef) return true;
                try {
                    return Carbon::parse($dateRef)->startOfDay()->gte(now()->startOfDay());
                } catch (\Exception $e) {
                    return true;
                }
            })
            ->values();

        $recherchesDispos = $activites->where('type', 'recherche')->values()
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
                        $dataMail = ['nom' => $entity->nom, 'prenom' => '', 'numero' => $entity->numero_adherent];
                    } elseif (!empty($formData['_adherent_id'])) {
                        $entity = Adherent::find($formData['_adherent_id']);
                        $dataMail = ['nom' => $entity->nom, 'prenom' => $entity->prenom, 'numero' => $entity->numero_adherent];
                    }

                    if ($entity) {
                        Mail::send('emails.admin_nouvelle_inscription', $dataMail, function ($message) {
                            $message->to('direction@savoirsvivants.fr')->subject('🎉 Nouvelle inscription - Savoirs Vivants');
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

        $ticket = null;
        if ($step === 11 && ($formData['mode_paiement'] ?? '') === 'interne' && !$isStructure) {
            $adherentCree = Adherent::find($formData['_adherent_id'] ?? null);
            $inscription  = $adherentCree ? $adherentCree->inscriptions()->latest()->first() : null;

            $activitesTicket = Activite::whereIn('id', $formData['activites_selectionnees'] ?? [])->get();
            $ressourceriesTicket = Ressourcerie::whereIn('id', $formData['ressourcerie_selectionnees'] ?? [])->get();

            $isDrusenheimTicket = $activitesTicket->contains(function ($a) {
                return stripos($a->nom, 'drusenheim') !== false || stripos($a->ville, 'drusenheim') !== false;
            });

            $montantCotisation = $this->getMontantCotisation($formData);

            $isPreInscription = $this->isPreInscription($formData);

            $lignes = [];
            $month = now()->month;
            $totalTicket = 0;
            $isPreInscription = $this->isPreInscription($formData);

            foreach ($activitesTicket as $act) {
                if ($isPreInscription) {
                    $prixAct = 50.0;
                    $lignes[] = ['nom' => $act->nom . ' (Acompte)', 'prix' => $prixAct];
                } else {
                    $prixAct = (float) $act->tarif;
                    if ($act->type === 'activite') {
                        if ($month == 2 || $month == 3) {
                            $prixAct = max(0, $prixAct - 50);
                        } elseif ($month >= 4 && $month <= 6) {
                            $prixAct = ($prixAct / 10) * (7 - $month);
                        }
                    }
                    $lignes[] = ['nom' => $act->nom, 'prix' => $prixAct];
                }
                $totalTicket += $prixAct;
            }

            foreach ($ressourceriesTicket as $ress) {
                $prixRess = (float) $ress->prix;
                $lignes[] = ['nom' => $ress->nom, 'prix' => $prixRess];
                $totalTicket += $prixRess;
            }

            if ($montantCotisation > 0) {
                $lignes[] = [
                    'nom' => 'Adhésion annuelle' . ($isDrusenheimTicket ? ' Club Drusenheim' : ''),
                    'prix' => (float) $montantCotisation
                ];
                $totalTicket += $montantCotisation;
            }

            $ticket = [
                'lignes' => $lignes,
                'total'  => $totalTicket,
            ];
        }

        $moisActuel = now()->month;
        $isPreInscription = ($moisActuel === 7 || $moisActuel === 8);
        $titreFormulaire = $isPreInscription ? 'Formulaire de pré-inscription' : "Formulaire d'adhésion";

        $preInscription = null;
        $resteAPayer = 0;
        $totalVersePreInscrit = 0;

        if ($step === 16 && !empty($formData['_pre_inscription_id'])) {
            $preInscription = Inscription::find($formData['_pre_inscription_id']);
            $adherentPre = Adherent::find($formData['_adherent_id']);

            if ($preInscription && $adherentPre) {

                $totalVersePreInscrit = $adherentPre->paiements()->sum('montant');
                $resteAPayer = max(0, $preInscription->montant - $totalVersePreInscrit);

                $adherentPre->load('activitesActives');
                $nomsActivites = $adherentPre->activitesActives->pluck('nom')->join(', ');

                if (empty($nomsActivites)) {
                    $nomsActivites = match ($preInscription->type_adhesion) {
                        'ressourcerie' => 'Ressourcerie',
                        'recherche'    => 'Recherche participative',
                        'soutien'      => 'Adhésion de soutien',
                        'stage'        => 'Stage',
                        default        => 'Atelier',
                    };
                }

                $preInscription->noms_activites = $nomsActivites;
            }
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
            'classesEligibles',
            'paiement1Done',
            'isStructure',
            'montantStructure',
            'ressourcerieSelectionnees',
            'totalRessourcerieStructure',
            'clubMakerActivites',
            'nbInscritsParActivite',
            'isStructure',
            'ticket',
            'titreFormulaire',
            'preInscription',
            'resteAPayer',
            'totalVersePreInscrit',
            'recherchesDispos'
        ));
    }

    public function envoyerCodeRecup(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);

        if ($validator->fails()) {
            return response()->json(['message' => 'L\'adresse email n\'est pas valide.', 'status'  => 'error'], 422);
        }

        try {
            $adherent = Adherent::where('mail', $request->email)->first();

            if ($adherent) {
                $code = strtoupper(Str::random(6));
                Cache::put("recup_adherent_{$code}", $adherent->numero_adherent, now()->addMinutes(30));
                Mail::to($adherent->mail)->send(new RecupNumeroMail($adherent, $code));
            }

            return response()->json(['message' => 'Si cet email est associé à un compte, un code a été envoyé.', 'status'  => 'success']);
        } catch (\Exception $e) {
            Log::error('envoyerCodeRecup error: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur serveur est survenue. Veuillez réessayer.', 'status'  => 'error'], 500);
        }
    }

    public function next(Request $request, string $token)
    {

        abort_if(empty($token), 403, 'Lien invalide ou expiré.');

        $step     = (int) $request->input('current_step', 1);
        $formData = $request->session()->get("adhesion_{$token}", []);

        if ($step === 1 && $request->input('is_adherent') === 'oui') {
            $input = trim($request->input('numero_adherent'));

            $adherentExistant = null;
            $structureExistante = null;

            if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
                $adherentExistant = Adherent::where('mail', $input)->first();

                if (!$adherentExistant) {
                    $structureExistante = AdherentStructure::where('mail', $input)->first();
                }
            } else {
                $vraiNumero = Cache::get("recup_adherent_{$input}");
                $numeroRecherche = $vraiNumero ?: $input;

                $adherentExistant = Adherent::where('numero_adherent', $numeroRecherche)->first();

                if (!$adherentExistant) {
                    $structureExistante = AdherentStructure::where('numero_adherent', $numeroRecherche)->first();
                }
            }

            if ($adherentExistant) {
                $preInscriptionDb = $adherentExistant->inscriptions()
                    ->where('saison', Saison::current())
                    ->whereIn('a_paye', ['acompte_paye', 'pre_inscrit'])
                    ->latest()
                    ->first();

                if ($preInscriptionDb) {
                    $request->merge([
                        '_pre_inscription_id' => $preInscriptionDb->id,
                        '_adherent_id'        => $adherentExistant->id
                    ]);
                }
            }

            if (!$adherentExistant && !$structureExistante) {
                return back()->withErrors([
                    'numero_adherent' => 'Cet identifiant (numéro, code ou email) est introuvable.'
                ]);
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

        if ($step === 16) {
            $action = $request->input('action_pre_inscription');
            $adherentStep16 = Adherent::find($formData['_adherent_id']);
            $preInsc = Inscription::find($formData['_pre_inscription_id']);

            if ($action === 'pay_balance') {
                $totalVerse = $adherentStep16->paiements()->sum('montant');
                $resteAPayer = max(0, $preInsc->montant - $totalVerse);

                if ($resteAPayer > 0) {
                    $formData['_is_paying_solde'] = true;
                    $formData['_montant_solde']   = $resteAPayer;
                    $request->session()->put("adhesion_{$token}", $formData);

                    $service = app(HelloAssoService::class);
                    $payerInfo = ['prenom' => $adherentStep16->prenom, 'nom' => $adherentStep16->nom, 'mail' => $adherentStep16->mail];

                    try {
                        $urlPaiement = $service->createCheckout((int) round($resteAPayer * 100), $payerInfo, $token, 'adhesion.helloasso.return', 'Solde de rentrée - Savoirs Vivants');
                        return redirect($urlPaiement);
                    } catch (\Exception $e) {
                        return back()->withErrors(['helloasso' => 'Erreur de connexion au service de paiement : ' . $e->getMessage()]);
                    }
                } else {
                    $preInsc->update(['a_paye' => Inscription::EN_ATTENTE]);
                    $formData['_helloasso_ok'] = true;
                    $formData['_last_completed'] = 11;
                    $request->session()->put("adhesion_{$token}", $formData);
                    return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
                }
            } elseif ($action === 'cancel') {
                if ($preInsc) {
                    DB::table('activites_adherents')->where('id_adherent', $adherentStep16->id)->where('saison', Saison::current())->delete();
                    $preInsc->delete();
                }
                $formData['_pre_inscription_handled'] = true;
                $request->session()->put("adhesion_{$token}", $formData);
                return redirect()->route('adhesion.show', ['token' => $token, 'step' => 2]);
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
            $service = app(HelloAssoService::class);

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
                    $totalRessourcerie = Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix');

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
            $isPreInscription  = $this->isPreInscription($formData);

            $totalActiviteEuros = 0;
            if (!empty($activitesIds)) {
                $totalActiviteEuros += $isPreInscription
                    ? (count($activitesIds) * 50.0)
                    : $this->calculerMontantActivites($activitesIds);
            }
            if (!empty($ressourcerieIds)) $totalActiviteEuros += Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix');

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

            $payerPrenom = $formData['prenom'] ?? 'Prénom';
            $payerNom    = $formData['nom']    ?? 'Nom';
            $payerMail   = $formData['mail']   ?? 'email@defaut.fr';

            if (($formData['is_adherent'] ?? 'non') === 'oui' && !empty($formData['numero_adherent'])) {
                $adherentExistant = Adherent::where('numero_adherent', $formData['numero_adherent'])->first();
                if ($adherentExistant) {
                    $payerPrenom = $adherentExistant->prenom;
                    $payerNom    = $adherentExistant->nom;
                    $payerMail   = $adherentExistant->mail;
                }
            }
            $payerInfo = ['prenom' => $payerPrenom, 'nom' => $payerNom, 'mail' => $payerMail];

            if ($totalActiviteEuros > 0) {
                $itemLabel = ($formData['type_activite'] ?? '') === 'ressourcerie'
                    ? 'Ressourcerie - Savoirs Vivants' : 'Inscription Activité - Savoirs Vivants';
                try {
                    $urlPaiement = $service->createCheckout((int) round($totalActiviteEuros * 100), $payerInfo, $token, 'adhesion.helloasso.return', $itemLabel);
                    return redirect($urlPaiement);
                } catch (\Exception $e) {
                    Log::error('HelloAsso Error (Checkout 1): ' . $e->getMessage());
                    return back()->withErrors(['helloasso' => 'Le service de paiement est indisponible pour le moment.']);
                }
            } elseif ($estNouvelAdherent) {
                try {
                    /* L'utilisation de env() dans le code est dangereuse en production
                     * car Laravel le cache. Il faut privilégier config().
                     */
                    $formSlug = Setting::where('key', 'helloasso_membership_form_slug')->value('value')
                        ?? config('services.helloasso.membership_form_slug');
                    $price    = $service->getBaseMembershipPrice($formSlug);

                    if ($price > 0) {
                        $urlPaiement = $service->createCheckout((int) round($price * 100), $payerInfo, $token, 'adhesion.helloasso2.return', 'Adhésion Annuelle - Savoirs Vivants');
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
            $request->validate([
                'carnet_sante' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            $filePath = $request->file('carnet_sante')->store('carnets', 'local');

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

    public function notifierActivitePleine(Request $request, string $token)
    {
        abort_if(!$request->session()->has("adhesion_{$token}"), 403);

        $activiteId = (int) $request->input('activite_id');
        $activite   = Activite::findOrFail($activiteId);
        $formData   = $request->session()->get("adhesion_{$token}", []);

        $prenom = $formData['prenom'] ?? ($formData['nom_correspondant'] ?? 'Prénom inconnu');
        $nom    = $formData['nom']    ?? '';
        $mail   = $formData['mail']   ?? ($formData['mail_structure'] ?? null);

        try {
            Mail::send('emails.activite_pleine_notification', [
                'activite' => $activite,
                'prenom'   => $prenom,
                'nom'      => $nom,
                'mail'     => $mail,
            ], function ($message) use ($activite) {
                $message->to('direction@savoirsvivants.fr')
                    ->subject("⏳ Demande de pré-inscription — {$activite->nom}");
            });
        } catch (\Exception $e) {
            Log::error("Erreur envoi mail activité pleine : " . $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    public function setSaisonCible(Request $request, string $token)
    {
        $formData = $request->session()->get("adhesion_{$token}", []);

        $formData['_saison_cible'] = $request->input('saison_cible');

        if ($formData['_saison_cible'] === 'actuelle') {
            $formData['type_activite'] = 'stage';
        }

        $request->session()->put("adhesion_{$token}", $formData);

        return back();
    }


    public function voirCarnet(Adherent $adherent)
    {
        if (empty($adherent->carnet)) {
            abort(404, "Aucun carnet de santé enregistré pour cet adhérent.");
        }

        if (!Storage::disk('local')->exists($adherent->carnet)) {
            abort(404, "Le fichier du carnet de santé n'existe plus sur le serveur.");
        }

        return response()->file(storage_path('app/' . $adherent->carnet));
    }
}
