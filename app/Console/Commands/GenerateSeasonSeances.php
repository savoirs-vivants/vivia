<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activite;
use App\Services\ActiviteService;

class GenerateSeasonSeances extends Command
{
    protected $signature = 'seances:generate-season';
    protected $description = 'Génère automatiquement les séances pour la nouvelle saison (à lancer le 1er septembre)';

    public function handle(ActiviteService $activiteService)
    {
        $this->info('Début de la génération des séances...');

        $activites = Activite::where('is_archived', false)
                             ->where('type', '!=', 'recherche')
                             ->whereNotNull('horaires')
                             ->get();

        $count = 0;
        foreach ($activites as $activite) {
            $activiteService->genererSeancesAuto($activite);
            $count++;
        }

        $this->info("Génération terminée avec succès pour {$count} activités.");
    }
}
