<?php

namespace App\Http\Controllers;

use App\Mail\AdhesionValidee;
use App\Models\Adherent;
use App\Models\AdherentStructure;
use App\Models\Inscription;
use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdherentController extends Controller
{
    public function index(Request $request)
    {
        $user         = Auth::user();
        $tab          = $request->get('tab', 'payes');
        $search       = $request->get('q');
        $filterSource = $request->get('source');
        $filterStatut = $request->get('statut');

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

        $base = Adherent::with(['inscription', 'activitesActives', 'paiements'])
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
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PAYE));

        $queryAttente = (clone $base)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::EN_ATTENTE));

        $queryPartiel = (clone $base)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PARTIEL));

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
            ? (clone $baseStructures)->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::EN_ATTENTE))->orderBy('nom')->get()
            : collect();

        $structuresPayees = (clone $baseStructures)
            ->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PAYE))
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
            ->whereIn('id_activite', $idActivites)
            ->orderByDesc('date')
            ->get();

        $absencesMap = Presence::where('id_adherent', $adherent->id)
            ->whereIn('id_seance', $seances->pluck('id_seance'))
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

    public function commentaire(Request $request, Adherent $adherent)
    {
        $request->validate(['commentaire' => ['required', 'string', 'max:2000']]);
        $adherent->update(['commentaire' => $request->commentaire]);

        return redirect()->route('adherents.show', $adherent)->with('success', 'Note enregistrée.');
    }

    public function valider(Request $request, Adherent $adherent)
    {
        if ($request->filled('commentaire')) {
            $adherent->update(['commentaire' => $request->commentaire]);
        }

        $statut = $request->boolean('plusieurs_versements') ? Inscription::PARTIEL : Inscription::PAYE;

        $ancienStatut = $adherent->inscription->a_paye ?? null;

        $totalAttendu = $adherent->load('activitesActives')->activitesActives->sum('tarif') + 10;

        $adherent->inscription()->update([
            'a_paye'  => $statut,
            'montant' => $totalAttendu,
        ]);

        if ($statut === Inscription::PARTIEL && $request->filled('montant_recu')) {
            $paiement = $adherent->paiements()->latest()->first();
            if ($paiement) {
                $paiement->update(['montant' => (float) $request->montant_recu]);
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
            ->with('success', $adherent->prenom . ' ' . $adherent->nom . ' — ' . strtolower($statut) . '.');
    }

    public function showStructure(AdherentStructure $structure)
    {
        $structure->load(['inscriptions', 'inscription', 'paiements']);
        $saisons   = $structure->inscriptions->sortByDesc('saison');
        $totalPaye = (float) $structure->paiements->sum('montant');

        return view('adherents.show_structure', compact('structure', 'saisons', 'totalPaye'));
    }

    public function ajouterVersement(Request $request, Adherent $adherent)
    {
        $request->validate([
            'montant_versement' => ['required', 'numeric', 'min:0.01'],
            'source'            => ['nullable', 'string', 'max:100'],
            'date_paiement'     => ['nullable', 'date'],
        ]);

        $inscription = $adherent->inscription;

        if (!$inscription || $inscription->a_paye !== Inscription::PARTIEL) {
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
            $inscription->update(['a_paye' => Inscription::PAYE]);

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

    public function updateFiche(Request $request, Adherent $adherent)
    {
        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'communication' => 'boolean',
            'bulletin' => 'boolean',
            'manif' => 'boolean',
            'date_naiss' => 'nullable|date',
            'genre' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:20',
            'ville' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:50',
            'mail' => 'nullable|email|max:255',
            'occupation' => 'nullable|string|max:255',
            'etablissement' => 'nullable|string|max:255',
            'regime_social' => 'nullable|string|max:255',
            'idee_metier' => 'nullable|string|max:1000',
            'decouverte_metier' => 'nullable|string|max:1000',
            'problemes_sante' => 'nullable|string|max:1000',
            'allergies' => 'nullable|string|max:1000',
            'conduite_a_tenir' => 'nullable|string|max:1000',
            'restrictions_alimentaires' => 'nullable|string|max:1000',
        ]);

        $validated['communication'] = $request->boolean('communication');
        $validated['bulletin'] = $request->boolean('bulletin');
        $validated['manif'] = $request->boolean('manif');

        $adherent->update($validated);

        return back()->with('success', 'La fiche de l\'adhérent a été mise à jour.');
    }
}
