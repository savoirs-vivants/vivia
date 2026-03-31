<?php

namespace App\Http\Controllers;

use App\Mail\CoursAnnule;
use App\Models\Activite;
use App\Models\DossierActivite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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
            'classes'           => 'nullable|array',
            'classes.*'         => 'nullable|string',
            'dossier_action'    => 'nullable|in:none,existing,new',
            'id_dossier'        => 'nullable|exists:dossiers_activite,id',
            'nouveau_dossier'   => 'nullable|string|max:255',
        ]);

        $horaires = [];

        if ($validated['type'] === 'stage') {
            $horaires['stage'] = [
                'date_debut'  => $request->input('date_debut_stage'),
                'date_fin'    => $request->input('date_fin_stage'),
                'heure_debut' => $request->input('heure_debut_stage'),
                'heure_fin'   => $request->input('heure_fin_stage'),
            ];
        } else {
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
            'classes'    => $request->input('classes') ?: null,
            'id_dossier' => $idDossier,
        ]);

        if (!empty($validated['gestionnaires'])) {
            $activite->gestionnaires()->sync($validated['gestionnaires']);
        }
        $this->genererSeancesAuto($activite);

        return redirect()->route('activites.index')
            ->with('success', 'L\'événement a été créé avec succès.');
    }

    public function show(Activite $activite)
    {
        $activite->load(['adherentsActifs', 'gestionnaires', 'adherents']);

        $seances = $activite->seances()
            ->with('presences')
            ->where(function($q) {
                $q->where('date', '<=', now())
                  ->orWhere('statut', 'terminee');
            })
            ->orderByDesc('date')
            ->get();

        $seancesAVenir = $activite->seances()
            ->where('date', '>', now())
            ->where(function($q) {
                $q->where('statut', '!=', 'terminee')
                  ->orWhereNull('statut');
            })
            ->orderBy('date')
            ->get();

        $seancesIds = $seances->pluck('id_seance');
        $toutesPresences = \App\Models\Presence::whereIn('id_seance', $seancesIds)->get()->groupBy('id_seance');

        $nbSeancesPassees = $seances->count();
        $adherentsActifs = $activite->adherentsActifs;

        $adherentsStats = $adherentsActifs->map(function ($adherent) use ($seances, $toutesPresences, $nbSeancesPassees) {
            if ($nbSeancesPassees === 0) {
                $adherent->taux_presence = 0;
                return $adherent;
            }

            $nbAbsences = 0;
            foreach ($seances as $seance) {
                $presencesSeance = $toutesPresences->get($seance->id_seance, collect());
                if ($presencesSeance->where('id_adherent', $adherent->id)->isNotEmpty()) {
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
                'date' => Carbon::parse($seance->date)->isoFormat('D MMM'),
                'presents' => $nbPresents,
                'total' => $adherentsActifs->count(),
                'pourcentage' => $adherentsActifs->count() > 0 ? ($nbPresents / $adherentsActifs->count()) * 100 : 0
            ];
        });

        return view('activites.show', compact(
            'activite',
            'adherentsStats',
            'seances',
            'seancesAVenir',
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
            'classes'           => 'nullable|array',
            'classes.*'         => 'nullable|string',
            'dossier_action'    => 'nullable|in:none,existing,new',
            'id_dossier'        => 'nullable|exists:dossiers_activite,id',
            'nouveau_dossier'   => 'nullable|string|max:255',
        ]);

        $horaires = [];

        if ($validated['type'] === 'stage') {
            $horaires['stage'] = [
                'date_debut'  => $request->input('date_debut_stage'),
                'date_fin'    => $request->input('date_fin_stage'),
                'heure_debut' => $request->input('heure_debut_stage'),
                'heure_fin'   => $request->input('heure_fin_stage'),
            ];
        } else {
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
            'classes'    => $request->input('classes') ?: null,
            'id_dossier' => $idDossier,
        ]);

        $activite->gestionnaires()->sync($validated['gestionnaires'] ?? []);
        $this->genererSeancesAuto($activite);

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

        $users = User::where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('firstname', 'like', "%{$search}%");
        })
            ->limit(10)
            ->get(['id', 'firstname', 'name']);

        return response()->json($users);
    }

    private function genererSeancesAuto(Activite $activite)
    {
        $horaires = is_string($activite->horaires) ? json_decode($activite->horaires, true) : $activite->horaires;
        if (empty($horaires) || !is_array($horaires)) return;

        if ($activite->type === 'stage' && isset($horaires['stage'])) {
            $debutStage = $horaires['stage']['date_debut'] ?? null;
            $finStage   = $horaires['stage']['date_fin'] ?? null;
            $heureDebut = $horaires['stage']['heure_debut'] ?? '00:00';

            if (!$debutStage || !$finStage) return;

            $dateDebut = \Carbon\Carbon::parse($debutStage);
            $dateFin   = \Carbon\Carbon::parse($finStage);

            for ($dateCourante = clone $dateDebut; $dateCourante->lte($dateFin); $dateCourante->addDay()) {
                $dateAvecHeure = $dateCourante->format('Y-m-d') . ' ' . $heureDebut . ':00';

                $existe = DB::table('seances')
                    ->where('id_activite', $activite->id)
                    ->where('date', $dateAvecHeure)
                    ->exists();

                if (!$existe) {
                    DB::table('seances')->insert([
                        'id_activite' => $activite->id,
                        'date'        => $dateAvecHeure,
                    ]);
                }
            }

            return;
        }

        $joursMap = [
            'Lundi' => \Carbon\Carbon::MONDAY, 'Mardi' => \Carbon\Carbon::TUESDAY, 'Mercredi' => \Carbon\Carbon::WEDNESDAY,
            'Jeudi' => \Carbon\Carbon::THURSDAY, 'Vendredi' => \Carbon\Carbon::FRIDAY, 'Samedi' => \Carbon\Carbon::SATURDAY, 'Dimanche' => \Carbon\Carbon::SUNDAY,
        ];

        $finAnneeScolaire = now()->month >= 9 ? \Carbon\Carbon::create(now()->year + 1, 6, 30) : \Carbon\Carbon::create(now()->year, 6, 30);

        foreach ($horaires as $jour => $plagesStr) {
            if (!isset($joursMap[$jour])) continue;

            $plages = explode(',', $plagesStr);
            $dateCourante = now()->next($joursMap[$jour]);

            while ($dateCourante->lte($finAnneeScolaire)) {
                foreach ($plages as $plage) {
                    $parts = explode('-', trim($plage));
                    $heureDebut = trim($parts[0]);
                    $dateAvecHeure = $dateCourante->format('Y-m-d') . ' ' . $heureDebut . ':00';

                    $existe = DB::table('seances')
                        ->where('id_activite', $activite->id)
                        ->where('date', $dateAvecHeure)
                        ->exists();

                    if (!$existe) {
                        DB::table('seances')->insert([
                            'id_activite' => $activite->id,
                            'date'        => $dateAvecHeure,
                        ]);
                    }
                }
                $dateCourante->addWeek();
            }
        }
    }

    public function annulerSeance(Activite $activite, $idSeance)
    {
        $seance = DB::table('seances')
            ->where('id_activite', $activite->id)
            ->where('id_seance', $idSeance)
            ->first();

        if ($seance) {
            $dateFormatee = \Carbon\Carbon::parse($seance->date)->isoFormat('dddd D MMMM YYYY à HH:mm');

            $adherents = $activite->adherentsActifs()->with('tousLesTuteurs')->get();
            $emails = [];

            foreach ($adherents as $adherent) {
                $tuteur = $adherent->tousLesTuteurs->first();
                if ($tuteur && $tuteur->mail) {
                    $emails[] = $tuteur->mail;
                }
            }

            $emails = array_unique($emails);

            if (!empty($emails)) {
                Mail::to('contact@savoirsvivants.fr')->bcc($emails)->send(new CoursAnnule($activite->nom, $dateFormatee));
            }
        }

        DB::table('seances')
            ->where('id_activite', $activite->id)
            ->where('id_seance', $idSeance)
            ->delete();

        return redirect()->back()->with('success', 'La séance a été annulée, et les tuteurs ont été prévenus par email.');
    }
}
