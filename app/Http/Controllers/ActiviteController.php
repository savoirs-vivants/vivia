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

        $toutesActivites = Activite::withCount(['adherentsActifs as nb_inscrits'])
            ->when($search, fn($q) => $q->where('nom', 'like', "%{$search}%"))
            ->when($type,   fn($q) => $q->where('type', $type))
            ->when($ville,  fn($q) => $q->where(function ($q2) use ($ville) {
                $q2->where('ville', 'like', "%{$ville}%")
                   ->orWhere('adresse', 'like', "%{$ville}%");
            }))
            ->orderBy('nom')
            ->get();

        $activites = $toutesActivites->where('is_archived', false);
        $archives = $toutesActivites->where('is_archived', true);

        $lieux = Activite::select('ville')->distinct()->whereNotNull('ville')->pluck('ville');

        return view('activites.index', compact('activites', 'archives', 'search', 'type', 'ville', 'lieux'));
    }

    public function toggleArchive(Activite $activite)
    {
        $activite->update([
            'is_archived' => !$activite->is_archived
        ]);

        $message = $activite->is_archived ? "L'activité a été archivée." : "L'activité a été restaurée.";

        return redirect()->back()->with('success', $message);
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

    public function show(Activite $activite)
    {
        $activite->load(['adherentsActifs', 'gestionnaires']);

        $seances = $activite->seances()
            ->with('presences')
            ->where('date', '<=', now())
            ->orderByDesc('date')
            ->get();

        $nbSeancesPassees = $seances->count();
        $adherents = $activite->adherentsActifs;

        $adherentsStats = $adherents->map(function ($adherent) use ($seances, $nbSeancesPassees) {
            if ($nbSeancesPassees === 0) {
                $adherent->taux_presence = 0;
                return $adherent;
            }

            $nbAbsences = 0;
            foreach ($seances as $seance) {
                if ($seance->presences->where('id_adherent', $adherent->id)->isNotEmpty()) {
                    $nbAbsences++;
                }
            }

            $nbPresences = $nbSeancesPassees - $nbAbsences;
            $adherent->taux_presence = round(($nbPresences / $nbSeancesPassees) * 100);
            return $adherent;
        });

        $tauxMoyen = $adherentsStats->count() > 0 ? round($adherentsStats->avg('taux_presence')) : 0;
        $actifs = $adherentsStats->where('taux_presence', '>=', 75)->count();

        $graphiqueSeances = $seances->take(8)->reverse()->map(function($seance) use ($adherents) {
            $nbAbsents = $seance->presences->whereIn('id_adherent', $adherents->pluck('id'))->count();
            $nbPresents = $adherents->count() - $nbAbsents;

            return [
                'date' => $seance->date->isoFormat('D MMM'),
                'presents' => $nbPresents,
                'total' => $adherents->count(),
                'pourcentage' => $adherents->count() > 0 ? ($nbPresents / $adherents->count()) * 100 : 0
            ];
        });

        return view('activites.show', compact('activite', 'adherentsStats', 'seances', 'tauxMoyen', 'actifs', 'nbSeancesPassees', 'graphiqueSeances'));
    }

    public function storePresences(Request $request, Activite $activite, $seance)
    {
        $presences = $request->input('presences', []);
        $adherents = $activite->adherentsActifs->pluck('id');

        foreach ($adherents as $idAdherent) {
            $data = $presences[$idAdherent] ?? [];
            $estPresent = isset($data['statut']) && $data['statut'] === 'present';

            if ($estPresent) {
                \App\Models\Presence::where('id_seance', $seance)
                    ->where('id_adherent', $idAdherent)
                    ->delete();
            } else {
                \App\Models\Presence::updateOrCreate(
                    [
                        'id_seance'   => $seance,
                        'id_adherent' => $idAdherent,
                    ],
                    [
                        'statut' => 'Absent',
                        'raison' => $data['raison'] ?? null,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Les présences ont bien été enregistrées.');
    }

    public function edit(Activite $activite)
    {
        return view('activites.edit', compact('activite'));
    }

    public function update(Request $request, Activite $activite)
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

        $activite->update([
            'type'     => $validated['type'],
            'nom'      => $validated['nom'],
            'tarif'    => $validated['tarif'],
            'adresse'  => $validated['adresse'],
            'ville'    => $validated['ville'],
            'horaires' => empty($horaires) ? null : $horaires,
        ]);

        return redirect()->route('activites.show', $activite)
            ->with('success', 'L\'événement a été modifié avec succès.');
    }
}
