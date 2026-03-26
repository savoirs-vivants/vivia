<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\DossierActivite;
use App\Models\User;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $type   = $request->get('type');
        $ville  = $request->get('ville');

        $toutesActivites = Activite::withCount(['adherentsActifs as nb_inscrits'])
            ->with('dossier')
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

        $dossiers = DossierActivite::withCount(['activitesActives as nb_activites'])->orderBy('nom')->get();

        return view('activites.index', compact('activites', 'archives', 'search', 'type', 'ville', 'lieux', 'dossiers'));
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
        $users = User::orderBy('name')->get();
        $dossiers = DossierActivite::orderBy('nom')->get();
        return view('activites.create', compact('users', 'dossiers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'              => 'required|in:activite,stage',
            'nom'               => 'required|string|max:255',
            'tarif'             => 'nullable|numeric|min:0',
            'adresse'           => 'nullable|string|max:255',
            'ville'             => 'nullable|string|max:255',
            'jours.*'           => 'nullable|string',
            'debuts.*'          => 'nullable|date_format:H:i',
            'fins.*'            => 'nullable|date_format:H:i',
            'gestionnaires'     => 'nullable|array',
            'gestionnaires.*'   => 'exists:users,id',
            'dossier_action'    => 'nullable|in:none,existing,new',
            'id_dossier'        => 'nullable|exists:dossiers_activite,id',
            'nouveau_dossier'   => 'nullable|string|max:255',
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

        $idDossier = null;
        $dossierAction = $request->input('dossier_action', 'none');

        if ($dossierAction === 'existing') {
            $idDossier = $request->input('id_dossier');
        } elseif ($dossierAction === 'new' && !empty($request->input('nouveau_dossier'))) {
            $dossier = DossierActivite::create(['nom' => $request->input('nouveau_dossier')]);
            $idDossier = $dossier->id;
        }

        $activite = Activite::create([
            'type'       => $validated['type'],
            'nom'        => $validated['nom'],
            'tarif'      => $validated['tarif'],
            'adresse'    => $validated['adresse'],
            'ville'      => $validated['ville'],
            'horaires'   => empty($horaires) ? null : $horaires,
            'id_dossier' => $idDossier,
        ]);

        if (!empty($validated['gestionnaires'])) {
            $activite->gestionnaires()->sync($validated['gestionnaires']);
        }

        return redirect()->route('activites.index')
            ->with('success', 'L\'événement a été créé avec succès.');
    }

    public function show(Activite $activite)
    {
        $activite->load(['adherentsActifs', 'gestionnaires', 'adherents']);

        $seances = $activite->seances()
            ->with('presences')
            ->where('date', '<=', now())
            ->orderByDesc('date')
            ->get();

        $nbSeancesPassees = $seances->count();
        $adherentsActifs = $activite->adherentsActifs;

        $adherentsStats = $adherentsActifs->map(function ($adherent) use ($seances, $nbSeancesPassees) {
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

        $tousLesAdherents = $activite->adherents;
        $saisons = $tousLesAdherents->pluck('pivot.saison')->unique()->sortDesc()->values();

        $tauxReconduction = 0;
        $nbReconduits = 0;
        $saisonPrecedente = null;

        if ($saisons->count() >= 2) {
            $saisonCourante = $saisons[0];
            $saisonPrecedente = $saisons[1];

            $idsPrecedents = $tousLesAdherents->where('pivot.saison', $saisonPrecedente)->pluck('id')->unique();
            $idsCourants = $tousLesAdherents->where('pivot.saison', $saisonCourante)->pluck('id')->unique();

            $nbCourants = $idsCourants->count();
            $nbReconduits = $idsCourants->intersect($idsPrecedents)->count();
            $tauxReconduction = $nbCourants > 0 ? round(($nbReconduits / $nbCourants) * 100) : 0;
        }

        $totalInscritsHistorique = $tousLesAdherents->count();
        $nbAbandons = $tousLesAdherents->where('pivot.est_un_abandon', true)->count();
        $tauxAbandon = $totalInscritsHistorique > 0 ? round(($nbAbandons / $totalInscritsHistorique) * 100) : 0;

        $graphiqueSeances = $seances->take(8)->reverse()->map(function ($seance) use ($adherentsActifs) {
            $nbAbsents = $seance->presences->whereIn('id_adherent', $adherentsActifs->pluck('id'))->count();
            $nbPresents = $adherentsActifs->count() - $nbAbsents;

            return [
                'date' => $seance->date->isoFormat('D MMM'),
                'presents' => $nbPresents,
                'total' => $adherentsActifs->count(),
                'pourcentage' => $adherentsActifs->count() > 0 ? ($nbPresents / $adherentsActifs->count()) * 100 : 0
            ];
        });

        return view('activites.show', compact(
            'activite',
            'adherentsStats',
            'seances',
            'tauxMoyen',
            'nbSeancesPassees',
            'graphiqueSeances',
            'tauxAbandon',
            'nbAbandons',
            'tauxReconduction',
            'nbReconduits',
            'saisonPrecedente'
        ));
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
        $users = User::orderBy('name')->get();
        $dossiers = DossierActivite::orderBy('nom')->get();
        $activite->load('gestionnaires');

        return view('activites.edit', compact('activite', 'users', 'dossiers'));
    }

    public function update(Request $request, Activite $activite)
    {
        $validated = $request->validate([
            'type'              => 'required|in:activite,stage',
            'nom'               => 'required|string|max:255',
            'tarif'             => 'nullable|numeric|min:0',
            'adresse'           => 'nullable|string|max:255',
            'ville'             => 'nullable|string|max:255',
            'jours.*'           => 'nullable|string',
            'debuts.*'          => 'nullable|date_format:H:i',
            'fins.*'            => 'nullable|date_format:H:i',
            'gestionnaires'     => 'nullable|array',
            'gestionnaires.*'   => 'exists:users,id',
            'dossier_action'    => 'nullable|in:none,existing,new',
            'id_dossier'        => 'nullable|exists:dossiers_activite,id',
            'nouveau_dossier'   => 'nullable|string|max:255',
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

        $idDossier = null;
        $dossierAction = $request->input('dossier_action', 'none');

        if ($dossierAction === 'existing') {
            $idDossier = $request->input('id_dossier');
        } elseif ($dossierAction === 'new' && !empty($request->input('nouveau_dossier'))) {
            $dossier = DossierActivite::create(['nom' => $request->input('nouveau_dossier')]);
            $idDossier = $dossier->id;
        }

        $activite->update([
            'type'       => $validated['type'],
            'nom'        => $validated['nom'],
            'tarif'      => $validated['tarif'],
            'adresse'    => $validated['adresse'],
            'ville'      => $validated['ville'],
            'horaires'   => empty($horaires) ? null : $horaires,
            'id_dossier' => $idDossier,
        ]);

        $activite->gestionnaires()->sync($validated['gestionnaires'] ?? []);

        return redirect()->route('activites.show', $activite)
            ->with('success', 'L\'événement a été modifié avec succès.');
    }

    public function abandonner(Request $request, Activite $activite, \App\Models\Adherent $adherent)
    {
        $request->validate([
            'motif_sortie' => 'required|string|max:255',
        ]);

        $activite->adherents()->updateExistingPivot($adherent->id, [
            'date_sortie' => now()->toDateString(),
            'motif_sortie' => $request->motif_sortie,
            'est_un_abandon' => true,
        ]);

        return redirect()->back()->with('success', "L'adhérent {$adherent->prenom} a été marqué comme ayant abandonné.");
    }

    public function searchUsers(Request $request)
{
    $search = $request->get('q');

    $users = User::where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('firstname', 'like', "%{$search}%");
        })
        ->limit(10)
        ->get(['id', 'firstname', 'name']);

    return response()->json($users);
}
}
