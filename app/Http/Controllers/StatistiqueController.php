<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Inscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    public function index(Request $request)
    {
        $saisons = Inscription::select('saison')->distinct()->pluck('saison')->sortDesc()->values();
        $saisonCourante = $request->get('saison', $saisons->first() ?? '2025-2026');

        $indexSaisonCourante = $saisons->search($saisonCourante);
        $saisonPrecedente = $indexSaisonCourante !== false && $saisons->has($indexSaisonCourante + 1) ? $saisons[$indexSaisonCourante + 1] : null;

        $inscriptionsCourantes = Inscription::where('saison', $saisonCourante)->get();
        $totalAdherents = $inscriptionsCourantes->count();

        $adherentsIdsCourants = $inscriptionsCourantes->pluck('id_adherent');
        $adherents = Adherent::with('tousLesTuteurs')->whereIn('id', $adherentsIdsCourants)->get();

        $idsSaisonsPassees = Inscription::where('saison', '<', $saisonCourante)->pluck('id_adherent')->unique();

        $nbReinscrits = 0;
        $nbNouveaux = 0;

        foreach ($adherentsIdsCourants as $id) {
            if ($idsSaisonsPassees->contains($id)) {
                $nbReinscrits++;
            } else {
                $nbNouveaux++;
            }
        }

        $nouveauxInscritsPrec = 0;
        $totalAdherentsPrec = 0;

        if ($saisonPrecedente) {
            $inscriptionsPrecedentes = Inscription::where('saison', $saisonPrecedente)->get();
            $totalAdherentsPrec = $inscriptionsPrecedentes->count();

            $idsAvantPrec = Inscription::where('saison', '<', $saisonPrecedente)->pluck('id_adherent')->unique();
            foreach ($inscriptionsPrecedentes->pluck('id_adherent') as $id) {
                if (!$idsAvantPrec->contains($id)) {
                    $nouveauxInscritsPrec++;
                }
            }
        }

        $nouveauxInscrits = $nbNouveaux;

        $diffTotalAdherents = $totalAdherents - $totalAdherentsPrec;

        $diffNouveaux = $nbNouveaux - $nouveauxInscritsPrec;

        $tauxFidelisation = $totalAdherents > 0 ? round(($nbReinscrits / $totalAdherents) * 100) : 0;


        $ages = $adherents->map(function ($a) {
            return $a->date_naiss ? Carbon::parse($a->date_naiss)->age : null;
        })->filter()->sort()->values();

        $ageMoyen = $ages->count() > 0 ? round($ages->average(), 1) : 0;
        $medianeAge = 0;
        if ($ages->count() > 0) {
            $mid = floor($ages->count() / 2);
            $medianeAge = $ages->count() % 2 == 0 ? ($ages[$mid - 1] + $ages[$mid]) / 2 : $ages[$mid];
        }

        $nbFilles = $adherents->whereIn('genre', ['Fille', 'Femme'])->count();
        $nbGarcons = $adherents->whereIn('genre', ['Garçon', 'Homme'])->count();
        $totalGenre = $nbFilles + $nbGarcons;
        $pctFilles = $totalGenre > 0 ? round(($nbFilles / $totalGenre) * 100) : 0;
        $pctGarcons = $totalGenre > 0 ? round(($nbGarcons / $totalGenre) * 100) : 0;

        $tranchesAge = [
            '< 6 ans' => [0, 5],
            '6-8 ans' => [6, 8],
            '9-11 ans' => [9, 11],
            '12-14 ans' => [12, 14],
            '15-17 ans' => [15, 17],
            '18-25 ans' => [18, 25],
            '26-40 ans' => [26, 40],
            '41-60 ans' => [41, 60],
            '> 60 ans' => [61, 200]
        ];

        $ageData = ['labels' => array_keys($tranchesAge), 'filles' => [], 'garcons' => []];
        foreach ($tranchesAge as $label => $range) {
            $ageData['filles'][] = $adherents->whereIn('genre', ['Fille', 'Femme'])
                ->filter(fn($a) => $a->date_naiss && Carbon::parse($a->date_naiss)->age >= $range[0] && Carbon::parse($a->date_naiss)->age <= $range[1])->count();
            $ageData['garcons'][] = $adherents->whereIn('genre', ['Garçon', 'Homme'])
                ->filter(fn($a) => $a->date_naiss && Carbon::parse($a->date_naiss)->age >= $range[0] && Carbon::parse($a->date_naiss)->age <= $range[1])->count();
        }

        $adherentsAvecCsp = $adherents->filter(function ($a) {
            $parent = $a->tousLesTuteurs->first(function ($t) {
                return $t->type === 'parent_tuteur';
            });

            return $parent && !empty($parent->profession);
        });

        $totalAvecCsp = $adherentsAvecCsp->count();

        $cspData = $adherentsAvecCsp->groupBy(function ($a) {
            $parent = $a->tousLesTuteurs->first(function ($t) {
                return $t->type === 'parent_tuteur';
            });

            return $parent->profession;
        })->map->count()->sortDesc()->take(6)->map(function ($count, $label) use ($totalAvecCsp) {
            return [
                'label' => $label,
                'count' => $count,
                'pct'   => $totalAvecCsp > 0 ? round(($count / $totalAvecCsp) * 100) : 0
            ];
        })->values();

        $quartiersData = $adherents->groupBy(function ($a) {
            return $a->ville ?: 'Non renseigné';
        })->map->count()->sortDesc()->take(6)->map(function ($count, $label) {
            return ['label' => $label, 'count' => $count];
        })->values();

        $abandons = DB::table('activites_adherents')
            ->where('saison', $saisonCourante)
            ->where('est_un_abandon', 1)
            ->distinct('id_adherent')
            ->count('id_adherent');

        $statutData = [
            'reinscrits' => ['count' => $nbReinscrits, 'pct' => $tauxFidelisation],
            'nouveaux'   => ['count' => $nbNouveaux, 'pct' => $totalAdherents > 0 ? round(($nbNouveaux / $totalAdherents) * 100) : 0],
            'abandons'   => ['count' => $abandons, 'pct' => $totalAdherents > 0 ? round(($abandons / $totalAdherents) * 100) : 0],
        ];

        $evolutionData = [
            'labels' => ['Sep', 'Oct', 'Nov', 'Déc', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Jul', 'Aoû'],
            'courante' => array_fill(0, 12, 0),
            'precedente' => array_fill(0, 12, 0)
        ];

        foreach ($inscriptionsCourantes as $insc) {
            if ($insc->date_inscription) {
                $m = Carbon::parse($insc->date_inscription)->month;
                $idx = $m >= 9 ? $m - 9 : $m + 3;
                if ($idx >= 0 && $idx <= 11) {
                    $evolutionData['courante'][$idx]++;
                }
            }
        }

        if ($saisonPrecedente) {
            foreach ($inscriptionsPrecedentes as $insc) {
                if ($insc->date_inscription) {
                    $m = Carbon::parse($insc->date_inscription)->month;
                    $idx = $m >= 9 ? $m - 9 : $m + 3;
                    if ($idx >= 0 && $idx <= 11) {
                        $evolutionData['precedente'][$idx]++;
                    }
                }
            }
        }

        $cumulCourant = 0;
        $cumulPrecedent = 0;
        for ($i = 0; $i < 12; $i++) {
            $cumulCourant += $evolutionData['courante'][$i];
            $evolutionData['courante'][$i] = $cumulCourant;

            $cumulPrecedent += $evolutionData['precedente'][$i];
            $evolutionData['precedente'][$i] = $cumulPrecedent;
        }

        return view('statistiques.index', compact(
            'saisonCourante',
            'saisonPrecedente',
            'totalAdherents',
            'diffTotalAdherents',
            'ageMoyen',
            'medianeAge',
            'pctFilles',
            'pctGarcons',
            'nbFilles',
            'nbGarcons',
            'tauxFidelisation',
            'nouveauxInscrits',
            'diffNouveaux',
            'ageData',
            'cspData',
            'quartiersData',
            'statutData',
            'evolutionData',
            'nbReinscrits'
        ));
    }
}
