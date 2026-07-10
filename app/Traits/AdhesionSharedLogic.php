<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Activite;
use App\Models\Adherent;
use App\Models\AdherentStructure;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Ressourcerie;
use App\Models\Saison;
use App\Models\Tuteur;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait AdhesionSharedLogic
{
    protected function isStructure(array $formData): bool
    {
        return in_array($formData['statut_juridique'] ?? '', ['tpe_asso', 'esr_pme']);
    }

    protected function getTypesActiviteFromFormData(array $formData): array
    {
        if (!empty($formData['types_activite'])) {
            return array_values(array_filter((array) $formData['types_activite']));
        }
        $single = $formData['type_activite'] ?? '';
        return $single ? [$single] : [];
    }

    protected function isPreInscription(array $formData): bool
    {
        $moisActuel = now()->month;
        if ($moisActuel !== 7 && $moisActuel !== 8) return false;
        $types = $this->getTypesActiviteFromFormData($formData);
        return in_array('atelier', $types);
    }

    protected function getMontantCotisation(array $formData): int
    {
        if (($formData['is_adherent'] ?? 'non') === 'oui') return 0;

        $types = $this->getTypesActiviteFromFormData($formData);
        // Club Maker seul = pas de cotisation
        if (count($types) === 1 && in_array('club_maker', $types)) return 0;

        $activiteIds = array_filter((array) ($formData['activites_selectionnees'] ?? []));
        if (empty($activiteIds)) return 10;

        $isDrusenheim = Activite::whereIn('id', $activiteIds)
            ->where(function ($q) {
                $q->where('nom', 'like', '%drusenheim%')
                    ->orWhere('ville', 'like', '%drusenheim%');
            })->exists();

        return $isDrusenheim ? 20 : 10;
    }

    protected function montantStructure(array $formData): int
    {
        if (($formData['is_adherent'] ?? 'non') === 'oui') return 0;
        return ($formData['statut_juridique'] ?? '') === 'esr_pme' ? 200 : 50;
    }

    protected function determinerSaisonDynamique(array $formData): string
    {
        if (($formData['_saison_cible'] ?? 'actuelle') === 'suivante') {
            return Saison::preinscriptions();
        }
        return Saison::current();
    }

    protected function calculerMontantActivites(array $activiteIds): float
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

    protected function genererCommentairePaiement(array $activiteIds, array $ressourcerieIds): string
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

    protected function sauvegarderAdherent(array $formData): int
    {
        /* L'inscription d'un adhérent touche de nombreuses tables. On utilise
         * une transaction BDD pour garantir que l'adhérent n'est pas créé "à moitié"
         * si une erreur survient au milieu du processus.
         */
        return DB::transaction(function () use ($formData) {
            $isAdherent   = ($formData['is_adherent'] ?? 'non') === 'oui';
            $types        = $this->getTypesActiviteFromFormData($formData);
            $typeActivite = $types[0] ?? ($formData['type_activite'] ?? '');
            $activiteIds     = array_filter((array) ($formData['activites_selectionnees'] ?? []));
            $ressourcerieIds = array_filter((array) ($formData['ressourcerie_selectionnees'] ?? []));
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

                $updateData['bulletin']      = $formData['bulletin'] ?? [];
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
                    'bulletin'                  => $formData['bulletin'] ?? [],
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

            // Soutien ou autre inscription hors-atelier en juillet/août → saison suivante, statut en_attente
            $mois   = now()->month;
            $saison = ($mois === 7 || $mois === 8) ? Saison::preinscriptions() : Saison::current();

            $montantReelActivites = $this->calculerMontantActivites($activiteIds);
            $montantRessourcerie  = !empty($ressourcerieIds) ? Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix') : 0;
            $cotisation           = $this->getMontantCotisation($formData);
            $montantTotalReel     = (float) ($montantReelActivites + $montantRessourcerie + $cotisation);

            Inscription::create([
                'id_adherent'       => $adherent->id,
                'saison'            => $saison,
                'date_inscription'  => now()->toDateString(),
                'type_adhesion'     => $typeActivite,
                'types_activite'    => $types,
                'ressourceries_ids' => !empty($ressourcerieIds) ? array_values($ressourcerieIds) : null,
                'a_paye'            => $aPaye,
                'montant'           => $montantTotalReel,
                'renouvellement'    => $isAdherent,
                'is_preinscription' => $isPreInscription,
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
            $rechercheIds = array_filter((array) ($formData['recherches_selectionnees'] ?? []));
            if (!empty($rechercheIds)) {
                $pivotRecherche = array_map(fn($idRech) => [
                    'id_adherent'    => $adherent->id,
                    'id_activite'    => $idRech,
                    'saison'         => $saison,
                    'date_entree'    => now()->toDateString(),
                    'est_un_abandon' => 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ], $rechercheIds);
                DB::table('activites_adherents')->insertOrIgnore($pivotRecherche);
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



    protected function sauvegarderStructure(array $formData): int
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
                'bulletin'           => $formData['bulletin'] ?? [],
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
}
