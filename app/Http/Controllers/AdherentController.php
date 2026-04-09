<?php

namespace App\Http\Controllers;

use App\Mail\AdhesionValidee;
use App\Models\Adherent;
use App\Models\AdherentStructure;
use App\Models\Inscription;
use App\Models\Presence;
use App\Models\Saison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\CommentaireAdherentRequest;
use App\Http\Requests\AjouterVersementRequest;
use App\Http\Requests\UpdateFicheAdherentRequest;

class AdherentController extends Controller
{
    public function index(Request $request)
    {
        $user         = Auth::user();
        $tab          = $request->get('tab', 'payes');
        $search       = $request->get('q');
        $filterSource = $request->get('source');
        $filterStatut = $request->get('statut');
        $saison = Saison::current();

        $canVoirTousStatuts = in_array($user->role, ['admin', 'comptable']);
        if (!$canVoirTousStatuts) {
            $tab = 'payes';
        }

        $mesActivitesIds = null;
        if ($user->role === 'animateur') {
            $mesActivitesIds = DB::table('activites_gestionnaire')
                ->where('id_users', $user->id)
                ->pluck('id_activite');
        }

        $base = Adherent::with(['inscriptions', 'inscription', 'activitesActives', 'paiements'])
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%{$search}%")
                    ->orWhere('nom',   'like', "%{$search}%")
                    ->orWhere('mail',  'like', "%{$search}%");
            }))
            ->when($filterSource && $filterSource !== 'Tous', function ($q) use ($filterSource) {
                if ($filterSource === 'Interne') {
                    $q->where(function ($sub) {
                        $sub->whereHas('paiements', fn($p) => $p->where('source', 'Interne')->orWhereNull('source'))
                            ->orWhereDoesntHave('paiements');
                    });
                } else {
                    $q->whereHas('paiements', fn($p) => $p->where('source', $filterSource));
                }
            })
            ->when($mesActivitesIds, fn($q) => $q->whereHas('activites',
                fn($q2) => $q2->whereIn('activites_adherents.id_activite', $mesActivitesIds)
            ));

        $queryPayes = (clone $base)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PAYE)->where('saison', $saison));

        $queryAttente = (clone $base)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::EN_ATTENTE)->where('saison', $saison));

        $queryPartiel = (clone $base)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PARTIEL)->where('saison', $saison));

        $countPayes   = (clone $queryPayes)->count();
        $countAttente = $canVoirTousStatuts ? (clone $queryAttente)->count() : 0;
        $countPartiel = $canVoirTousStatuts ? (clone $queryPartiel)->count() : 0;

        $adherentsPayes     = $queryPayes->orderBy('nom')->paginate(25)->withQueryString();
        $adherentsEnAttente = $canVoirTousStatuts ? $queryAttente->orderBy('nom')->paginate(25)->withQueryString() : collect();
        $adherentsPartiel   = $canVoirTousStatuts ? $queryPartiel->orderBy('nom')->paginate(25)->withQueryString() : collect();

        $baseStructures = AdherentStructure::with(['inscription', 'paiements'])
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('mail', 'like', "%{$search}%")
                    ->orWhere('nom_correspondant', 'like', "%{$search}%");
            }));

        $structuresEnAttente = $canVoirTousStatuts
            ? (clone $baseStructures)->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::EN_ATTENTE)->where('saison', $saison))->orderBy('nom')->get()
            : collect();

        $structuresPayees = (clone $baseStructures)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PAYE)->where('saison', $saison))
            ->orderBy('nom')
            ->get();

        if ($canVoirTousStatuts) {
            $countAttente += $structuresEnAttente->count();
        }
        $countPayes += $structuresPayees->count();

        return view('adherents.index', compact(
            'tab',
            'search',
            'filterSource',
            'filterStatut',
            'adherentsPayes',
            'adherentsEnAttente',
            'adherentsPartiel',
            'countPayes',
            'countAttente',
            'countPartiel',
            'structuresEnAttente',
            'structuresPayees',
            'canVoirTousStatuts',
        ));
    }

    public function show(Adherent $adherent)
    {
        if (Auth::user()->role === 'animateur') {
            $mesActivitesIds = DB::table('activites_gestionnaire')
                ->where('id_users', Auth::id())
                ->pluck('id_activite');

            $adherentDansMesActivites = DB::table('activites_adherents')
                ->whereIn('id_activite', $mesActivitesIds)
                ->where('id_adherent', $adherent->id)
                ->exists();

            abort_if(!$adherentDansMesActivites, 403);
        }

        $adherent->load(['tousLesTuteurs', 'inscriptions', 'inscription', 'activitesActives', 'paiements']);

        $idActivites = $adherent->activitesActives->pluck('id');

        $seances = \App\Models\Seance::with('activite')
            ->join('activites_adherents', function($join) use ($adherent) {
                $join->on('seances.id_activite', '=', 'activites_adherents.id_activite')
                     ->where('activites_adherents.id_adherent', $adherent->id);
            })
            ->whereRaw('DATE(seances.date) >= activites_adherents.date_entree')
            ->where(function($q) {
                $q->whereNull('activites_adherents.date_sortie')
                  ->orWhereRaw('DATE(seances.date) <= activites_adherents.date_sortie');
            })
            ->select('seances.*')
            ->orderByDesc('seances.date')
            ->get();

        $seancesIds = $seances->pluck('id_seance');
        $absencesMap = Presence::where('id_adherent', $adherent->id)
            ->whereIn('id_seance', $seancesIds)
            ->get()
            ->keyBy('id_seance');

        $toutesLesSeances = $seances->map(function ($seance) use ($absencesMap) {
            $presence = $absencesMap->get($seance->id_seance);
            $seance->statut_presence = $presence?->statut ?? 'Présent';
            $seance->raison_presence = $presence?->raison;
            return $seance;
        });

        $presences = $toutesLesSeances->filter(function ($s) {
            return \Carbon\Carbon::parse($s->date)->isPast() || $s->statut === 'terminee';
        });

        $totalSeances = $presences->count();
        $nbAbsences   = $presences->filter(fn($p) => strtolower($p->statut_presence) === 'absent')->count();
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

    public function commentaire(CommentaireAdherentRequest $request, Adherent $adherent)
    {
        $adherent->update(['commentaire' => $request->commentaire]);

        return redirect()->route('adherents.show', $adherent)->with('success', 'Note enregistrée.');
    }

    public function valider(Request $request, Adherent $adherent)
{

    $saison = Saison::current();

    $statut = $request->boolean('plusieurs_versements') ? Inscription::PARTIEL : Inscription::PAYE;

    $inscriptionEnAttente = $adherent->inscriptions()
        ->where('saison', $saison)
        ->where('a_paye', Inscription::EN_ATTENTE)
        ->latest()
        ->first();

    if (!$inscriptionEnAttente) {
        return redirect()->route('adherents.index', ['tab' => 'attente'])
            ->with('error', 'Aucune inscription en attente trouvée.');
    }

    $inscriptionPayeeExistante = $adherent->inscriptions()
        ->where('saison', $saison)
        ->where('a_paye', Inscription::PAYE)
        ->first();

    $isReinscription = $inscriptionPayeeExistante !== null;
    $ancienStatut    = $isReinscription ? Inscription::PAYE : ($adherent->inscription?->a_paye);

    if ($isReinscription && $statut === Inscription::PAYE) {
        $inscriptionPayeeExistante->update([
            'montant' => $inscriptionPayeeExistante->montant + $inscriptionEnAttente->montant,
        ]);
        $inscriptionEnAttente->update(['a_paye' => $statut]);

    } elseif ($isReinscription && $statut === Inscription::PARTIEL) {
        $inscriptionEnAttente->update(['a_paye' => $statut]);

        if ($request->filled('montant_recu')) {
            $paiement = $adherent->paiements()->latest()->first();
            if ($paiement) {
                $paiement->update(['montant' => (float) $request->montant_recu]);
            }
        }

    } else {
        $inscriptionEnAttente->update(['a_paye' => $statut]);

        if ($statut === Inscription::PARTIEL && $request->filled('montant_recu')) {
            $paiement = $adherent->paiements()->latest()->first();
            if ($paiement) {
                $paiement->update(['montant' => (float) $request->montant_recu]);
            }
        }
    }

    if ($statut === Inscription::PAYE && $ancienStatut !== Inscription::PAYE) {
        $destinataire = null;

        if ($adherent->tranche_age === 'Enfant' || $adherent->tranche_age === 'Adolescent') {
            $premierTuteur = $adherent->tousLesTuteurs()->first();
            if ($premierTuteur && $premierTuteur->mail) {
                $destinataire = $premierTuteur->mail;
            }
        } else {
            if ($adherent->mail) {
                $destinataire = $adherent->mail;
            }
        }

        if ($destinataire) {
            Mail::to($destinataire)->send(new AdhesionValidee($adherent));
        }
    }

    $tab = match ($statut) {
        Inscription::PAYE    => 'payes',
        Inscription::PARTIEL => 'partiel',
        default              => 'attente',
    };

    return redirect()
        ->route('adherents.index', ['tab' => $tab])
        ->with('success', $adherent->prenom . ' ' . $adherent->nom . ($isReinscription ? ' — réinscription fusionnée et ' : ' — ') . strtolower($statut) . '.');
}

    public function showStructure(AdherentStructure $structure)
    {
        $structure->load(['inscriptions', 'inscription', 'paiements']);
        $saisons   = $structure->inscriptions->sortByDesc('saison');
        $totalPaye = (float) $structure->paiements->sum('montant');

        return view('adherents.show_structure', compact('structure', 'saisons', 'totalPaye'));
    }

    public function ajouterVersement(AjouterVersementRequest $request, Adherent $adherent)
    {

    $saison = Saison::current();

    $inscription = $adherent->inscriptions()
        ->where('saison', $saison)
        ->where('a_paye', Inscription::PARTIEL)
        ->latest()
        ->first();

    if (!$inscription) {
        return redirect()->route('adherents.index', ['tab' => 'partiel'])
            ->with('error', 'Cet adhérent n\'est pas en statut Partiel.');
    }

    $adherent->paiements()->create([
        'montant'       => (float) $request->montant_versement,
        'source'        => $request->source ?? 'Interne',
        'date_paiement' => $request->date_paiement ?? now()->toDateString(),
        'commentaire'   => $request->commentaire ?? null,
    ]);

    $totalVerse = (float) $adherent->paiements()->sum('montant');
    $totalDu    = (float) $inscription->montant;

    if ($totalVerse >= $totalDu) {
        $inscriptionPayeeExistante = $adherent->inscriptions()
            ->where('saison', $saison)
            ->where('a_paye', Inscription::PAYE)
            ->first();

        if ($inscriptionPayeeExistante) {
            $inscriptionPayeeExistante->update([
                'montant' => $inscriptionPayeeExistante->montant + $inscription->montant,
            ]);
            $inscription->delete();
        } else {
            $inscription->update(['a_paye' => Inscription::PAYE]);
        }

        $destinataire = null;
        if (in_array($adherent->tranche_age, ['Enfant', 'Adolescent'])) {
            $premierTuteur = $adherent->tousLesTuteurs()->first();
            if ($premierTuteur?->mail) {
                $destinataire = $premierTuteur->mail;
            }
        } elseif ($adherent->mail) {
            $destinataire = $adherent->mail;
        }
        if ($destinataire) {
            Mail::to($destinataire)->send(new AdhesionValidee($adherent));
        }

        return redirect()->route('adherents.index', ['tab' => 'payes'])
            ->with('success', $adherent->prenom . ' ' . $adherent->nom . ' — solde complet, passé en Payé.');
    }

    $reste = number_format($totalDu - $totalVerse, 2, ',', ' ');

    return redirect()->route('adherents.index', ['tab' => 'partiel'])
        ->with('success', 'Versement enregistré. Reste dû : ' . $reste . ' €');
}

    public function validerStructure(Request $request, AdherentStructure $structure)
    {
        $statut = $request->boolean('plusieurs_versements') ? Inscription::PARTIEL : Inscription::PAYE;

        $structure->inscription()->update([
            'a_paye'  => $statut,
            'montant' => $structure->montant_adhesion,
        ]);

        $tab = match ($statut) {
            Inscription::PAYE    => 'payes',
            Inscription::PARTIEL => 'partiel',
            default              => 'attente',
        };

        return redirect()
            ->route('adherents.index', ['tab' => $tab, 'type' => 'structure'])
            ->with('success', 'La structure ' . $structure->nom . ' est passée en statut : ' . strtolower($statut) . '.');
    }

    public function downloadPdf(Adherent $adherent)
    {
        $adherent->load(['tousLesTuteurs', 'activitesActives', 'inscription', 'paiements']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('adherents.pdf', compact('adherent'));

        $fileName = 'fiche_' . \Illuminate\Support\Str::slug($adherent->prenom . '_' . $adherent->nom) . '.pdf';

        return $pdf->download($fileName);
    }

    public function downloadPdfStructure(AdherentStructure $structure)
    {
        $structure->load(['inscription', 'paiements']);
        $totalPaye = (float) $structure->paiements->sum('montant');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('adherents.structure_pdf', compact('structure', 'totalPaye'));

        $fileName = 'fiche_structure_' . \Illuminate\Support\Str::slug($structure->nom) . '.pdf';

        return $pdf->download($fileName);
    }

    public function updateFiche(UpdateFicheAdherentRequest $request, Adherent $adherent)
    {
        $validated = $request->validated();

        $validated['communication'] = $request->boolean('communication');
        $validated['bulletin'] = $request->boolean('bulletin');
        $validated['manif'] = $request->boolean('manif');

        $adherent->update($validated);

        return back()->with('success', 'La fiche de l\'adhérent a été mise à jour.');
    }
}
