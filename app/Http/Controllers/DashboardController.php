<?php

namespace App\Http\Controllers;

use App\Mail\EnfantAbsent;
use App\Models\Adherent;
use App\Models\Saison;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function index()
    {
        $saison = Saison::current();
        $user   = Auth::user();

        $mesActivitesIds = DB::table('activites_gestionnaire')
            ->where('id_users', Auth::id())
            ->pluck('id_activite')
            ->toArray();

        $isGestionnaire  = !empty($mesActivitesIds);
        $isRoleRestreint = in_array($user->role, ['coordinateur', 'animateur']);

        if ($isRoleRestreint && !$isGestionnaire) {
            return redirect()->route('activites.index');
        }

        $seanceData = $isGestionnaire
            ? $this->getProchaineSeanceData($mesActivitesIds, $saison)
            : ['seance' => null, 'adherents' => collect(), 'absentsIds' => []];

        if ($isRoleRestreint) {
            return view('dashboard', [
                'isGestionnaire'   => $isGestionnaire,
                'isRoleRestreint'  => true,
                'prochaineSeance'  => $seanceData['seance'],
                'adherentsSeance'  => $seanceData['adherents'],
                'absentsSeanceIds' => $seanceData['absentsIds'],
                'saison'           => $saison,
                'totalAdherents'   => 0,
                'newThisMonth'     => 0,
                'totalCotisations' => 0,
                'totalEnAttente'   => 0,
                'statutPaye'       => 0,
                'statutAttente'    => 0,
                'statutPartiel'    => 0,
                'activitesStats'   => collect(),
                'maxInscrits'      => 1,
                'repartitionTypes' => collect(),
            ]);
        }

        $totalAdherents = DB::table('inscriptions')
            ->where('saison', $saison)
            ->whereNotNull('id_adherent')
            ->whereIn('a_paye', ['oui', 'Payé'])
            ->distinct('id_adherent')
            ->count('id_adherent');

        $newThisMonth = DB::table('inscriptions')
            ->where('saison', $saison)
            ->whereMonth('date_inscription', now()->month)
            ->whereYear('date_inscription', now()->year)
            ->whereNotNull('id_adherent')
            ->whereIn('a_paye', ['oui', 'Payé'])
            ->distinct('id_adherent')
            ->count('id_adherent');

        $totalCotisations = DB::table('inscriptions')
            ->where('saison', $saison)
            ->whereIn('a_paye', ['oui', 'Payé'])
            ->sum('montant');

        $totalEnAttente = DB::table('inscriptions')
            ->where('saison', $saison)
            ->whereIn('a_paye', ['en attente', 'En attente', 'partiel', 'Partiel', 'non', 'Non'])
            ->sum('montant');

        $statutPaye = $totalAdherents;

        $statutAttente = DB::table('inscriptions')
            ->where('saison', $saison)
            ->whereNotNull('id_adherent')
            ->whereIn('a_paye', ['en attente', 'En attente', 'non', 'Non'])
            ->distinct('id_adherent')
            ->count('id_adherent');

        $statutPartiel = DB::table('inscriptions')
            ->where('saison', $saison)
            ->whereNotNull('id_adherent')
            ->whereIn('a_paye', ['partiel', 'Partiel'])
            ->distinct('id_adherent')
            ->count('id_adherent');

        $activitesStats = DB::table('activites')
            ->leftJoin('activites_adherents', function ($join) use ($saison) {
                $join->on('activites.id', '=', 'activites_adherents.id_activite')
                    ->where('activites_adherents.saison', '=', $saison)
                    ->where('activites_adherents.est_un_abandon', '=', 0);
            })
            ->select('activites.id', 'activites.nom', 'activites.type', 'activites.horaires', DB::raw('COUNT(activites_adherents.id) as total_inscrits'))
            ->groupBy('activites.id', 'activites.nom', 'activites.type', 'activites.horaires')
            ->orderByDesc('total_inscrits')
            ->take(3)
            ->get();

        $maxInscrits = $activitesStats->max('total_inscrits') ?: 1;

        $repartitionTypes = DB::table('inscriptions')
            ->where('saison', $saison)
            ->select('type_adhesion', DB::raw('COUNT(*) as total'))
            ->groupBy('type_adhesion')
            ->get();

        return view('dashboard', [
            'repartitionTypes' => $repartitionTypes,
            'saison'           => $saison,
            'totalAdherents'   => $totalAdherents,
            'newThisMonth'     => $newThisMonth,
            'activitesStats'   => $activitesStats,
            'maxInscrits'      => $maxInscrits,
            'totalCotisations' => $totalCotisations,
            'totalEnAttente'   => $totalEnAttente,
            'statutPaye'       => $statutPaye,
            'statutAttente'    => $statutAttente,
            'statutPartiel'    => $statutPartiel,
            'prochaineSeance'  => $seanceData['seance'],
            'adherentsSeance'  => $seanceData['adherents'],
            'absentsSeanceIds' => $seanceData['absentsIds'],
            'isGestionnaire'   => $isGestionnaire,
            'isRoleRestreint'  => $isRoleRestreint
        ]);
    }

    public function enregistrerAppel(Request $request, int $seance)
    {
        $seanceData = DB::table('seances')
            ->join('activites', 'seances.id_activite', '=', 'activites.id')
            ->select('seances.*', 'activites.nom as activite_nom')
            ->where('seances.id_seance', $seance)
            ->first();

        abort_if(!$seanceData, 404);

        $mesActivitesIds = DB::table('activites_gestionnaire')->where('id_users', Auth::id())->pluck('id_activite')->toArray();
        abort_if(!in_array($seanceData->id_activite, $mesActivitesIds), 403);

        $absents = $request->input('absents', []);

        /* Faire l'appel implique 3 opérations BDD (Suppression, Insertion, Mise à jour).
         * On utilise une Transaction. Si un mail échoue ou que la BDD plante, aucune donnée n'est
         * corrompue ou enregistrée à moitié.
         */
        DB::transaction(function () use ($seance, $seanceData, $absents) {
            DB::table('presence')->where('id_seance', $seance)->delete();

            if (!empty($absents)) {
                $presencesToInsert = [];
                $absentsIds = [];

                foreach ($absents as $absent) {
                    $idAdherent = (int) ($absent['id_adherent'] ?? 0);
                    if (!$idAdherent) continue;

                    $absentsIds[] = $idAdherent;
                    $presencesToInsert[] = [
                        'id_adherent' => $idAdherent,
                        'id_seance'   => $seance,
                        'statut'      => 'Absent',
                        'raison'      => $absent['motif'] ?? null,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }

                if (!empty($presencesToInsert)) {
                    DB::table('presence')->insert($presencesToInsert);
                }

                Carbon::setLocale('fr');
                $dateFormatee = Carbon::parse($seanceData->date)->isoFormat('dddd D MMMM YYYY à HH:mm');

                // On récupère TOUS les adhérents absents en 1 seule requête (au lieu de N requêtes dans la boucle)
                $adherentsAbsents = Adherent::with('tousLesTuteurs')->whereIn('id', $absentsIds)->get();

                foreach ($adherentsAbsents as $adherent) {
                    if (in_array($adherent->tranche_age, ['Enfant', 'Adolescent'])) {
                        $premierTuteur = $adherent->tousLesTuteurs->first();

                        if ($premierTuteur && $premierTuteur->mail) {
                            Mail::to($premierTuteur->mail)->send(new EnfantAbsent(
                                $adherent->prenom,
                                $seanceData->activite_nom,
                                $dateFormatee
                            ));
                        }
                    }
                }
            }

            DB::table('seances')->where('id_seance', $seance)->update([
                'statut'     => 'appel_fait',
                'updated_at' => now(),
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function terminerSeance(int $seance)
    {
        $seanceData = DB::table('seances')->where('id_seance', $seance)->first();
        abort_if(!$seanceData, 404);
        abort_if($seanceData->statut === 'terminee', 422, 'Séance déjà terminée.');

        $mesActivitesIds = DB::table('activites_gestionnaire')->where('id_users', Auth::id())->pluck('id_activite')->toArray();
        abort_if(!in_array($seanceData->id_activite, $mesActivitesIds), 403);

        DB::table('seances')->where('id_seance', $seance)->update([
            'statut'     => 'terminee',
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function envoyerMailAdherents(Request $request)
    {
        abort_if(in_array(Auth::user()->role, ['coordinateur', 'animateur']), 403);

        $request->validate([
            'type_mail'        => 'required|in:ag,info,bulletin',
            'cible_bulletin'   => 'required_if:type_mail,bulletin|in:general,creabot,schlouk_sciences',
            'objet'            => 'required|string|max:255',
            'message'          => 'required|string',
            'pieces_jointes'   => 'nullable|array|max:5',
            'pieces_jointes.*' => 'file|max:5120',
        ]);

        $saison = Saison::current();
        $typeMail = $request->input('type_mail');
        $themeCible = $request->input('cible_bulletin');

        $queryAdherents = DB::table('adherents')
            ->join('inscriptions', 'adherents.id', '=', 'inscriptions.id_adherent')
            ->where('inscriptions.saison', $saison)
            ->whereIn('inscriptions.a_paye', ['oui', 'Payé'])
            ->whereNotNull('adherents.mail')
            ->where('adherents.mail', '!=', '')
            ->select('adherents.mail')
            ->distinct();

        $queryStructures = DB::table('adherents_structure')
            ->join('inscriptions', 'adherents_structure.id', '=', 'inscriptions.id_structure')
            ->where('inscriptions.saison', $saison)
            ->whereIn('inscriptions.a_paye', ['oui', 'Payé'])
            ->whereNotNull('adherents_structure.mail')
            ->where('adherents_structure.mail', '!=', '')
            ->select('adherents_structure.mail')
            ->distinct();

        if ($typeMail === 'bulletin') {
            $queryAdherents->whereJsonContains('adherents.bulletin', $themeCible);
            $queryStructures->whereJsonContains('adherents_structure.bulletin', $themeCible);
        }

        $emailsAdherents  = $queryAdherents->pluck('mail')->toArray();
        $emailsStructures = $queryStructures->pluck('mail')->toArray();

        $tousLesEmails = array_unique(array_merge($emailsAdherents, $emailsStructures));

        if (empty($tousLesEmails)) {
            return back()->withErrors(['mail' => "Aucun adhérent ne correspond à ce critère de filtre pour cette saison."]);
        }

        $prefixe = match ($typeMail) {
            'ag'       => '[Assemblée Générale]',
            'bulletin' => '[Newsletter]',
            default    => '[Information]',
        };

        $sujet = trim($prefixe . ' ' . str_replace('[Newsletter]', '', $request->input('objet')));
        $contenuText = $request->input('message');

        $attachments = [];

        if ($request->hasFile('pieces_jointes')) {
            foreach ($request->file('pieces_jointes') as $file) {
                $attachments[] = [
                    'file_data' => file_get_contents($file->getRealPath()),
                    'name'      => $file->getClientOriginalName(),
                    'mime'      => $file->getMimeType(),
                ];
            }
        }

        set_time_limit(0);
        $mailsEnvoyes = 0;

        foreach ($tousLesEmails as $email) {
            try {
                Mail::send([], [], function ($message) use ($email, $sujet, $contenuText, $attachments) {
                    $message->to($email)
                        ->subject($sujet)
                        ->html(nl2br(e($contenuText)));

                    foreach ($attachments as $attachment) {
                        $message->attachData($attachment['file_data'], $attachment['name'], [
                            'mime' => $attachment['mime'],
                        ]);
                    }
                });
                $mailsEnvoyes++;

                usleep(100000);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur envoi mail groupé à {$email} : " . $e->getMessage());
            }
        }

        return back()->with('success', 'Votre email a été envoyé avec succès à ' . $mailsEnvoyes . ' contacts intéressés.');
    }

    /* ==============================================================================
     * MÉTHODES PRIVÉES
     * ============================================================================== */

    /**
     * Extrait la logique complexe de récupération de la prochaine séance pour un animateur.
     */
    private function getProchaineSeanceData(array $mesActivitesIds, string $saison): array
    {
        if (empty($mesActivitesIds)) {
            return ['seance' => null, 'adherents' => collect(), 'absentsIds' => []];
        }

        $seance = DB::table('seances')
            ->join('activites', 'seances.id_activite', '=', 'activites.id')
            ->whereIn('activites.id', $mesActivitesIds)
            ->where(function ($q) {
                $q->where('seances.statut', 'appel_fait')
                    ->orWhere(function ($q2) {
                        $q2->where('seances.date', '>', now())
                            ->where(function ($q3) {
                                $q3->whereNull('seances.statut')
                                    ->orWhere('seances.statut', '!=', 'terminee');
                            });
                    });
            })
            ->select(
                'seances.id_seance',
                'seances.id_activite',
                'seances.date',
                'seances.statut',
                'activites.nom as activite_nom',
                'activites.adresse',
                'activites.ville',
                DB::raw('(SELECT COUNT(*) FROM activites_adherents WHERE activites_adherents.id_activite = seances.id_activite AND activites_adherents.saison = "' . $saison . '" AND activites_adherents.est_un_abandon = 0) as nb_inscrits')
            )
            ->orderByRaw("CASE WHEN seances.statut = 'appel_fait' THEN 0 ELSE 1 END")
            ->orderBy('seances.date')
            ->first();

        if (!$seance) {
            return ['seance' => null, 'adherents' => collect(), 'absentsIds' => []];
        }

        $adherentsIds = DB::table('activites_adherents')
            ->join('inscriptions', function ($join) use ($saison) {
                $join->on('activites_adherents.id_adherent', '=', 'inscriptions.id_adherent')
                    ->where('inscriptions.saison', '=', $saison);
            })
            ->where('activites_adherents.id_activite', $seance->id_activite)
            ->where('activites_adherents.est_un_abandon', 0)
            ->whereNull('activites_adherents.date_sortie')
            ->whereNotIn('inscriptions.a_paye', ['En attente', 'en attente', 'non', 'Non'])
            ->pluck('activites_adherents.id_adherent');

        $adherents = Adherent::with('tousLesTuteurs')
            ->whereIn('id', $adherentsIds)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $absentsIds = DB::table('presence')
            ->where('id_seance', $seance->id_seance)
            ->where('statut', 'Absent')
            ->pluck('id_adherent')
            ->toArray();

        return ['seance' => $seance, 'adherents' => $adherents, 'absentsIds' => $absentsIds];
    }
}
