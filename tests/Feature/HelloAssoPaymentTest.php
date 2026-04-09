<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Adherent;
use App\Models\Activite;

class HelloAssoPaymentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_creation_checkout_activite_helloasso()
    {
        $activite = Activite::where('tarif', '>', 0)->where('type', 'activite')->first();

        if (!$activite) {
            $this->markTestSkipped('Aucune activité payante trouvée dans la base de test.');
        }

        $token = bin2hex(random_bytes(16));
        $sessionKey = "adhesion_{$token}";

        $this->withSession([
            $sessionKey => [
                '_last_completed' => 9,
                'is_adherent' => 'oui',
                '_adherent_id' => 1,
                'nom' => 'Testeur',
                'prenom' => 'Jean',
                'mail' => 'jean@test.com',
                'type_activite' => 'atelier',
                'activites_selectionnees' => [$activite->id],
            ]
        ]);

        $response = $this->post(route('adhesion.next', $token), [
            'current_step' => 10,
            'mode_paiement' => 'helloasso',
        ]);

        $this->assertStringContainsString('helloasso', $response->headers->get('Location'));
    }

    public function test_retour_positif_callback_helloasso()
    {
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent trouvé dans la base de test.');
        }

        $token = bin2hex(random_bytes(16));
        $sessionKey = "adhesion_{$token}";

        $this->withSession([
            $sessionKey => [
                '_last_completed' => 10,
                '_adherent_id' => $adherent->id,
                'is_adherent' => 'oui',
                'activites_selectionnees' => [1],
            ]
        ]);

        $response = $this->get(route('adhesion.helloasso.return', [
            'token' => $token,
            'status' => 'return'
        ]));

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 11]));

        $this->assertDatabaseHas('paiement', [
            'id_adherent' => $adherent->id,
            'source' => 'HelloAsso',
        ]);

        $this->assertTrue(session($sessionKey)['_helloasso_ok']);
    }

    public function test_annulation_paiement_helloasso()
    {
        $token = bin2hex(random_bytes(16));

        $this->withSession([
            "adhesion_{$token}" => [
                '_last_completed' => 10,
            ]
        ]);

        $response = $this->get(route('adhesion.helloasso.return', [
            'token' => $token,
            'status' => 'cancel'
        ]));

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 10]));

        $response->assertSessionHasErrors('helloasso');
    }
}
