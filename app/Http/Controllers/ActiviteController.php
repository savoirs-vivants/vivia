<?php

namespace App\Http\Controllers;

use App\Mail\CoursAnnule;
use App\Models\Activite;
use App\Models\DossierActivite;
use App\Models\Saison;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreActiviteRequest;
use App\Http\Requests\UpdateActiviteRequest;
use App\Http\Requests\AbandonnerAdherentRequest;

class ActiviteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');
        $type   = $request->query('type');
        $ville  = $request->query('ville');
        $user   = Auth::user();

        $mesActivitesIds = $user->role === 'animateur'
            ? DB::table('activites_gestionnaire')->where('id_users', $user->id)->pluck('id_activite')
            : null;

        $toutesActivites = Activite::withCount('adherentsActifs as nb_inscrits')
            ->with('dossier')
            ->when($mesActivitesIds, fn($q) => $q->whereIn('id', $mesActivitesIds))
            ->when($search, fn($q) => $q->where('nom', 'like', "%{$search}%"))
            ->when($type,   fn($q) => $q->where('type', $type))
            ->when($ville,  fn($q) => $q->where(function ($q2) use ($ville) {
                $q2->where('ville', 'like', "%{$ville}%")
                    ->orWhere('adresse', 'like', "%{$ville}%");
            }))
            ->orderBy('nom')
            ->get();

        $activites = $toutesActivites->where('is_archived', false);
        $archives  = $toutesActivites->where('is_archived', true);

        $lieux = Activite::select('ville')->distinct()->whereNotNull('ville')->pluck('ville');
        $dossiers = DossierActivite::withCount('activitesActives as nb_activites')->orderBy('nom')->get();

        $activitesParDossier   = $activites->groupBy(fn($a) => $a->id_dossier ?? 0);
        $activitesSansDossier  = $activitesParDossier->get(0) ?? collect();
        $dossiersAvecActivites = $dossiers->filter(fn($d) => $activitesParDossier->has($d->id));

        return view('activites.index', compact(
            'activites',
            'archives',
            'search',
            'type',
            'ville',
            'lieux',
            'dossiers',
            'activitesParDossier',
            'activitesSansDossier',
            'dossiersAvecActivites',
        ));
    }

    public function toggleArchive(Activite $activite)
    {
        $activite->update(['is_archived' => !$activite->is_archived]);
        return back()->with('success', $activite->is_archived ? "L'activité a été archivée." : "L'activité a été restaurée.");
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $dossiers = DossierActivite::orderBy('nom')->get();

        return view('activites.create', compact('users', 'dossiers'));
    }

    public function store(StoreActiviteRequest $request)
    {
        $data = $this->preparerDonneesActivite($request);
        $activite = Activite::create($data);

        if ($request->filled('gestionnaires')) {
            $activite->gestionnaires()->sync($request->validated('gestionnaires'));
        }

        $this->genererSeancesAuto($activite);

        return redirect()->route('activites.index')->with('success', 'L\'événement a été créé avec succès.');
    }

    public function edit(Activite $activite)
    {
        $selectedUsers = old('gestionnaires')
            ? User::whereIn('id', old('gestionnaires'))->get(['id', 'firstname', 'name'])
            : $activite->gestionnaires->map(fn($u) => [
                'id'        => $u->id,
                'firstname' => $u->firstname,
                'name'      => $u->name,
            ]);

        return view('activites.edit', [
            'activite'      => $activite,
            'selectedUsers' => $selectedUsers,
            'dossiers'      => DossierActivite::orderBy('nom')->get(),
        ]);
    }

    public function update(UpdateActiviteRequest $request, Activite $activite)
    {
        $data = $this->preparerDonneesActivite($request);

        $anciensHoraires = is_array($activite->horaires) ? json_encode($activite->horaires) : $activite->horaires;
        $nouveauxHoraires = json_encode($data['horaires']);

        $horairesOntChange = ($anciensHoraires !== $nouveauxHoraires) || ($data['type'] !== $activite->type);

        $activite->update($data);
        $activite->gestionnaires()->sync($request->validated('gestionnaires', []));

        if ($horairesOntChange) {
            $this->regenererFuturesSeances($activite);
        }

        return redirect()->route('activites.show', $activite)->with('success', 'L\'événement a été modifié avec succès.');
    }

    public function show(Activite $activite)
    {
        if (Auth::user()->role === 'animateur') {
            abort_if(!$activite->gestionnaires()->where('users.id', Auth::id())->exists(), 403);
        }

        $saison = Saison::current();
        $saisonModel = Saison::where('nom', $saison)->first();

        $dateDebutSaison = $saisonModel ? $saisonModel->date_debut : Carbon::create((int)explode('-', $saison)[0], 9, 1);
        $dateFinSaison   = $saisonModel ? $saisonModel->date_fin   : Carbon::create((int)explode('-', $saison)[1], 6, 30);

        $activite->load(['gestionnaires', 'adherentsActifs']);
        $adherentsActifs = $activite->adherentsActifs()->wherePivot('saison', $saison)->get();

        $seances = $activite->seances()
            ->with('presences.adherent')
            ->whereBetween('date', [$dateDebutSaison, $dateFinSaison])
            ->where(fn($q) => $q->where('date', '<=', now())->orWhere('statut', 'terminee'))
            ->orderByDesc('date')
            ->get();

        $seancesAVenir = $activite->seances()
            ->where('date', '>', now())
            ->where(fn($q) => $q->where('statut', '!=', 'terminee')->orWhereNull('statut'))
            ->orderBy('date')
            ->get();

        $historiqueInscriptions = DB::table('activites_adherents')->where('id_activite', $activite->id)->get();

        $seancesEligibles = $seances->map(function ($seance) use ($historiqueInscriptions) {
            $dateSeance = Carbon::parse($seance->date)->format('Y-m-d');
            $seance->eligible_adherents = $historiqueInscriptions
                ->filter(fn($pivot) => $pivot->date_entree <= $dateSeance && (is_null($pivot->date_sortie) || $pivot->date_sortie > $dateSeance))
                ->pluck('id_adherent');

            $seance->eligible_count = $seance->eligible_adherents->count();
            return $seance;
        });

        $nbSeancesPassees = $seances->count();
        $totalEligibleAcrossSeances = $seancesEligibles->sum('eligible_count');
        $totalPresencesEligible = $seancesEligibles->sum(fn($s) => $s->eligible_count - $s->presences->whereIn('id_adherent', $s->eligible_adherents)->count());
        $tauxMoyen = $totalEligibleAcrossSeances > 0 ? round(($totalPresencesEligible / $totalEligibleAcrossSeances) * 100) : 0;

        /* Au lieu de charger tous les modèles Adhérents en mémoire pour calculer la fidélisation,
         * on demande uniquement les identifiants et les saisons à MySQL. C'est infiniment plus rapide
         * et cela protège le serveur de la saturation mémoire au fil des années.
         */
        $saisonsHistoriques = DB::table('activites_adherents')
            ->where('id_activite', $activite->id)
            ->pluck('saison')
            ->unique()
            ->sortDesc()
            ->values();

        $tauxReconduction = 0;
        $nbReconduits     = 0;
        $saisonPrecedente = $saisonsHistoriques->count() >= 2 ? $saisonsHistoriques[1] : null;

        if ($saisonPrecedente) {
            $idsPrecedents = DB::table('activites_adherents')->where('id_activite', $activite->id)->where('saison', $saisonPrecedente)->pluck('id_adherent');
            $idsCourants   = DB::table('activites_adherents')->where('id_activite', $activite->id)->where('saison', $saison)->pluck('id_adherent');
            $nbCourants    = $idsCourants->count();

            $nbReconduits  = $idsCourants->intersect($idsPrecedents)->count();
            $tauxReconduction = $nbCourants > 0 ? round(($nbReconduits / $nbCourants) * 100) : 0;
        }

        $statistiquesAbandon = DB::table('activites_adherents')
            ->selectRaw('count(*) as total, sum(est_un_abandon) as abandons')
            ->where('id_activite', $activite->id)
            ->where('saison', $saison)
            ->first();

        $nbAbandons    = $statistiquesAbandon->abandons ?? 0;
        $totalDeSaison = $statistiquesAbandon->total ?? 0;
        $tauxAbandon   = $totalDeSaison > 0 ? round(($nbAbandons / $totalDeSaison) * 100) : 0;

        $graphiqueSeances = $seancesEligibles->take(8)->reverse()->map(function ($seance) {
            $nbAbsents  = $seance->presences->whereIn('id_adherent', $seance->eligible_adherents)->count();
            $nbPresents = $seance->eligible_count - $nbAbsents;
            return [
                'date'        => Carbon::parse($seance->date)->isoFormat('D MMM'),
                'presents'    => $nbPresents,
                'total'       => $seance->eligible_count,
                'pourcentage' => $seance->eligible_count > 0 ? ($nbPresents / $seance->eligible_count) * 100 : 0,
            ];
        });

        $adherentsStats = $adherentsActifs->map(function ($adherent) use ($seancesEligibles) {
            $seancesEligibleAdherent = $seancesEligibles->filter(fn($s) => $s->eligible_adherents->contains($adherent->id));
            $nbSeances = $seancesEligibleAdherent->count();

            if ($nbSeances === 0) {
                $adherent->taux_presence = 0;
                return $adherent;
            }

            $nbAbsences = $seancesEligibleAdherent->filter(fn($s) => $s->presences->where('id_adherent', $adherent->id)->isNotEmpty())->count();
            $adherent->taux_presence = round((($nbSeances - $nbAbsences) / $nbSeances) * 100);

            return $adherent;
        });

        return view('activites.show', compact(
            'activite',
            'adherentsActifs',
            'adherentsStats',
            'seancesEligibles',
            'seancesAVenir',
            'tauxMoyen',
            'nbSeancesPassees',
            'graphiqueSeances',
            'tauxAbandon',
            'nbAbandons',
            'tauxReconduction',
            'nbReconduits',
            'saisonPrecedente'
        ))->with('seances', $seancesEligibles);
    }

    public function storePresences(Request $request, Activite $activite, $seance)
    {
        $presences = $request->input('presences', []);
        $adherents = $activite->adherentsActifs->pluck('id');

        /* Architecture de "Présomption de présence". Pour économiser de l'espace BDD,
         * l'état par défaut (pas de ligne en base) = Présent. On insère une ligne uniquement
         * pour tracer les absences. Si on marque qqn présent, on supprime l'enregistrement d'absence.
         */
        foreach ($adherents as $idAdherent) {
            $data = $presences[$idAdherent] ?? [];

            if (isset($data['statut']) && $data['statut'] === 'present') {
                \App\Models\Presence::where('id_seance', $seance)->where('id_adherent', $idAdherent)->delete();
            } else {
                \App\Models\Presence::updateOrCreate(
                    ['id_seance' => $seance, 'id_adherent' => $idAdherent],
                    ['statut' => 'Absent', 'raison' => $data['raison'] ?? null]
                );
            }
        }

        return back()->with('success', 'Les présences ont bien été enregistrées.');
    }

    public function abandonner(AbandonnerAdherentRequest $request, Activite $activite, \App\Models\Adherent $adherent)
    {
        $activite->adherents()->updateExistingPivot($adherent->id, [
            'date_sortie' => now()->toDateString(),
            'motif_sortie' => $request->motif_sortie,
            'est_un_abandon' => true,
        ]);

        return back()->with('success', "L'adhérent {$adherent->prenom} a été marqué comme ayant abandonné.");
    }

    public function searchUsers(Request $request)
    {
        $search = $request->query('q');
        return response()->json(
            User::where('name', 'like', "%{$search}%")
                ->orWhere('firstname', 'like', "%{$search}%")
                ->limit(10)
                ->get(['id', 'firstname', 'name'])
        );
    }

    public function annulerSeance(Activite $activite, $idSeance)
    {
        $seance = DB::table('seances')->where('id_activite', $activite->id)->where('id_seance', $idSeance)->first();

        if ($seance) {
            $dateFormatee = Carbon::parse($seance->date)->isoFormat('dddd D MMMM YYYY à HH:mm');

            // Extraction propre des emails via les relations Laravel
            $emails = $activite->adherentsActifs()->with('tousLesTuteurs')->get()
                ->map(fn($a) => $a->tousLesTuteurs->first()?->mail)
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($emails)) {
                Mail::to('contact@savoirsvivants.fr')->bcc($emails)->send(new CoursAnnule($activite->nom, $dateFormatee));
            }

            DB::table('seances')->where('id_seance', $idSeance)->delete();
        }

        return back()->with('success', 'La séance a été annulée, et les tuteurs ont été prévenus par email.');
    }

    /* ==============================================================================
     * MÉTHODES PRIVÉES
     * ============================================================================== */

    private function preparerDonneesActivite(Request $request): array
    {
        $validated = $request->validated();
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
                    $horaires[$jour] = isset($horaires[$jour]) ? $horaires[$jour] . ', ' . $plage : $plage;
                }
            }
        }

        $idDossier = null;
        if ($request->input('dossier_action') === 'existing') {
            $idDossier = $request->input('id_dossier');
        } elseif ($request->input('dossier_action') === 'new' && $request->filled('nouveau_dossier')) {
            $idDossier = DossierActivite::create(['nom' => $request->input('nouveau_dossier')])->id;
        }

        return [
            'type'       => $validated['type'],
            'nom'        => $validated['nom'],
            'tarif'      => $validated['tarif'],
            'max_eleves' => $validated['max_eleves'] ?? null,
            'adresse'    => $validated['adresse'],
            'ville'      => $validated['ville'],
            'horaires'   => empty($horaires) ? null : $horaires,
            'classes'    => $request->input('classes') ?: null,
            'id_dossier' => $idDossier,
        ];
    }

    private function regenererFuturesSeances(Activite $activite)
    {
        $futuresSeances = DB::table('seances')
            ->where('id_activite', $activite->id)
            ->where('date', '>=', now()->startOfDay())
            ->pluck('id_seance');

        if ($futuresSeances->isNotEmpty()) {
            $seancesAvecAppel = DB::table('presence')
                ->whereIn('id_seance', $futuresSeances)
                ->pluck('id_seance')
                ->unique();

            $seancesASupprimer = $futuresSeances->diff($seancesAvecAppel);

            DB::table('seances')->whereIn('id_seance', $seancesASupprimer)->delete();
        }

        $this->genererSeancesAuto($activite, true);
    }

    private function genererSeancesAuto(Activite $activite, $depuisAujourdhui = false)
    {
        $horaires = is_string($activite->horaires) ? json_decode($activite->horaires, true) : $activite->horaires;
        if (empty($horaires) || !is_array($horaires)) return;

        $nouvellesSeances = [];

        if ($activite->type === 'stage' && isset($horaires['stage'])) {
            $debutStage = $horaires['stage']['date_debut'] ?? null;
            $finStage   = $horaires['stage']['date_fin'] ?? null;
            $heureDebut = $horaires['stage']['heure_debut'] ?? '00:00';

            if (!$debutStage || !$finStage) return;

            $dateCourante = Carbon::parse($debutStage);
            $dateFin      = Carbon::parse($finStage);

            if ($depuisAujourdhui && $dateCourante->isPast()) {
                $dateCourante = now()->startOfDay();
            }

            while ($dateCourante->lte($dateFin)) {
                $nouvellesSeances[] = [
                    'id_activite' => $activite->id,
                    'date'        => $dateCourante->format('Y-m-d') . ' ' . $heureDebut . ':00',
                ];
                $dateCourante->addDay();
            }
        } else {
            $joursMap = [
                'Lundi' => Carbon::MONDAY, 'Mardi' => Carbon::TUESDAY, 'Mercredi' => Carbon::WEDNESDAY,
                'Jeudi' => Carbon::THURSDAY, 'Vendredi' => Carbon::FRIDAY, 'Samedi' => Carbon::SATURDAY, 'Dimanche' => Carbon::SUNDAY,
            ];

            $saisonActive = Saison::where('nom', Saison::current())->first();
            $finGeneration = $saisonActive
                ? Carbon::parse($saisonActive->date_fin)
                : Carbon::create(now()->month >= 6 ? now()->year + 1 : now()->year, 7, 31);

            $baseDate = $depuisAujourdhui ? now()->startOfDay() : ($saisonActive ? Carbon::parse($saisonActive->date_debut)->startOfDay() : now()->startOfDay());

            foreach ($horaires as $jour => $plagesStr) {
                if (!isset($joursMap[$jour])) continue;

                $plages = explode(',', $plagesStr);
                $dateCourante = clone $baseDate;

                if ($dateCourante->dayOfWeekIso !== $joursMap[$jour]) {
                    $dateCourante->next($joursMap[$jour]);
                }

                while ($dateCourante->lte($finGeneration)) {
                    foreach ($plages as $plage) {
                        $heureDebut = trim(explode('-', $plage)[0]);
                        $nouvellesSeances[] = [
                            'id_activite' => $activite->id,
                            'date'        => $dateCourante->format('Y-m-d') . ' ' . $heureDebut . ':00',
                        ];
                    }
                    $dateCourante->addWeek();
                }
            }
        }

        /* Bulk Insert. Au lieu de faire 40 requêtes `insert()` dans une boucle (très lent),
         * on insère tout le tableau en une seule requête SQL. `insertOrIgnore` permet de
         * ne pas écraser ni doubler les séances qui existent déjà si l'activité est mise à jour.
         */
        if (!empty($nouvellesSeances)) {
            $datesExistantes = DB::table('seances')->where('id_activite', $activite->id)->pluck('date')->toArray();

            $aInserer = array_filter($nouvellesSeances, function ($seance) use ($datesExistantes) {
                return !in_array($seance['date'], $datesExistantes);
            });

            if (!empty($aInserer)) {
                DB::table('seances')->insert($aInserer);
            }
        }
    }
}
