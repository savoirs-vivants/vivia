<?php

namespace App\Http\Controllers;

use App\Mail\EnfantAbsent;
use App\Models\Adherent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function index()
    {
        $saison = $this->currentSaison();
        $mesActivitesIds = DB::table('activites_gestionnaire')
            ->where('id_users', Auth::id())
            ->pluck('id_activite');

        $isGestionnaire = $mesActivitesIds->isNotEmpty();

        // ── KPIs : Adhérents ──────────────────────────────────────────────
        $totalAdherents = DB::table('inscriptions')
            ->where('saison', $saison)
            ->count();

        $newThisMonth = DB::table('inscriptions')
            ->where('saison', $saison)
            ->whereMonth('date_inscription', now()->month)
            ->whereYear('date_inscription', now()->year)
            ->count();

        // ── Finances ──────────────────────────────────────────────────────
        $adherentsSaison = DB::table('inscriptions')
            ->where('saison', $saison)
            ->pluck('id_adherent');

        $totalGlobal = DB::table('paiement')
            ->whereIn('id_adherent', $adherentsSaison)
            ->sum('montant');

        $totalEnAttente = DB::table('inscriptions')
            ->join('paiement', 'inscriptions.id_adherent', '=', 'paiement.id_adherent')
            ->where('inscriptions.saison', $saison)
            ->where('inscriptions.a_paye', 'En attente')
            ->sum('paiement.montant');

        $totalCotisations = $totalGlobal - $totalEnAttente;

        // ── Statuts d'adhésion ────────────────────────────────────────────
        $statuts = DB::table('inscriptions')
            ->where('saison', $saison)
            ->select('a_paye', DB::raw('COUNT(*) as nb'))
            ->groupBy('a_paye')
            ->pluck('nb', 'a_paye');

        $statutPaye    = $statuts->get('Payé', 0);
        $statutAttente = $statuts->get('En attente', 0);
        $statutPartiel = $statuts->get('Partiel', 0);

        // ── Prochaine séance (gestionnaire) ───────────────────────────────
        $prochaineSeance  = null;
        $adherentsSeance  = collect();
        $absentsSeanceIds = [];

        if ($isGestionnaire) {
            $prochaineSeance = DB::table('seances')
                ->join('activites', 'seances.id_activite', '=', 'activites.id')
                ->whereIn('activites.id', $mesActivitesIds)
                ->where(function ($q) {
                    $q->where('seances.statut', 'appel_fait')
                      ->orWhere(function ($q2) {
                          $q2->where('seances.date', '>', now())
                             ->where(function ($q3) {
                                 $q3->whereNull('seances.statut')
                                    ->orWhere('seances.statut', '!=', 'terminee');
                             });
                      });
                })
                ->select(
                    'seances.id_seance',
                    'seances.id_activite',
                    'seances.date',
                    'seances.statut',
                    'activites.nom as activite_nom',
                    'activites.adresse',
                    'activites.ville',
                    DB::raw('(SELECT COUNT(*) FROM activites_adherents WHERE activites_adherents.id_activite = seances.id_activite AND activites_adherents.saison = "' . $saison . '" AND activites_adherents.est_un_abandon = 0) as nb_inscrits')
                )
                ->orderByRaw("CASE WHEN seances.statut = 'appel_fait' THEN 0 ELSE 1 END")
                ->orderBy('seances.date')
                ->first();

            if ($prochaineSeance) {
                $adherentsSeance = DB::table('activites_adherents')
                    ->join('adherents', 'activites_adherents.id_adherent', '=', 'adherents.id')
                    ->join('inscriptions', function ($join) use ($saison) {
                        $join->on('adherents.id', '=', 'inscriptions.id_adherent')
                             ->where('inscriptions.saison', '=', $saison);
                    })
                    ->where('activites_adherents.id_activite', $prochaineSeance->id_activite)
                    ->where('activites_adherents.est_un_abandon', 0)
                    ->whereNull('activites_adherents.date_sortie')
                    ->where('inscriptions.a_paye', '!=', 'En attente')
                    ->select(
                        'adherents.id',
                        'adherents.nom',
                        'adherents.prenom',
                    )
                    ->distinct()
                    ->orderBy('adherents.nom')
                    ->orderBy('adherents.prenom')
                    ->get();

                $absentsSeanceIds = DB::table('presence')
                    ->where('id_seance', $prochaineSeance->id_seance)
                    ->where('statut', 'Absent')
                    ->pluck('id_adherent')
                    ->toArray();
            }
        }

        // ── TOP 3 ACTIVITÉS ───────────────────────────────────────────────
        $activitesStats = DB::table('activites')
            ->leftJoin('activites_adherents', function ($join) use ($saison) {
                $join->on('activites.id', '=', 'activites_adherents.id_activite')
                    ->where('activites_adherents.saison', '=', $saison)
                    ->where('activites_adherents.est_un_abandon', '=', 0);
            })
            ->select(
                'activites.id',
                'activites.nom',
                'activites.type',
                'activites.horaires',
                DB::raw('COUNT(activites_adherents.id) as total_inscrits')
            )
            ->groupBy('activites.id', 'activites.nom', 'activites.type', 'activites.horaires')
            ->orderByDesc('total_inscrits')
            ->take(3)
            ->get();

        $maxInscrits = $activitesStats->max('total_inscrits') ?: 1;

        $repartitionTypes = DB::table('inscriptions')
            ->where('saison', $saison)
            ->select('type_adhesion', DB::raw('COUNT(*) as total'))
            ->groupBy('type_adhesion')
            ->get();

        return view('dashboard', compact(
            'repartitionTypes',
            'saison',
            'totalAdherents',
            'newThisMonth',
            'activitesStats',
            'maxInscrits',
            'totalCotisations',
            'totalEnAttente',
            'statutPaye',
            'statutAttente',
            'statutPartiel',
            'prochaineSeance',
            'adherentsSeance',
            'absentsSeanceIds',
            'isGestionnaire'
        ));
    }

    /**
     * Enregistrer l'appel (absents) et passer la séance en statut "appel_fait".
     */
    public function enregistrerAppel(Request $request, int $seance)
    {
        $seanceData = DB::table('seances')
            ->join('activites', 'seances.id_activite', '=', 'activites.id')
            ->select('seances.*', 'activites.nom as activite_nom')
            ->where('seances.id_seance', $seance)
            ->first();

        abort_if(!$seanceData, 404);

        $mesActivitesIds = DB::table('activites_gestionnaire')
            ->where('id_users', Auth::id())
            ->pluck('id_activite')
            ->toArray();

        abort_if(!in_array($seanceData->id_activite, $mesActivitesIds), 403);

        $absents = $request->input('absents', []);

        DB::table('presence')->where('id_seance', $seance)->delete();

        Carbon::setLocale('fr');
        $dateFormatee = Carbon::parse($seanceData->date)->isoFormat('dddd D MMMM YYYY à HH:mm');

        foreach ($absents as $absent) {
            $idAdherent = (int) ($absent['id_adherent'] ?? 0);
            if (!$idAdherent) continue;

            DB::table('presence')->insert([
                'id_adherent' => $idAdherent,
                'id_seance'   => $seance,
                'statut'      => 'Absent',
                'raison'      => $absent['motif'] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $adherent = Adherent::with('tousLesTuteurs')->find($idAdherent);

            if ($adherent && in_array($adherent->tranche_age, ['Enfant', 'Adolescent'])) {
                $premierTuteur = $adherent->tousLesTuteurs->first();

                if ($premierTuteur && $premierTuteur->mail) {
                    Mail::to($premierTuteur->mail)
                        ->send(new EnfantAbsent(
                            $adherent->prenom,
                            $seanceData->activite_nom,
                            $dateFormatee
                        ));
                }
            }
            // -----------------------------------------
        }

        DB::table('seances')->where('id_seance', $seance)->update([
            'statut'     => 'terminee',
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Clôturer la séance (fin de l'activité).
     */
    public function terminerSeance(Request $request, int $seance)
    {
        $seanceData = DB::table('seances')->where('id_seance', $seance)->first();
        abort_if(!$seanceData, 404);
        abort_if($seanceData->statut === 'terminee', 422, 'Séance déjà terminée.');

        $mesActivitesIds = DB::table('activites_gestionnaire')
            ->where('id_users', Auth::id())
            ->pluck('id_activite')
            ->toArray();

        abort_if(!in_array($seanceData->id_activite, $mesActivitesIds), 403);

        DB::table('seances')->where('id_seance', $seance)->update([
            'statut'     => 'terminee',
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    private function currentSaison(): string
    {
        $year = now()->month >= 9 ? now()->year : now()->year - 1;
        return $year . '-' . ($year + 1);
    }
}
