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

    public function create()
    {
        return view('activites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'     => 'required|in:activite,stage',
            'nom'      => 'required|string|max:255',
            'tarif'    => 'nullable|numeric|min:0',
            'adresse'  => 'nullable|string|max:255',
            'ville'    => 'nullable|string|max:255',
            'jours.*'  => 'nullable|string',
            'debuts.*' => 'nullable|date_format:H:i',
            'fins.*'   => 'nullable|date_format:H:i',
        ]);

        $horaires = [];
        $jours = $request->input('jours', []);
        $debuts = $request->input('debuts', []);
        $fins = $request->input('fins', []);

        foreach ($jours as $index => $jour) {
            if (!empty($jour) && !empty($debuts[$index]) && !empty($fins[$index])) {
                $plage = $debuts[$index] . '-' . $fins[$index];

                if (isset($horaires[$jour])) {
                    $horaires[$jour] .= ', ' . $plage;
                } else {
                    $horaires[$jour] = $plage;
                }
            }
        }

        Activite::create([
            'type'     => $validated['type'],
            'nom'      => $validated['nom'],
            'tarif'    => $validated['tarif'],
            'adresse'  => $validated['adresse'],
            'ville'    => $validated['ville'],
            'horaires' => empty($horaires) ? null : $horaires,
        ]);

        return redirect()->route('activites.index')
            ->with('success', 'L\'événement a été créé avec succès.');
    }
}
