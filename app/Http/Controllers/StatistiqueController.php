<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Inscription;
use App\Models\Saison;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    public function index(Request $request)
    {
        $saisons = Saison::allSorted();
        $saisonCourante = $request->get('saison', Saison::current());

        $indexSaisonCourante = $saisons->search($saisonCourante);
        $saisonPrecedente = ($indexSaisonCourante !== false && $saisons->has($indexSaisonCourante + 1))
            ? $saisons[$indexSaisonCourante + 1]
            : null;

        $adherentsIdsCourants = Inscription::where('saison', $saisonCourante)
            ->whereNotNull('id_adherent')
            ->whereIn('a_paye', ['oui', 'Payé'])
            ->distinct()
            ->pluck('id_adherent');

        $totalAdherents = $adherentsIdsCourants->count();

        /* Calcul de fidélisation par "Intersection SQL".
         * On demande à la BDD de nous donner le nombre d'IDs
         * qui existent à la fois dans la saison courante ET dans les saisons précédentes.
         */
        $nbReinscrits = Inscription::whereIn('id_adherent', $adherentsIdsCourants)
            ->where('saison', '<', $saisonCourante)
            ->distinct('id_adherent')
            ->count('id_adherent');

        $nbNouveaux = $totalAdherents - $nbReinscrits;

        $totalAdherentsPrec = 0;
        $nouveauxInscritsPrec = 0;
        if ($saisonPrecedente) {
            $adherentsIdsPrec = Inscription::where('saison', $saisonPrecedente)->whereNotNull('id_adherent')->distinct()->pluck('id_adherent');
            $totalAdherentsPrec = $adherentsIdsPrec->count();

            $nbReinscritsPrec = Inscription::whereIn('id_adherent', $adherentsIdsPrec)
                ->where('saison', '<', $saisonPrecedente)
                ->distinct('id_adherent')
                ->count('id_adherent');
            $nouveauxInscritsPrec = $totalAdherentsPrec - $nbReinscritsPrec;
        }

        $diffTotalAdherents = $totalAdherents - $totalAdherentsPrec;
        $diffNouveaux = $nbNouveaux - $nouveauxInscritsPrec;
        $tauxFidelisation = $totalAdherents > 0 ? round(($nbReinscrits / $totalAdherents) * 100) : 0;

        $adherents = Adherent::with('tousLesTuteurs')->whereIn('id', $adherentsIdsCourants)->get();

        $ageStats = DB::table('adherents')
            ->whereIn('id', $adherentsIdsCourants)
            ->selectRaw('
                AVG(TIMESTAMPDIFF(YEAR, date_naiss, CURDATE())) as average_age,
                COUNT(CASE WHEN genre IN ("Fille", "Femme") THEN 1 END) as count_filles,
                COUNT(CASE WHEN genre IN ("Garçon", "Homme") THEN 1 END) as count_garcons
            ')
            ->first();

        $ageMoyen = round($ageStats->average_age, 1);

        $allAges = $adherents->map(fn($a) => $a->date_naiss ? Carbon::parse($a->date_naiss)->age : null)
            ->filter()
            ->sort()
            ->values();

        $medianeAge = 0;
        if ($allAges->count() > 0) {
            $mid = floor($allAges->count() / 2);
            $medianeAge = ($allAges->count() % 2 == 0)
                ? ($allAges[$mid - 1] + $allAges[$mid]) / 2
                : $allAges[$mid];
        }

        $nbFilles = $ageStats->count_filles;
        $nbGarcons = $ageStats->count_garcons;
        $totalGenre = $nbFilles + $nbGarcons;
        $pctFilles = $totalGenre > 0 ? round(($nbFilles / $totalGenre) * 100) : 0;
        $pctGarcons = $totalGenre > 0 ? round(($nbGarcons / $totalGenre) * 100) : 0;

        $tranchesAge = [
            '< 6 ans' => [0, 5], '6-8 ans' => [6, 8], '9-11 ans' => [9, 11],
            '12-14 ans' => [12, 14], '15-17 ans' => [15, 17], '18-25 ans' => [18, 25],
            '26-40 ans' => [26, 40], '41-60 ans' => [41, 60], '> 60 ans' => [61, 200]
        ];

        $ageData = ['labels' => array_keys($tranchesAge), 'filles' => [], 'garcons' => []];
        $adherentsAges = $adherents->map(fn($a) => [
            'genre' => $a->genre,
            'age' => $a->date_naiss ? Carbon::parse($a->date_naiss)->age : null
        ]);

        foreach ($tranchesAge as $label => $range) {
            $ageData['filles'][] = $adherentsAges->whereIn('genre', ['Fille', 'Femme'])->whereBetween('age', $range)->count();
            $ageData['garcons'][] = $adherentsAges->whereIn('genre', ['Garçon', 'Homme'])->whereBetween('age', $range)->count();
        }

        /* On nettoie les espaces et la casse pour éviter d'avoir "Artisan" et "artisan"
         * en deux lignes séparées dans le graphique.
         */
        $cspData = DB::table('tuteur')
            ->join('adherent_tuteurs', 'tuteur.id', '=', 'adherent_tuteurs.id_tuteur')
            ->whereIn('adherent_tuteurs.id_adherent', $adherentsIdsCourants)
            ->where('tuteur.type', 'parent_tuteur')
            ->whereNotNull('tuteur.profession')
            ->selectRaw('LOWER(TRIM(profession)) as label, COUNT(*) as count')
            ->groupBy('label')
            ->orderByDesc('count')
            ->take(6)
            ->get()
            ->map(fn($item) => [
                'label' => ucfirst($item->label),
                'count' => $item->count,
                'pct'   => $totalAdherents > 0 ? round(($item->count / $totalAdherents) * 100) : 0
            ]);

        $quartiersData = $adherents->groupBy(fn($a) => $a->ville ?: 'Non renseigné')
            ->map->count()->sortDesc()->take(6)
            ->map(fn($count, $label) => ['label' => $label, 'count' => $count])
            ->values();

        $abandons = DB::table('activites_adherents')
            ->where('saison', $saisonCourante)
            ->where('est_un_abandon', 1)
            ->distinct('id_adherent')
            ->count('id_adherent');

        $statutData = [
            'reinscrits' => ['count' => $nbReinscrits, 'pct' => $tauxFidelisation],
            'nouveaux'   => ['count' => $nbNouveaux,   'pct' => $totalAdherents > 0 ? round(($nbNouveaux / $totalAdherents) * 100) : 0],
            'abandons'   => ['count' => $abandons,     'pct' => $totalAdherents > 0 ? round(($abandons / $totalAdherents) * 100) : 0],
        ];

        $evolutionData = [
            'labels' => ['Sep', 'Oct', 'Nov', 'Déc', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Jul', 'Aoû'],
            'courante' => array_fill(0, 12, 0),
            'precedente' => array_fill(0, 12, 0)
        ];

        $this->remplirEvolution($evolutionData, 'courante', Inscription::where('saison', $saisonCourante)->get());
        if ($saisonPrecedente) {
            $this->remplirEvolution($evolutionData, 'precedente', Inscription::where('saison', $saisonPrecedente)->get());
        }

        $mapData = $adherents->filter(fn($a) => !is_null($a->latitude))
            ->map(fn($a) => ['lat' => (float)$a->latitude, 'lng' => (float)$a->longitude])
            ->values();

        return view('statistiques.index', compact(
            'saisons', 'saisonCourante', 'saisonPrecedente', 'totalAdherents', 'diffTotalAdherents',
            'ageMoyen', 'medianeAge',
            'pctFilles', 'pctGarcons', 'nbFilles', 'nbGarcons', 'tauxFidelisation',
            'nbReinscrits', 'mapData', 'ageData', 'cspData', 'quartiersData',
            'statutData', 'evolutionData'
        ) + ['nouveauxInscrits' => $nbNouveaux, 'diffNouveaux' => $diffNouveaux]);
    }

    private function remplirEvolution(array &$data, string $key, $inscriptions)
    {
        $cumul = 0;
        $tempMois = array_fill(0, 12, 0);

        foreach ($inscriptions as $insc) {
            if ($insc->date_inscription) {
                $m = Carbon::parse($insc->date_inscription)->month;
                $idx = $m >= 9 ? $m - 9 : $m + 3;
                if ($idx >= 0 && $idx <= 11) $tempMois[$idx]++;
            }
        }

        for ($i = 0; $i < 12; $i++) {
            $cumul += $tempMois[$i];
            $data[$key][$i] = $cumul;
        }
    }
}
