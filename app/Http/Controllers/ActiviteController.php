<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $type   = $request->get('type');
        $ville  = $request->get('ville');

        $activites = Activite::withCount([
                'adherents as nb_inscrits' => fn($q) => $q,
            ])
            ->when($search, fn($q) => $q->where('nom', 'like', "%{$search}%"))
            ->when($type,   fn($q) => $q->where('type', $type))
            ->when($ville,  fn($q) => $q->where('ville', 'like', "%{$ville}%")
                                        ->orWhere('adresse', 'like', "%{$ville}%"))
            ->orderBy('nom')
            ->get();

        $lieux = Activite::select('ville')->distinct()->whereNotNull('ville')->pluck('ville');

        return view('activites.index', compact('activites', 'search', 'type', 'ville', 'lieux'));
    }
}
