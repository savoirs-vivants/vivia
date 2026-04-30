<?php

namespace App\Services;

use App\Models\Activite;
use App\Models\DossierActivite;
use App\Models\Saison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ActiviteService
{
    private function getVacancesStrasbourg(): array
    {
        // On garde le résultat en mémoire pendant 30 jours
        return Cache::remember('vacances_scolaires_strasbourg', now()->addDays(30), function () {
            $url = 'https://data.education.gouv.fr/api/explore/v2.1/catalog/datasets/fr-en-calendrier-scolaire/records';

            try {
                $response = Http::get($url, [
                    'where' => "location='Strasbourg' AND population!='Enseignants'",
                    'order_by' => 'start_date DESC',
                    'limit' => 100,
                ]);

                if ($response->successful()) {
                    $vacances = [];
                    foreach ($response->json('results', []) as $record) {
                        if (isset($record['start_date']) && isset($record['end_date'])) {
                            $vacances[] = [
                                'start' => substr($record['start_date'], 0, 10),
                                'end'   => substr($record['end_date'], 0, 10),
                            ];
                        }
                    }
                    return $vacances;
                }
            } catch (\Exception $e) {
                Log::error("Erreur API Vacances: " . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Vérifie si une date tombe pendant les vacances
     */
    private function isJourDeVacances(Carbon $date): bool
    {
        $vacances = $this->getVacancesStrasbourg();
        $dateString = $date->format('Y-m-d');

        foreach ($vacances as $periode) {
            if ($dateString >= $periode['start'] && $dateString < $periode['end']) {
                return true;
            }
        }

        return false;
    }

    public function preparerDonneesActivite(Request $request): array
    {
        $validated = $request->validated();
        $horaires = [];

        if ($validated['type'] === 'stage') {
            $horaires['stage'] = [
                'date_debut'  => $request->input('date_debut_stage'),
                'date_fin'    => $request->input('date_fin_stage'),
                'heure_debut' => $request->input('heure_debut_stage'),
                'heure_fin'   => $request->input('heure_fin_stage'),
            ];
        } else {
            $jours = $request->input('jours', []);
            $debuts = $request->input('debuts', []);
            $fins = $request->input('fins', []);

            foreach ($jours as $index => $jour) {
                if (!empty($jour) && !empty($debuts[$index]) && !empty($fins[$index])) {
                    $plage = $debuts[$index] . '-' . $fins[$index];
                    $horaires[$jour] = isset($horaires[$jour]) ? $horaires[$jour] . ', ' . $plage : $plage;
                }
            }
        }

        $idDossier = null;
        if ($request->input('dossier_action') === 'existing') {
            $idDossier = $request->input('id_dossier');
        } elseif ($request->input('dossier_action') === 'new' && $request->filled('nouveau_dossier')) {
            $idDossier = DossierActivite::create(['nom' => $request->input('nouveau_dossier')])->id;
        }

        return [
            'type'       => $validated['type'],
            'nom'        => $validated['nom'],
            'tarif'      => $validated['tarif'],
            'max_eleves' => $validated['max_eleves'] ?? null,
            'adresse'    => $validated['adresse'],
            'ville'      => $validated['ville'],
            'horaires'   => empty($horaires) ? null : $horaires,
            'classes'    => $request->input('classes') ?: null,
            'id_dossier' => $idDossier,
        ];
    }

    public function regenererFuturesSeances(Activite $activite)
    {
        $futuresSeances = DB::table('seances')
            ->where('id_activite', $activite->id)
            ->where('date', '>=', now()->startOfDay())
            ->pluck('id_seance');

        if ($futuresSeances->isNotEmpty()) {
            $seancesAvecAppel = DB::table('presence')
                ->whereIn('id_seance', $futuresSeances)
                ->pluck('id_seance')
                ->unique();

            $seancesASupprimer = $futuresSeances->diff($seancesAvecAppel);

            DB::table('seances')->whereIn('id_seance', $seancesASupprimer)->delete();
        }

        $this->genererSeancesAuto($activite, true);
    }

    public function genererSeancesAuto(Activite $activite, $depuisAujourdhui = false)
    {
        $horaires = is_string($activite->horaires) ? json_decode($activite->horaires, true) : $activite->horaires;
        if (empty($horaires) || !is_array($horaires)) return;

        $nouvellesSeances = [];

        if ($activite->type === 'stage' && isset($horaires['stage'])) {
            $debutStage = $horaires['stage']['date_debut'] ?? null;
            $finStage   = $horaires['stage']['date_fin'] ?? null;
            $heureDebut = $horaires['stage']['heure_debut'] ?? '00:00';

            if (!$debutStage || !$finStage) return;

            $dateCourante = Carbon::parse($debutStage);
            $dateFin      = Carbon::parse($finStage);

            if ($depuisAujourdhui && $dateCourante->isPast()) {
                $dateCourante = now()->startOfDay();
            }

            while ($dateCourante->lte($dateFin)) {
                $nouvellesSeances[] = [
                    'id_activite' => $activite->id,
                    'date'        => $dateCourante->format('Y-m-d') . ' ' . $heureDebut . ':00',
                ];
                $dateCourante->addDay();
            }
        } else {
            $joursMap = [
                'Lundi' => Carbon::MONDAY,
                'Mardi' => Carbon::TUESDAY,
                'Mercredi' => Carbon::WEDNESDAY,
                'Jeudi' => Carbon::THURSDAY,
                'Vendredi' => Carbon::FRIDAY,
                'Samedi' => Carbon::SATURDAY,
                'Dimanche' => Carbon::SUNDAY,
            ];

            $saisonActive = Saison::where('nom', Saison::current())->first();

            $finGeneration = $saisonActive
                ? Carbon::parse($saisonActive->date_fin)
                : Carbon::create(now()->month >= 7 ? now()->year + 1 : now()->year, 6, 30);

            $debutSaison = $saisonActive
                ? Carbon::parse($saisonActive->date_debut)->startOfDay()
                : now()->startOfDay();

            $rentree = clone $debutSaison;
            if ($rentree->month === 7 || $rentree->month === 8) {
                $rentree->month(9)->day(1);
            }

            if ($depuisAujourdhui) {
                $baseDate = now()->startOfDay();
                if ($baseDate->lessThan($rentree)) {
                    $baseDate = clone $rentree;
                }
            } else {
                $baseDate = clone $rentree;
            }

            foreach ($horaires as $jour => $plagesStr) {
                if (!isset($joursMap[$jour])) continue;

                $plages = explode(',', $plagesStr);
                $dateCourante = clone $baseDate;

                if ($dateCourante->dayOfWeekIso !== $joursMap[$jour]) {
                    $dateCourante->next($joursMap[$jour]);
                }

                while ($dateCourante->lte($finGeneration)) {

                    if (!$this->isJourDeVacances($dateCourante)) {
                        foreach ($plages as $plage) {
                            $heureDebut = trim(explode('-', $plage)[0]);
                            $nouvellesSeances[] = [
                                'id_activite' => $activite->id,
                                'date'        => $dateCourante->format('Y-m-d') . ' ' . $heureDebut . ':00',
                            ];
                        }
                    }

                    $dateCourante->addWeek();
                }
            }
        }

        /* Bulk Insert. Au lieu de faire 40 requêtes `insert()` dans une boucle (très lent),
         * on insère tout le tableau en une seule requête SQL. `insertOrIgnore` permet de
         * ne pas écraser ni doubler les séances qui existent déjà si l'activité est mise à jour.
         */
        if (!empty($nouvellesSeances)) {
            $datesExistantes = DB::table('seances')->where('id_activite', $activite->id)->pluck('date')->toArray();

            $aInserer = array_filter($nouvellesSeances, function ($seance) use ($datesExistantes) {
                return !in_array($seance['date'], $datesExistantes);
            });

            if (!empty($aInserer)) {
                DB::table('seances')->insert($aInserer);
            }
        }
    }
}
