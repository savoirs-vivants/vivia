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
use Illuminate\Support\Str;
use App\Http\Requests\CommentaireAdherentRequest;
use App\Http\Requests\AjouterVersementRequest;
use App\Http\Requests\UpdateFicheAdherentRequest;
use App\Models\Seance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class AdherentController extends Controller
{
    public function index(Request $request)
    {
        $user         = Auth::user();
        $tab          = $request->get('tab', 'payes');
        $search       = $request->get('q');
        $filterSource = $request->get('source');
        $filterStatut = $request->get('statut');
        $saison       = Saison::current();

        $canVoirTousStatuts = in_array($user->role, ['admin', 'comptable']);
        if (!$canVoirTousStatuts) {
            $tab = 'payes';
        }

        $mesActivitesIds = $user->role === 'animateur'
            ? DB::table('activites_gestionnaire')->where('id_users', $user->id)->pluck('id_activite')
            : null;

        $base = Adherent::with(['inscriptions', 'inscription', 'activitesActives', 'paiements'])
            ->when($search, fn($q) => $q->where(function ($query) use ($search) {
                $query->where('prenom', 'like', "%{$search}%")
                    ->orWhere('nom',  'like', "%{$search}%")
                    ->orWhere('mail', 'like', "%{$search}%");
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
            ->when($mesActivitesIds, fn($q) => $q->whereHas(
                'activites',
                fn($q2) => $q2->whereIn('activites_adherents.id_activite', $mesActivitesIds)
            ));

        $mois = now()->month;
        $saisonPreinscriptions = ($mois === 7 || $mois === 8) ? Saison::preinscriptions() : $saison;

        $queryPayes       = (clone $base)->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PAYE)->where('saison', $saison));
        $queryAttente     = (clone $base)->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::EN_ATTENTE)->whereIn('saison', array_unique([$saison, $saisonPreinscriptions])));
        $queryPartiel     = (clone $base)->whereHas('inscriptions', fn($q) => $q->where('a_paye', Inscription::PARTIEL)->where('saison', $saison));
        $queryPreInscrits = (clone $base)->whereHas('inscriptions', fn($q) => $q->where('a_paye', 'pre_inscrit')->where('saison', $saisonPreinscriptions));

        $countPayes       = (clone $queryPayes)->count();
        $countAttente     = $canVoirTousStatuts ? (clone $queryAttente)->count() : 0;
        $countPartiel     = $canVoirTousStatuts ? (clone $queryPartiel)->count() : 0;
        $countPreInscrits = $canVoirTousStatuts ? (clone $queryPreInscrits)->count() : 0;

        $adherentsPayes       = $queryPayes->orderBy('nom')->paginate(25)->withQueryString();
        $adherentsEnAttente   = $canVoirTousStatuts ? $queryAttente->orderBy('nom')->paginate(25)->withQueryString() : collect();
        $adherentsPartiel     = $canVoirTousStatuts ? $queryPartiel->orderBy('nom')->paginate(25)->withQueryString() : collect();
        $adherentsPreInscrits = $canVoirTousStatuts ? $queryPreInscrits->orderBy('nom')->paginate(25)->withQueryString() : collect();

        $baseStructures = AdherentStructure::with(['inscription', 'paiements'])
            ->when($search, fn($q) => $q->where(function ($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
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

        $filterType = $request->get('type', 'tous');
        $structuresList = match ($tab) {
            'payes'   => $structuresPayees,
            'attente' => $structuresEnAttente,
            default   => collect(),
        };
        $items = match ($tab) {
            'payes'        => $adherentsPayes,
            'partiel'      => $adherentsPartiel,
            'pre_inscrits' => $adherentsPreInscrits,
            default        => $adherentsEnAttente,
        };

        return view('adherents.index', compact(
            'tab',
            'search',
            'filterSource',
            'filterType',
            'items',
            'structuresList',
            'adherentsPayes',
            'adherentsEnAttente',
            'adherentsPartiel',
            'adherentsPreInscrits',
            'countPayes',
            'countAttente',
            'countPartiel',
            'countPreInscrits',
            'structuresEnAttente',
            'structuresPayees',
            'canVoirTousStatuts'
        ));
    }

    public function show(Adherent $adherent)
    {

        $saison = Saison::current();
        abort_if(
            !$adherent->inscriptions()->where('saison', $saison)->exists(),
            403,
            "Cet adhérent n'est pas inscrit pour la saison active ({$saison})."
        );

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

        /* La jointure manuelle ici est nécessaire car on doit filtrer les séances
         * strictement contenues entre la date_entree et la date_sortie (ou null) du pivot
         * pour éviter d'imputer des absences à un adhérent avant son inscription ou après son abandon.
         */
        $seances = \App\Models\Seance::with('activite')
            ->join('activites_adherents', function ($join) use ($adherent) {
                $join->on('seances.id_activite', '=', 'activites_adherents.id_activite')
                    ->where('activites_adherents.id_adherent', $adherent->id);
            })
            ->whereRaw('DATE(seances.date) >= activites_adherents.date_entree')
            ->where(function ($q) {
                $q->whereNull('activites_adherents.date_sortie')
                    ->orWhereRaw('DATE(seances.date) <= activites_adherents.date_sortie');
            })
            ->select('seances.*')
            ->orderByDesc('seances.date')
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

        $presences = $toutesLesSeances->filter(fn($s) => \Carbon\Carbon::parse($s->date)->isPast() || $s->statut === 'terminee');

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

    public function destroy(Adherent $adherent)
    {
        $nom = "{$adherent->prenom} {$adherent->nom}";
        $adherent->delete();

        return redirect()->route('adherents.index')
            ->with('success', "{$nom} a été supprimé(e) définitivement.");
    }

    public function uploaderFichiers(Request $request, Adherent $adherent)
    {
        $request->validate([
            'nouveaux_fichiers' => 'required|array',
            'nouveaux_fichiers.*' => 'file|max:5120',
        ]);

        $fichiersExistants = $adherent->commentaire ?? [];

        if ($request->hasFile('nouveaux_fichiers')) {
            foreach ($request->file('nouveaux_fichiers') as $fichier) {

                $chemin = $fichier->store('adherents/documents', 'local');

                $fichiersExistants[] = [
                    'chemin' => $chemin,
                    'nom_original' => $fichier->getClientOriginalName(),
                    'type' => $fichier->getClientMimeType(),
                    'date_ajout' => now()->toDateTimeString(),
                ];
            }
        }

        $adherent->commentaire = $fichiersExistants;
        $adherent->save();

        return back()->with('success', 'Fichiers ajoutés en toute sécurité.');
    }


    public function voirFichier(Adherent $adherent, $index)
    {
        $fichiers = $adherent->commentaire ?? [];

        if (!isset($fichiers[$index])) {
            abort(404, "Fichier introuvable.");
        }

        $chemin = $fichiers[$index]['chemin'];

        if (!Storage::disk('local')->exists($chemin)) {
            abort(404, "Le fichier n'existe plus sur le serveur.");
        }

        return response()->file(storage_path('app/' . $chemin));
    }
    /* Les paiements touchent plusieurs tables critiques. On utilise DB::transaction
         * pour garantir que si l'envoi du mail échoue, on ne sauvegarde pas l'état "Payé"
         * en base de données. C'est le principe de l'atomicité.
         */
    public function valider(Request $request, Adherent $adherent)
    {
        return DB::transaction(function () use ($request, $adherent) {
            $saison = Saison::current();
            $statut = $request->boolean('plusieurs_versements') ? Inscription::PARTIEL : Inscription::PAYE;

            // NOUVEAU : On cherche une inscription "en_attente" OU "pre_inscrit"
            $inscriptionEnAttente = $adherent->inscriptions()
                ->where('saison', $saison)
                ->whereIn('a_paye', [Inscription::EN_ATTENTE, 'pre_inscrit'])
                ->latest()
                ->first();

            if (!$inscriptionEnAttente) {
                return redirect()->route('adherents.index', ['tab' => 'attente'])
                    ->with('error', 'Aucune inscription en attente ou pré-inscription trouvée.');
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
            } else {
                $inscriptionEnAttente->update(['a_paye' => $statut]);

                if ($statut === Inscription::PARTIEL && $request->filled('montant_recu')) {
                    $adherent->paiements()->create([
                        'montant'       => (float) $request->montant_recu,
                        'source'        => 'Interne',
                        'date_paiement' => now()->toDateString(),
                        'commentaire'   => '1er versement (Partiel)',
                    ]);
                }

                if ($statut === Inscription::PAYE) {
                    // NOUVEAU : Calcul magique pour ne faire payer que le reste ! (Total - Déjà payé cet été)
                    $totalDejaVerse = $adherent->paiements()->sum('montant');
                    $resteAPayer = max(0, $inscriptionEnAttente->montant - $totalDejaVerse);

                    if ($resteAPayer > 0) {
                        $adherent->paiements()->create([
                            'montant'       => $resteAPayer,
                            'source'        => 'Interne',
                            'date_paiement' => now()->toDateString(),
                            'commentaire'   => $ancienStatut === 'pre_inscrit' ? 'Solde pré-inscription' : 'Paiement intégral',
                        ]);
                    }
                }
            }

            if ($statut === Inscription::PAYE && $ancienStatut !== Inscription::PAYE) {
                $destinataire = $this->resoudreEmailContact($adherent);
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
                ->with('success', "{$adherent->prenom} {$adherent->nom} " . ($isReinscription ? ' — réinscription fusionnée et ' : ' — ') . strtolower($statut) . '.');
        });
    }

    public function showStructure(AdherentStructure $structure)
    {

        $saison = Saison::current();
        abort_if(
            !$structure->inscriptions()->where('saison', $saison)->exists(),
            403,
            "Cette structure n'est pas inscrite pour la saison active ({$saison})."
        );

        $structure->load(['inscriptions', 'inscription', 'paiements']);
        $saisons   = $structure->inscriptions->sortByDesc('saison');
        $totalPaye = (float) $structure->paiements->sum('montant');

        return view('adherents.show_structure', compact('structure', 'saisons', 'totalPaye'));
    }

    public function ajouterVersement(AjouterVersementRequest $request, Adherent $adherent)
    {
        return DB::transaction(function () use ($request, $adherent) {
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

                $destinataire = $this->resoudreEmailContact($adherent);
                if ($destinataire) {
                    Mail::to($destinataire)->send(new AdhesionValidee($adherent));
                }

                return redirect()->route('adherents.index', ['tab' => 'payes'])
                    ->with('success', "{$adherent->prenom} {$adherent->nom} — solde complet, passé en Payé.");
            }

            $reste = number_format($totalDu - $totalVerse, 2, ',', ' ');
            return redirect()->route('adherents.index', ['tab' => 'partiel'])
                ->with('success', "Versement enregistré. Reste dû : {$reste} €");
        });
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
            ->with('success', "La structure {$structure->nom} est passée en statut : " . strtolower($statut) . ".");
    }

    public function downloadPdf(Adherent $adherent)
    {
        $adherent->load(['tousLesTuteurs', 'activitesActives', 'inscription', 'paiements']);
        $seances = Seance::with('activite')
            ->join('activites_adherents', function ($join) use ($adherent) {
                $join->on('seances.id_activite', '=', 'activites_adherents.id_activite')
                    ->where('activites_adherents.id_adherent', $adherent->id);
            })
            ->whereRaw('DATE(seances.date) >= activites_adherents.date_entree')
            ->where(function ($q) {
                $q->whereNull('activites_adherents.date_sortie')
                    ->orWhereRaw('DATE(seances.date) <= activites_adherents.date_sortie');
            })
            ->select('seances.*')
            ->orderByDesc('seances.date')
            ->get();

        $absencesMap = Presence::where('id_adherent', $adherent->id)
            ->whereIn('id_seance', $seances->pluck('id_seance'))
            ->get()
            ->keyBy('id_seance');

        $toutesLesSeances = $seances->map(function ($seance) use ($absencesMap) {
            $presence = $absencesMap->get($seance->id_seance);
            $seance->statut_presence = $presence?->statut ?? 'Présent';
            return $seance;
        });

        $presences = $toutesLesSeances->filter(fn($s) => \Carbon\Carbon::parse($s->date)->isPast() || $s->statut === 'terminee');


        $pdf = PDF::loadView('adherents.pdf', compact('adherent', 'presences'));

        $fileName = 'fiche_' . Str::slug($adherent->prenom . '_' . $adherent->nom) . '.pdf';

        return $pdf->download($fileName);
    }

    public function downloadPdfStructure(AdherentStructure $structure)
    {
        $structure->load(['inscription', 'paiements']);
        $totalPaye = (float) $structure->paiements->sum('montant');

        $pdf = Pdf::loadView('adherents.structure_pdf', compact('structure', 'totalPaye'));

        $fileName = 'fiche_structure_' . Str::slug($structure->nom) . '.pdf';
        return $pdf->download($fileName);
    }

    public function updateFiche(UpdateFicheAdherentRequest $request, Adherent $adherent)
    {
        $validated = $request->validated();

        $validated['communication'] = $request->boolean('communication');
        $validated['bulletin']      = $request->input('bulletin', []);
        $validated['manif']         = $request->boolean('manif');

        $adherent->update($validated);

        return back()->with('success', 'La fiche de l\'adhérent a été mise à jour.');
    }

    /* ==============================================================================
     * MÉTHODES PRIVÉES
     * ============================================================================== */

    private function resoudreEmailContact(Adherent $adherent): ?string
    {
        if (in_array($adherent->tranche_age, ['Enfant', 'Adolescent'])) {
            return $adherent->tousLesTuteurs()->first()?->mail;
        }

        return $adherent->mail;
    }
}
