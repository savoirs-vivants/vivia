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
use App\Services\ActiviteService;

class ActiviteController extends Controller
{
    public function __construct(protected ActiviteService $activiteService)
    {
    }

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
            ->where('type', '!=', 'recherche')
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
        abort_if($activite->type === 'recherche', 404);

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
        $data = $this->activiteService->preparerDonneesActivite($request);
        $activite = Activite::create($data);

        if ($request->filled('gestionnaires')) {
            $activite->gestionnaires()->sync($request->validated('gestionnaires'));
        }

        $this->activiteService->genererSeancesAuto($activite);

        return redirect()->route('activites.index')->with('success', 'L\'événement a été créé avec succès.');
    }

    public function edit(Activite $activite)
    {
        abort_if($activite->type === 'recherche', 404);

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
        abort_if($activite->type === 'recherche', 404);

        $data = $this->activiteService->preparerDonneesActivite($request);

        $anciensHoraires = is_array($activite->horaires) ? json_encode($activite->horaires) : $activite->horaires;
        $nouveauxHoraires = json_encode($data['horaires']);

        $horairesOntChange = ($anciensHoraires !== $nouveauxHoraires) || ($data['type'] !== $activite->type);

        $activite->update($data);
        $activite->gestionnaires()->sync($request->validated('gestionnaires', []));

        if ($horairesOntChange) {
            $this->activiteService->regenererFuturesSeances($activite);
        }

        return redirect()->route('activites.show', $activite)->with('success', 'L\'événement a été modifié avec succès.');
    }

    public function show(Activite $activite)
    {
        abort_if($activite->type === 'recherche', 404);

        if (empty($activite->horaires)) {
            if (Auth::user()->role === 'animateur') {
                abort_if(!$activite->gestionnaires()->where('users.id', Auth::id())->exists(), 403);
            }
            $activite->load(['gestionnaires']);
            $saison = Saison::current();
            $adherents = $activite->adherentsActifs()->wherePivot('saison', $saison)->get();
            return view('activites.show_sans_horaires', compact('activite', 'adherents', 'saison'));
        }

        Saison::syncActive();

        if (Auth::user()->role === 'animateur') {
            abort_if(!$activite->gestionnaires()->where('users.id', Auth::id())->exists(), 403);
        }

        $saison = Saison::current();
        $saisonModel = Saison::where('nom', $saison)->first();

        $derniereSeance = DB::table('seances')->where('id_activite', $activite->id)->max('date');
        $finSaison = $saisonModel ? $saisonModel->date_fin : Carbon::create(now()->month >= 7 ? now()->year + 1 : now()->year, 6, 30)->toDateString();

        if (!$derniereSeance || $derniereSeance < $finSaison) {
            $this->activiteService->genererSeancesAuto($activite);
        }

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

}
