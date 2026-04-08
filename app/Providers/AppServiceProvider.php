<?php

namespace App\Providers;

use App\Models\Saison;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (str_contains(config('app.url'), 'ngrok')) {
            URL::forceScheme('https');
        }

        try {
            Saison::syncActive();
        } catch (\Throwable) {
        }

        Gate::define('acces-equipe', fn(User $u) => $u->role === 'admin');

        Gate::define('gerer-activites', fn(User $u) => in_array($u->role, ['admin', 'coordinateur']));

        Gate::define('gerer-ressourcerie', fn(User $u) => in_array($u->role, ['admin', 'coordinateur']));

        Gate::define('acces-statistiques', fn(User $u) => in_array($u->role, ['admin', 'comptable']));

        Gate::define('voir-tous-adherents', fn(User $u) => in_array($u->role, ['admin', 'comptable']));
    }
}

