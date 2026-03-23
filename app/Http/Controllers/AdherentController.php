<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Inscription;
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
            ->when($filterSource && $filterSource !== 'Tous', fn($q) =>
                $q->whereHas('paiements', fn($q) => $q->where('source', $filterSource))
            );

        $queryPayes = (clone $base)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PAYE))
            ->when($filterStatut && $filterStatut !== 'Tous', fn($q) =>
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
}
