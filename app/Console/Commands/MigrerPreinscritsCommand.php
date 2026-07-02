<?php

namespace App\Console\Commands;

use App\Models\Inscription;
use App\Models\Saison;
use Illuminate\Console\Command;

class MigrerPreinscritsCommand extends Command
{
    protected $signature   = 'inscriptions:migrer-preinscrits {--force : Forcer l\'exécution même hors septembre}';
    protected $description = 'Passe les pré-inscrits en "En attente" au 1er septembre pour la nouvelle saison';

    public function handle(): void
    {
        if (now()->month !== 9 && !$this->option('force')) {
            $this->error('Cette commande est conçue pour le 1er septembre uniquement.');
            $this->line('Utilisez --force pour forcer l\'exécution.');
            return;
        }

        $saison = Saison::current();

        $count = Inscription::where('saison', $saison)
            ->where('a_paye', 'pre_inscrit')
            ->update(['a_paye' => Inscription::EN_ATTENTE]);

        $this->info("{$count} pré-inscription(s) passée(s) en attente pour la saison {$saison}.");
    }
}
