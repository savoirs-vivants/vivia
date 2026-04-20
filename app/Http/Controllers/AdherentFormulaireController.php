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
use \Illuminate\Support\Facades\Storage;
use App\Services\HelloAssoService;
use App\Services\GeocodingService;
use App\Models\SyncLog;
use App\Models\Setting;

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

    private function isPreInscription(array $formData): bool
    {
        $moisActuel = now()->month;
        return ($moisActuel === 7 || $moisActuel === 8) && ($formData['type_activite'] ?? '') === 'atelier';
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

    private function isStructure(array $formData): bool
    {
        return in_array($formData['statut_juridique'] ?? '', ['tpe_asso', 'esr_pme']);
    }

    private function montantStructure(array $formData): int
    {
        if (($formData['is_adherent'] ?? 'non') === 'oui') return 0;
        return ($formData['statut_juridique'] ?? '') === 'esr_pme' ? 200 : 50;
    }

    private function getMontantCotisation(array $formData): int
    {
        if (($formData['is_adherent'] ?? 'non') === 'oui') return 0;
        if (($formData['type_activite'] ?? '') === 'club_maker') return 0;

        $activiteIds = array_filter((array) ($formData['activites_selectionnees'] ?? []));
        if (empty($activiteIds)) return 10;

        $isDrusenheim = Activite::whereIn('id', $activiteIds)
            ->where(function ($q) {
                $q->where('nom', 'like', '%drusenheim%')
                    ->orWhere('ville', 'like', '%drusenheim%');
            })->exists();

        return $isDrusenheim ? 17 : 10;
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
        if (!$isClubMaker) $path[] = 7;
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
                        $dataMail = ['nom' => $entity->nom, 'prenom' => '', 'numero' => $entity->numero_adherent];
                    } elseif (!empty($formData['_adherent_id'])) {
                        $entity = Adherent::find($formData['_adherent_id']);
                        $dataMail = ['nom' => $entity->nom, 'prenom' => $entity->prenom, 'numero' => $entity->numero_adherent];
                    }

                    if ($entity) {
                        Mail::send('emails.admin_nouvelle_inscription', $dataMail, function ($message) {
                            $message->to('stantrebes@gmail.com')->subject('🎉 Nouvelle inscription - Savoirs Vivants');
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
            'totalVersePreInscrit'
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
            $inputNumero     = trim($request->input('numero_adherent'));
            $vraiNumero      = Cache::get("recup_adherent_{$inputNumero}");
            $numeroRecherche = $vraiNumero ?: $inputNumero;

            $adherentExistant   = Adherent::where('numero_adherent', $numeroRecherche)->first();

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
            Storage::disk('public')->makeDirectory('carnets');
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

        if (!empty($formData['_is_paying_solde'])) {
            $adherentSolde = Adherent::find($formData['_adherent_id']);
            $preInscSolde  = Inscription::find($formData['_pre_inscription_id']);

            if ($adherentSolde && $preInscSolde) {
                Paiement::create([
                    'id_adherent'   => $adherentSolde->id,
                    'montant'       => $formData['_montant_solde'],
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Restant de l\'acompte payé via HelloAsso',
                ]);
                $preInscSolde->update(['a_paye' => Inscription::EN_ATTENTE]);
            }

            $formData['_helloasso_ok'] = true;
            $formData['_last_completed'] = 11;
            unset($formData['_is_paying_solde']);
            $request->session()->put("adhesion_{$token}", $formData);

            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
        }

        if ($this->isStructure($formData)) {
            if (($formData['type_activite'] ?? '') === 'ressourcerie' && empty($formData['_ressourcerie_paid'])) {
                $formData['_ressourcerie_paid'] = true;

                if (!empty($formData['_structure_id'])) {
                    $ressourcerieIds     = $formData['ressourcerie_selectionnees'] ?? [];
                    $montantRessourcerie = Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix');

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
            $activiteIds       = array_filter((array) ($formData['activites_selectionnees'] ?? []));
            $ressourcerieIds   = array_filter((array) ($formData['ressourcerie_selectionnees'] ?? []));
            $isPreInscription  = $this->isPreInscription($formData);

            $totalActivites    = $isPreInscription ? (count($activiteIds) * 50.0) : $this->calculerMontantActivites($activiteIds);
            $totalRessourcerie = !empty($ressourcerieIds) ? Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix') : 0;
            $montantActivite   = (float) ($totalActivites + $totalRessourcerie);

            if ($montantActivite > 0) {
                $commentaireDynamique = $isPreInscription
                    ? 'Acompte pré-inscription'
                    : $this->genererCommentairePaiement($activiteIds, $ressourcerieIds);

                Paiement::create([
                    'id_adherent'   => $formData['_adherent_id'],
                    'montant'       => $montantActivite,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => $commentaireDynamique,
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
        $basePublic = $isSandbox ? 'https://www.helloasso-sandbox.com' : 'https://www.helloasso.com';
        $orgSlug    = config('services.helloasso.org_slug');

        $formSlug = Setting::where('key', 'helloasso_membership_form_slug')->value('value')
            ?? config('services.helloasso.membership_form_slug');

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
            $cotisation = $this->getMontantCotisation($formData);

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
        $cotisation = $this->getMontantCotisation($formData);

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
                $message->to('stantrebes@gmail.com')
                    ->subject("⏳ Demande de pré-inscription — {$activite->nom}");
            });
        } catch (\Exception $e) {
            Log::error("Erreur envoi mail activité pleine : " . $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    private function sauvegarderAdherent(array $formData): int
    {
        /* L'inscription d'un adhérent touche de nombreuses tables. On utilise
         * une transaction BDD pour garantir que l'adhérent n'est pas créé "à moitié"
         * si une erreur survient au milieu du processus.
         */
        return DB::transaction(function () use ($formData) {
            $isAdherent      = ($formData['is_adherent'] ?? 'non') === 'oui';
            $typeActivite    = $formData['type_activite'] ?? '';
            $activiteIds     = array_filter((array) ($formData['activites_selectionnees'] ?? []));
            $ressourcerieIds = array_filter((array) ($formData['ressourcerie_selectionnees'] ?? []));
            $saison          = Saison::current();
            $aPaye           = Inscription::EN_ATTENTE;

            $autresTouteurs = [];

            if (!$isAdherent) {
                foreach ((array) ($formData['tuteurs'] ?? []) as $t) {
                    $type = $t['type'] ?? 'parent_tuteur';
                    $tuteur = Tuteur::create([
                        'type'           => $type,
                        'nom'            => $t['nom'] ?? '',
                        'prenom'         => $t['prenom'] ?? '',
                        'tel'            => $t['tel'] ?? null,
                        'mail'           => $t['mail'] ?? null,
                        'profession'     => $t['profession'] ?? null,
                        'adhere'         => !empty($t['adhere']),
                        'rentre_fin'     => !empty($t['rentre_fin']),
                        'rentre_annul'   => !empty($t['rentre_annul']),
                        'date_signature' => $type === 'parent_tuteur' ? ($t['date_signature'] ?? null) : null,
                        'signature'      => $type === 'parent_tuteur' ? ($t['signature'] ?? null) : null,
                    ]);
                    $autresTouteurs[] = $tuteur->id;
                }
            }

            /* L'appel au service de Géocodage peut échouer ou timeout.
             * On le met dans un try-catch pour que l'inscription passe coûte que coûte.
             */
            $coords = null;
            try {
                $geocoder = new GeocodingService();
                $coords = $geocoder->getCoordinates($formData['adresse'] ?? null, $formData['code_postal'] ?? null, $formData['ville'] ?? null);
            } catch (\Exception $e) {
                Log::warning("Échec du géocodage silencieux : " . $e->getMessage());
            }

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
                    $updateData['age'] = Carbon::parse($formData['date_naiss'])->age;
                }
                if (isset($formData['carnet_sante_path'])) $updateData['carnet'] = $formData['carnet_sante_path'];
                if (isset($formData['signature_adherent'])) $updateData['signature'] = $formData['signature_adherent'];
                if (isset($formData['actions_benevoles'])) $updateData['actions'] = json_encode($formData['actions_benevoles']);

                if (isset($formData['adresse']) || isset($formData['ville'])) {
                    $updateData['latitude']  = $coords ? $coords['lat'] : $adherent->latitude;
                    $updateData['longitude'] = $coords ? $coords['lng'] : $adherent->longitude;
                }

                $updateData['bulletin']      = !empty($formData['bulletin'] ?? false);
                $updateData['communication'] = !empty($formData['communication'] ?? false);
                $updateData['manif']         = ($formData['participation_manif'] ?? '0') === '1';

                if (!empty($updateData)) {
                    $adherent->update($updateData);
                }

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
                        // On attache les tuteurs (Eloquent gère les doublons silencieusement avec syncWithoutDetaching)
                        $adherent->tousLesTuteurs()->syncWithoutDetaching([$tuteur->id]);
                    }
                }
            } else {
                $age = !empty($formData['date_naiss']) ? Carbon::parse($formData['date_naiss'])->age : null;

                $adherent = Adherent::create([
                    'numero_adherent'           => Adherent::genererNumeroUnique(),
                    'nom'                       => $formData['nom'] ?? '',
                    'prenom'                    => $formData['prenom'] ?? '',
                    'genre'                     => $formData['genre'] ?? null,
                    'date_naiss'                => $formData['date_naiss'] ?? null,
                    'age'                       => $age,
                    'adresse'                   => $formData['adresse'] ?? null,
                    'code_postal'               => $formData['code_postal'] ?? null,
                    'ville'                     => $formData['ville'] ?? null,
                    'tel'                       => $formData['tel'] ?? null,
                    'mail'                      => $formData['mail'] ?? null,
                    'regime_social'             => $formData['regime_social'] ?? null,
                    'occupation'                => $formData['occupation'] ?? null,
                    'etablissement'             => $formData['etablissement'] ?? null,
                    'carnet'                    => $formData['carnet_sante_path'] ?? null,
                    'problemes_sante'           => $formData['problemes_sante'] ?? null,
                    'allergies'                 => $formData['allergies'] ?? null,
                    'conduite_a_tenir'          => $formData['conduite_a_tenir'] ?? null,
                    'restrictions_alimentaires' => $formData['restrictions_alimentaires'] ?? null,
                    'bulletin'                  => !empty($formData['bulletin']),
                    'communication'             => !empty($formData['communication']),
                    'manif'                     => ($formData['participation_manif'] ?? '0') === '1',
                    'actions'                   => json_encode($formData['actions_benevoles'] ?? []),
                    'signature'                 => $formData['signature_adherent'] ?? null,
                    'idee_metier'               => $formData['idee_metier'] ?? null,
                    'decouverte_metier'         => $formData['decouverte_metier'] ?? null,
                    'latitude'                  => $coords ? $coords['lat'] : null,
                    'longitude'                 => $coords ? $coords['lng'] : null,
                ]);

                if (!empty($autresTouteurs)) {
                    $adherent->tousLesTuteurs()->attach($autresTouteurs);
                }
            }

            $isPreInscription    = $this->isPreInscription($formData);
            $aPaye               = $isPreInscription ? 'pre_inscrit' : 'En attente';

            $montantReelActivites = $this->calculerMontantActivites($activiteIds);
            $montantRessourcerie  = !empty($ressourcerieIds) ? Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix') : 0;
            $cotisation           = $this->getMontantCotisation($formData);
            $montantTotalReel     = (float) ($montantReelActivites + $montantRessourcerie + $cotisation);

            Inscription::create([
                'id_adherent'      => $adherent->id,
                'saison'           => $saison,
                'date_inscription' => now()->toDateString(),
                'type_adhesion'    => $typeActivite,
                'a_paye'           => $aPaye,
                'montant'          => $montantTotalReel,
                'renouvellement'   => $isAdherent,
            ]);

            /* Bulk Insert pour éviter de multiplier les requêtes SQL (N+1)
             * lors de l'enregistrement de plusieurs activités en même temps.
             */
            if (!empty($activiteIds)) {
                $pivotData = array_map(fn($idActivite) => [
                    'id_adherent'    => $adherent->id,
                    'id_activite'    => $idActivite,
                    'saison'         => $saison,
                    'date_entree'    => now()->toDateString(),
                    'est_un_abandon' => 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ], $activiteIds);

                DB::table('activites_adherents')->insertOrIgnore($pivotData);
            }

            if (!empty($formData['_helloasso_ok'])) {
                $montantActivitePaye = $isPreInscription
                    ? (count($activiteIds) * 50.0) + $montantRessourcerie
                    : $montantReelActivites + $montantRessourcerie;

                if ($montantActivitePaye > 0) {
                    $commentaireDynamique = $isPreInscription
                        ? 'Acompte pré-inscription via HelloAsso'
                        : $this->genererCommentairePaiement($activiteIds, $ressourcerieIds);

                    Paiement::create([
                        'id_adherent'   => $adherent->id,
                        'montant'       => $montantActivitePaye,
                        'source'        => 'HelloAsso',
                        'date_paiement' => now()->toDateString(),
                        'commentaire'   => $commentaireDynamique,
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
        });
    }

    private function genererCommentairePaiement(array $activiteIds, array $ressourcerieIds): string
    {
        $types = [];

        if (!empty($activiteIds)) {
            $activites = Activite::whereIn('id', $activiteIds)->get();

            if ($activites->where('type', 'activite')->isNotEmpty()) {
                $types[] = 'activité';
            }
            if ($activites->where('type', 'stage')->isNotEmpty()) {
                $types[] = 'stage';
            }
        }

        if (!empty($ressourcerieIds)) {
            $types[] = 'ressourcerie';
        }

        if (empty($types)) {
            return "Paiement via HelloAsso";
        }

        return 'Paiement ' . implode(' et ', $types) . ' via HelloAsso';
    }

    private function sauvegarderStructure(array $formData): int
    {
        return DB::transaction(function () use ($formData) {
            $statutActivite = match ($formData['type_activite'] ?? '') {
                'ressourcerie' => 'ressourcerie',
                'soutien'      => 'soutien',
                default        => 'participation',
            };

            $structure = AdherentStructure::create([
                'numero_adherent'    => AdherentStructure::genererNumeroUnique(),
                'nom'                => $formData['nom_structure'] ?? '',
                'sigle'              => $formData['sigle'] ?? null,
                'adresse'            => $formData['adresse_structure'] ?? null,
                'code_postal'        => $formData['code_postal_structure'] ?? null,
                'ville'              => $formData['ville_structure'] ?? null,
                'date_creation'      => $formData['date_creation_structure'] ?? null,
                'tel'                => $formData['tel_structure'] ?? null,
                'tel_portable'       => $formData['tel_portable_structure'] ?? null,
                'mail'               => $formData['mail_structure'] ?? null,
                'site_web'           => $formData['site_web'] ?? null,
                'nom_correspondant'  => $formData['nom_correspondant'] ?? null,
                'tel_correspondant'  => $formData['tel_correspondant'] ?? null,
                'bulletin'           => (bool) ($formData['bulletin'] ?? false),
                'autorisation_photo' => (bool) ($formData['autorisation_photo'] ?? false),
                'signature'          => $formData['signature_adherent'] ?? null,
                'statut'             => $statutActivite,
                'statut_juridique'   => $formData['statut_juridique'] ?? null,
            ]);

            $saison = $this->determinerSaisonDynamique($formData);
            $aPaye = Inscription::EN_ATTENTE;

            Inscription::create([
                'id_structure'     => $structure->id,
                'saison'           => $saison,
                'date_inscription' => now()->toDateString(),
                'type_adhesion'    => $formData['type_activite'] ?? 'soutien',
                'a_paye'           => $aPaye,
                'montant'          => $this->montantStructure($formData),
            ]);

            return $structure->id;
        });
    }

    public function helloassoWebhook(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['eventType'] ?? null;
        $state     = $payload['data']['state'] ?? null;

        if ($eventType !== 'Payment' || $state !== 'Authorized') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $syncLog = SyncLog::create([
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
                return response()->json(['status' => 'missing_email'], 200);
            }

            $adherent = Adherent::where('mail', $email)->latest()->first();
            $structure = AdherentStructure::where('mail', $email)->latest()->first();

            if ($adherent) {
                $dejaCree = Paiement::where('id_adherent', $adherent->id)
                    ->where('source', 'HelloAsso')
                    ->where('montant', $amount)
                    ->whereDate('date_paiement', today())
                    ->exists();

                if (!$dejaCree) {
                    Paiement::create([
                        'id_adherent'   => $adherent->id,
                        'montant'       => $amount,
                        'source'        => 'HelloAsso',
                        'date_paiement' => now()->toDateString(),
                        'commentaire'   => 'Paiement Webhook HelloAsso',
                    ]);
                }

                $syncLog->update(['status' => 'success', 'payments_imported' => 1]);
                return response()->json(['status' => 'ok'], 200);
            }

            if ($structure) {
                $dejaCreeeStruct = Paiement::where('id_structure', $structure->id)
                    ->where('source', 'HelloAsso')
                    ->where('montant', $amount)
                    ->whereDate('date_paiement', today())
                    ->exists();

                if (!$dejaCreeeStruct) {
                    Paiement::create([
                        'id_structure'  => $structure->id,
                        'montant'       => $amount,
                        'source'        => 'HelloAsso',
                        'date_paiement' => now()->toDateString(),
                    ]);
                }

                $syncLog->update(['status' => 'success', 'payments_imported' => 1]);
                return response()->json(['status' => 'ok'], 200);
            }

            $syncLog->update([
                'status' => 'warning',
                'errors' => ["Paiement de {$amount}€ reçu pour {$firstName} {$lastName}, mais l'email {$email} est introuvable."]
            ]);

            return response()->json(['status' => 'not_found_but_acknowledged'], 200);
        } catch (\Exception $e) {
            Log::error("Erreur critique Webhook HelloAsso : " . $e->getMessage());

            $syncLog->update([
                'status' => 'error',
                'errors' => ["Erreur serveur durant le traitement : " . $e->getMessage()]
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    private function calculerMontantActivites(array $activiteIds): float
    {
        if (empty($activiteIds)) return 0.0;

        $activites = Activite::whereIn('id', $activiteIds)->get();
        $total = 0.0;
        $month = now()->month;

        foreach ($activites as $activite) {
            $prix = (float) $activite->tarif;

            if ($activite->type === 'activite') {
                if ($month == 2 || $month == 3) {
                    $prix = max(0, $prix - 50);
                } elseif ($month >= 4 && $month <= 6) {
                    $moisRestants = 7 - $month;
                    $prix = ($prix / 10) * $moisRestants;
                }
            }

            $total += $prix;
        }

        return $total;
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

    private function determinerSaisonDynamique(array $formData): string
    {
        if (($formData['_saison_cible'] ?? 'actuelle') === 'suivante') {
            return Saison::preinscriptions(); // 2026-2027
        }

        return Saison::current();
    }
}
