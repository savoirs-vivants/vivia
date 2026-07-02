<?php

namespace App\Console\Commands;

use App\Models\Inscription;
use App\Models\Saison;
use App\Mail\RentreePreInscrits;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRentreeEmails extends Command
{
    protected $signature = 'email:rentree-pre-inscrits';

    protected $description = 'Envoie un email de rentrée à tous les adhérents ayant une pré-inscription en cours.';

    public function handle()
    {
        $saison = Saison::current();

        $this->info("Recherche des pré-inscrits pour la saison {$saison}...");

        $inscriptions = Inscription::with('adherent', 'adherent.tousLesTuteurs', 'adherent.paiements')
            ->where('saison', $saison)
            ->where(function ($q) {
                // La migration (00:01) a déjà passé les pre_inscrit en en_attente avant cet envoi (10:00)
                $q->whereIn('a_paye', ['pre_inscrit', 'acompte_paye'])
                  ->orWhere(function ($q2) {
                      $q2->where('a_paye', 'En attente')->where('is_preinscription', true);
                  });
            })
            ->get();

        if ($inscriptions->isEmpty()) {
            $this->info("Aucune pré-inscription trouvée.");
            return;
        }

        $count = 0;

        foreach ($inscriptions as $inscription) {
            $adherent = $inscription->adherent;

            if ($adherent) {
                $destinataire = $this->resoudreEmailContact($adherent);
                $totalVerse   = (float) $adherent->paiements->sum('montant');
                $resteAPayer  = max(0, (float) $inscription->montant - $totalVerse);

                if ($destinataire) {
                    Mail::to($destinataire)->send(new RentreePreInscrits($adherent, $inscription, $resteAPayer));
                    $count++;
                }
            }
        }

        $this->info("Terminé ! {$count} e-mails de rentrée ont été envoyés.");
        Log::info("Commande email:rentree-pre-inscrits exécutée. {$count} e-mails envoyés.");
    }

    private function resoudreEmailContact($adherent): ?string
    {
        if (in_array($adherent->tranche_age, ['Enfant', 'Adolescent'])) {
            return $adherent->tousLesTuteurs()->first()?->mail;
        }
        return $adherent->mail;
    }
}
