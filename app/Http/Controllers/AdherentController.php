<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Inscription;
use App\Models\Presence;
use Illuminate\Http\Request;

class AdherentController extends Controller
{
    public function index(Request $request)
    {
        $tab          = $request->get('tab', 'payes');
        $search       = $request->get('q');
        $filterSource = $request->get('source');
        $filterStatut = $request->get('statut');

        $base = Adherent::with(['inscription', 'activitesActives', 'paiements'])
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%{$search}%")
                    ->orWhere('nom',   'like', "%{$search}%")
                    ->orWhere('mail',  'like', "%{$search}%");
            }))
            ->when(
                $filterSource && $filterSource !== 'Tous',
                fn($q) =>
                $q->whereHas('paiements', fn($q) => $q->where('source', $filterSource))
            );

        $queryPayes = (clone $base)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PAYE))
            ->when(
                $filterStatut && $filterStatut !== 'Tous',
                fn($q) =>
                $q->whereHas('inscriptions', fn($q) => $q->where('a_paye', $filterStatut))
            );

        $queryAttente = (clone $base)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::EN_ATTENTE));

        $countPayes   = (clone $queryPayes)->count();
        $countAttente = (clone $queryAttente)->count();

        $adherentsPayes     = $queryPayes->orderBy('nom')->paginate(25)->withQueryString();
        $adherentsEnAttente = $queryAttente->orderBy('nom')->paginate(25)->withQueryString();

        return view('adherents.index', compact(
            'tab',
            'search',
            'filterSource',
            'filterStatut',
            'adherentsPayes',
            'adherentsEnAttente',
            'countPayes',
            'countAttente',
        ));
    }

    public function show(Adherent $adherent)
    {
        $adherent->load([
            'tuteur',
            'inscriptions',
            'inscription',
            'activitesActives',
            'paiements',
        ]);

        $idActivites = $adherent->activitesActives->pluck('id');

        $seances = \App\Models\Seance::with('activite')
            ->whereIn('id_activite', $idActivites)
            ->orderByDesc('date')
            ->get();

        $absencesMap = \App\Models\Presence::where('id_adherent', $adherent->id)
            ->whereIn('id_seance', $seances->pluck('id_seance'))
            ->get()
            ->keyBy('id_seance');

        $presences = $seances->map(function ($seance) use ($absencesMap) {
            $presence = $absencesMap->get($seance->id_seance);
            $seance->statut_presence = $presence?->statut ?? 'Présent';
            $seance->raison_presence = $presence?->raison;
            return $seance;
        });

        $totalSeances = $seances->count();

        $nbAbsences = $absencesMap->filter(
            fn($p) => strtolower($p->statut) === 'absent'
        )->count();

        $nbPresences  = max(0, $totalSeances - $nbAbsences);
        $tauxPresence = $totalSeances > 0 ? round(($nbPresences / $totalSeances) * 100) : 0;

        $paiementPrincipal = $adherent->paiements->sortByDesc('date_paiement')->first();
        $saisons           = $adherent->inscriptions->sortByDesc('saison');

        return view('adherents.show', compact(
            'adherent',
            'presences',
            'nbPresences',
            'nbAbsences',
            'tauxPresence',
            'paiementPrincipal',
            'saisons',
        ));
    }
}
