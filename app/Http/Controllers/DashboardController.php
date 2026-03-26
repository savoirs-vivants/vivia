<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $querySeance = DB::table('seances')
            ->join('activites', 'seances.id_activite', '=', 'activites.id')
            ->where('seances.date', '>', now())
            ->select(
                'seances.id_seance',
                'seances.date',
                'activites.nom as activite_nom',
                'activites.adresse',
                'activites.ville',
                DB::raw('(SELECT COUNT(*) FROM activites_adherents WHERE activites_adherents.id_activite = seances.id_activite AND activites_adherents.saison = "' . $saison . '" AND activites_adherents.est_un_abandon = 0) as nb_inscrits')
            );

        if (Auth::user()->role !== 'admin') {
            if ($isGestionnaire) {
                $querySeance->whereIn('activites.id', $mesActivitesIds);
            } else {
                $querySeance->whereRaw('1 = 0');
            }
        }

        $prochaineSeance = null;
        $presencesSeance = [];
        $nbPresencesEnregistrees = 0;

        if ($isGestionnaire) {
            $prochaineSeance = DB::table('seances')
                ->join('activites', 'seances.id_activite', '=', 'activites.id')
                ->where('seances.date', '>', now())
                ->whereIn('activites.id', $mesActivitesIds) 
                ->select(
                    'seances.id_seance',
                    'seances.date',
                    'activites.nom as activite_nom',
                    'activites.adresse',
                    'activites.ville',
                    DB::raw('(SELECT COUNT(*) FROM activites_adherents WHERE activites_adherents.id_activite = seances.id_activite AND activites_adherents.saison = "' . $saison . '" AND activites_adherents.est_un_abandon = 0) as nb_inscrits')
                )
                ->orderBy('seances.date')
                ->first();

            if ($prochaineSeance) {
                $presencesSeance = DB::table('presence')
                    ->join('adherents', 'presence.id_adherent', '=', 'adherents.id')
                    ->where('presence.id_seance', $prochaineSeance->id_seance)
                    ->select('adherents.prenom', 'adherents.nom', 'presence.statut')
                    ->take(3)
                    ->get();

                $nbPresencesEnregistrees = DB::table('presence')
                    ->where('id_seance', $prochaineSeance->id_seance)
                    ->count();
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
            'presencesSeance',
            'nbPresencesEnregistrees',
            'isGestionnaire' // <-- Ne pas oublier de passer la variable
        ));
    }

    private function currentSaison(): string
    {
        $year = now()->month >= 9 ? now()->year : now()->year - 1;
        return $year . '-' . ($year + 1);
    }
}
