<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdhesionJourneyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_initialisation_genere_token_et_redirige_etape_1()
    {
        $response = $this->get(route('adhesion.index'));

        $response->assertRedirect();
        $this->assertStringContainsString('step=1', $response->headers->get('Location'));
        $this->assertNotEmpty(session()->all());
    }

    public function test_parcours_nouvel_adulte_standard()
    {
        $token = bin2hex(random_bytes(16));
        $sessionKey = "adhesion_{$token}";

        $this->withSession([
            $sessionKey => ['_last_completed' => 0]
        ]);

        $response = $this->post(route('adhesion.next', $token), [
            'current_step' => 1,
            'is_adherent' => 'non',
        ]);

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 12]));
    }

    public function test_parcours_structure_asso()
    {
        $token = bin2hex(random_bytes(16));
        $sessionKey = "adhesion_{$token}";

        $this->withSession([
            $sessionKey => [
                '_last_completed' => 0,
                'statut_juridique' => 'tpe_asso'
            ]
        ]);

        $response = $this->post(route('adhesion.next', $token), [
            'current_step' => 1,
            'is_adherent' => 'non',
            'statut_juridique' => 'tpe_asso'
        ]);

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 12]));

        $this->withSession([
            $sessionKey => [
                '_last_completed' => 2,
                'is_adherent' => 'non',
                'statut_juridique' => 'tpe_asso',
                'type_activite' => 'ressourcerie'
            ]
        ]);

        $response = $this->post(route('adhesion.next', $token), [
            'current_step' => 2,
            'type_activite' => 'ressourcerie',
        ]);

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 6]));
    }

    public function test_blocage_saut_etapes()
    {
        $token = bin2hex(random_bytes(16));

        $this->withSession([
            "adhesion_{$token}" => [
                '_last_completed' => 1,
                'is_adherent' => 'non'
            ]
        ]);

        $response = $this->get(route('adhesion.show', ['token' => $token, 'step' => 10]));

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 12]));
    }
}
