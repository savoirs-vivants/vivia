<?php

namespace App\Console\Commands;

use App\Models\Inscription;
use App\Models\Saison;
use Illuminate\Console\Command;

class MigrerPreinscritsCommand extends Command
{
    protected $signature   = 'inscriptions:migrer-preinscrits';
    protected $description = 'Passe les pré-inscrits en "En attente" au 1er septembre pour la nouvelle saison';

    public function handle(): void
    {
        $saison = Saison::current();

        $count = Inscription::where('saison', $saison)
            ->where('a_paye', 'pre_inscrit')
            ->update(['a_paye' => Inscription::EN_ATTENTE]);

        $this->info("{$count} pré-inscription(s) passée(s) en attente pour la saison {$saison}.");
    }
}
