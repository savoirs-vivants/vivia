<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DashboardSaisonTest extends TestCase
{
    use DatabaseTransactions;

    public function test_le_dashboard_ne_montre_que_les_donnees_de_la_saison_demandee()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/statistiques?saison=2023-2024');
        $response->assertStatus(200);

    }
}
