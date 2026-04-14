<?php

namespace Tests\Feature;

use App\Models\Activite;
use App\Models\Adherent;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Services\HelloAssoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class PreInscriptionTest extends TestCase
{
    // DatabaseTransactions annule tout ce qui a été fait dans la BDD à la fin du test
    // pour garder ta base vivia_test toujours propre.
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Par défaut, on se place virtuellement le 15 juillet 2026 (période de pré-inscription)
        Carbon::setTestNow(Carbon::create(2026, 7, 15, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Réinitialiser le temps réel après chaque test
        parent::tearDown();
    }

    // =========================================================================
    // HELPERS POUR CRÉER DE LA DONNÉE MANUELLEMENT (SANS FACTORY)
    // =========================================================================

    private function creerAtelier(string $nom = 'Poterie', int $tarif = 200): Activite
    {
        return Activite::create([
            'nom'         => $nom,
            'type'        => 'activite',
            'tarif'       => $tarif,
            'is_archived' => false,
        ]);
    }

    private function creerAdherent(string $email = 'jean@test.fr'): Adherent
    {
        // Création manuelle d'un adhérent pour remplacer le "Factory" manquant
        return Adherent::create([
            'numero_adherent' => 'TEST-' . rand(1000, 9999),
            'nom'             => 'Dupont',
            'prenom'          => 'Jean',
            'mail'            => $email,
            'bulletin'        => 0,
            'communication'   => 0,
            'manif'           => 0,
            'actions'         => json_encode([]),
        ]);
    }

    private function sessionDeBase(array $overrides = []): array
    {
        return array_merge([
            'type_activite'    => 'atelier',
            'statut_juridique' => 'personne_physique',
            'nom'              => 'Dupont',
            'prenom'           => 'Jean',
            'mail'             => 'jean@test.fr',
            'is_adherent'      => 'non',
            '_last_completed'  => 9,
        ], $overrides);
    }

    private function sessionKey(string $token): string
    {
        return "adhesion_{$token}";
    }

    // =========================================================================
    // BLOC 1 – PRÉ-INSCRIPTION HELLOASSO EN JUILLET
    // =========================================================================

    public function test_preinscription_helloasso_un_atelier_facture_50_euros_acompte(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $token    = 'tok_preinsc_1_atelier';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(5000, \Mockery::any(), $token, \Mockery::any(), \Mockery::any()) // 5000 centimes = 50€
                ->andReturn('https://sandbox.helloasso.com/checkout-ok');
        });

        $response = $this->withSession([
            $this->sessionKey($token) => $this->sessionDeBase([
                'activites_selectionnees' => [$activite->id],
            ]),
        ])->post(route('adhesion.next', ['token' => $token]), [
            'current_step'  => 10,
            'mode_paiement' => 'helloasso',
        ]);

        $response->assertRedirect('https://sandbox.helloasso.com/checkout-ok');

        $adherent = Adherent::where('mail', 'jean@test.fr')->first();
        $this->assertNotNull($adherent);

        $this->assertDatabaseHas('inscriptions', [
            'id_adherent'   => $adherent->id,
            'a_paye'        => 'pre_inscrit',
            'montant'       => 210,   // 200 activité + 10 cotisation
            'type_adhesion' => 'atelier',
        ]);
    }

    public function test_preinscription_helloasso_deux_ateliers_facture_100_euros_acompte(): void
    {
        $a1    = $this->creerAtelier('Poterie', 200);
        $a2    = $this->creerAtelier('Peinture', 150);
        $token = 'tok_preinsc_2_ateliers';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(10000, \Mockery::any(), $token, \Mockery::any(), \Mockery::any()) // 100€
                ->andReturn('https://sandbox.helloasso.com/checkout-ok');
        });

        $this->withSession([
            $this->sessionKey($token) => $this->sessionDeBase([
                'activites_selectionnees' => [$a1->id, $a2->id],
            ]),
        ])->post(route('adhesion.next', ['token' => $token]), [
            'current_step'  => 10,
            'mode_paiement' => 'helloasso',
        ])->assertRedirect('https://sandbox.helloasso.com/checkout-ok');
    }

    public function test_preinscription_helloasso_return_cree_paiement_acompte_avec_commentaire(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $adherent = $this->creerAdherent('jean@test.fr'); // Utilise la création manuelle
        $token    = 'tok_preinsc_retour';

        $sessionData = $this->sessionDeBase([
            'activites_selectionnees' => [$activite->id],
            '_adherent_id'            => $adherent->id,
            '_last_completed'         => 10,
        ]);

        $response = $this->withSession([$this->sessionKey($token) => $sessionData])
            ->get(route('adhesion.helloasso.return', [
                'token'  => $token,
                'status' => 'success',
            ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('paiement', [
            'id_adherent' => $adherent->id,
            'montant'     => 50.0,
            'source'      => 'HelloAsso',
            'commentaire' => 'Acompte pré-inscription',
        ]);
    }

    public function test_preinscription_helloasso_return_idempotent_pas_de_doublon(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $adherent = $this->creerAdherent('jean@test.fr');
        $token    = 'tok_preinsc_idempotent';

        $sessionData = $this->sessionDeBase([
            'activites_selectionnees' => [$activite->id],
            '_adherent_id'            => $adherent->id,
            '_paiement1_cree'         => true,   // ← déjà traité
            '_last_completed'         => 10,
        ]);

        $this->withSession([$this->sessionKey($token) => $sessionData])
            ->get(route('adhesion.helloasso.return', [
                'token'  => $token,
                'status' => 'success',
            ]));

        $this->assertSame(
            0,
            Paiement::where('id_adherent', $adherent->id)->count(),
            'Aucun paiement ne doit être créé lors d\'un second appel (retry HelloAsso).'
        );
    }

    // =========================================================================
    // BLOC 2 – PAIEMENT INTERNE EN JUILLET
    // =========================================================================

    public function test_paiement_interne_juillet_ticket_acompte_et_cotisation(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $adherent = $this->creerAdherent('jean@test.fr');
        $token    = 'tok_interne_ticket';

        $sessionData = $this->sessionDeBase([
            'activites_selectionnees' => [$activite->id],
            'mode_paiement'           => 'interne',
            '_adherent_id'            => $adherent->id,
            '_last_completed'         => 10,
        ]);

        $response = $this->withSession([$this->sessionKey($token) => $sessionData])
            ->get(route('adhesion.show', ['token' => $token, 'step' => 11]));

        $response->assertStatus(200);

        $response->assertViewHas('ticket', function (?array $ticket) {
            if ($ticket === null) return false;

            $lignes = collect($ticket['lignes']);
            $ligneActivite = $lignes->first(fn($l) => $l['nom'] === 'Poterie (Acompte)');
            $ligneCotisation = $lignes->first(fn($l) => str_contains($l['nom'], 'Adhésion annuelle'));

            if (! $ligneActivite || (float) $ligneActivite['prix'] !== 50.0) return false;
            if (! $ligneCotisation || (float) $ligneCotisation['prix'] !== 10.0) return false;

            return (float) $ticket['total'] === 60.0;
        });

        $this->assertDatabaseMissing('paiement', [
            'id_adherent' => $adherent->id,
        ]);
    }

    public function test_paiement_interne_juillet_inscription_pre_inscrit_montant_reel(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $token    = 'tok_interne_inscription';

        $sessionDepart = $this->sessionDeBase([
            'activites_selectionnees' => [$activite->id],
            '_last_completed'         => 9,
        ]);

        $responseStep10 = $this->withSession([$this->sessionKey($token) => $sessionDepart])
            ->post(route('adhesion.next', ['token' => $token]), [
                'current_step'  => 10,
                'mode_paiement' => 'interne',
            ]);

        $responseStep10->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 11]));

        $sessionApresStep10 = $responseStep10->getSession()->get($this->sessionKey($token));

        $this->withSession([$this->sessionKey($token) => $sessionApresStep10])
            ->get(route('adhesion.show', ['token' => $token, 'step' => 11]))
            ->assertStatus(200);

        $adherent = Adherent::where('mail', 'jean@test.fr')->first();
        $this->assertNotNull($adherent);

        $this->assertDatabaseHas('inscriptions', [
            'id_adherent'   => $adherent->id,
            'a_paye'        => 'pre_inscrit',
            'montant'       => 210,
            'type_adhesion' => 'atelier',
        ]);
    }

    // =========================================================================
    // BLOC 3 – SOUTIEN / RECHERCHE PARTICIPATIVE
    // =========================================================================

    public function test_soutien_juillet_helloasso_pas_de_checkout_activite(): void
    {
        $token = 'tok_soutien_pas_checkout';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('createCheckout');
        });

        $response = $this->withSession([
            $this->sessionKey($token) => $this->sessionDeBase([
                'type_activite'           => 'soutien',
                'activites_selectionnees' => [],
            ]),
        ])->post(route('adhesion.next', ['token' => $token]), [
            'current_step'  => 10,
            'mode_paiement' => 'helloasso',
        ]);

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 10]));
        $this->assertTrue($response->getSession()->get("paiement1_done_{$token}", false));
    }

    public function test_recherche_juillet_helloasso_pas_de_checkout_activite(): void
    {
        $token = 'tok_recherche_pas_checkout';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('createCheckout');
        });

        $response = $this->withSession([
            $this->sessionKey($token) => $this->sessionDeBase([
                'type_activite'           => 'recherche',
                'activites_selectionnees' => [],
            ]),
        ])->post(route('adhesion.next', ['token' => $token]), [
            'current_step'  => 10,
            'mode_paiement' => 'helloasso',
        ]);

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 10]));
    }

    public function test_soutien_juillet_retour_helloasso2_cree_cotisation_10_euros(): void
    {
        $adherent = $this->creerAdherent('jean@test.fr');
        $token    = 'tok_soutien_cotis';

        $sessionData = $this->sessionDeBase([
            'type_activite'           => 'soutien',
            'activites_selectionnees' => [],
            '_adherent_id'            => $adherent->id,
            '_last_completed'         => 10,
        ]);

        $response = $this->withSession([$this->sessionKey($token) => $sessionData])
            ->get(route('adhesion.helloasso2.return', [
                'token'  => $token,
                'status' => 'success',
            ]));

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 11]));

        $this->assertDatabaseHas('paiement', [
            'id_adherent' => $adherent->id,
            'montant'     => 10.0,
            'source'      => 'HelloAsso',
            'commentaire' => 'Cotisation annuelle via HelloAsso',
        ]);
    }

    public function test_soutien_juillet_paiement_interne_inscription_en_attente_10_euros(): void
    {
        $token = 'tok_soutien_interne';

        $sessionDepart = $this->sessionDeBase([
            'type_activite'           => 'soutien',
            'activites_selectionnees' => [],
            '_last_completed'         => 9,
        ]);

        $responseStep10 = $this->withSession([$this->sessionKey($token) => $sessionDepart])
            ->post(route('adhesion.next', ['token' => $token]), [
                'current_step'  => 10,
                'mode_paiement' => 'interne',
            ]);

        $responseStep10->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 11]));

        $sessionApresStep10 = $responseStep10->getSession()->get($this->sessionKey($token));

        $this->withSession([$this->sessionKey($token) => $sessionApresStep10])
            ->get(route('adhesion.show', ['token' => $token, 'step' => 11]))
            ->assertStatus(200);

        $adherent = Adherent::where('mail', 'jean@test.fr')->first();
        $this->assertNotNull($adherent);

        $this->assertDatabaseHas('inscriptions', [
            'id_adherent'   => $adherent->id,
            'a_paye'        => 'En attente',
            'montant'       => 10.0,
            'type_adhesion' => 'soutien',
        ]);
    }

    // =========================================================================
    // BLOC 4 – HORS SAISON
    // =========================================================================

    public function test_hors_saison_octobre_facture_plein_tarif_pas_acompte(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 15, 12, 0, 0));

        $activite = $this->creerAtelier('Poterie', 200);
        $token    = 'tok_octobre_plein_tarif';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(20000, \Mockery::any(), $token, \Mockery::any(), \Mockery::any()) // 200€
                ->andReturn('https://sandbox.helloasso.com/checkout-ok');
        });

        $this->withSession([
            $this->sessionKey($token) => $this->sessionDeBase([
                'activites_selectionnees' => [$activite->id],
            ]),
        ])->post(route('adhesion.next', ['token' => $token]), [
            'current_step'  => 10,
            'mode_paiement' => 'helloasso',
        ])->assertRedirect('https://sandbox.helloasso.com/checkout-ok');

        $adherent = Adherent::where('mail', 'jean@test.fr')->first();
        $this->assertNotNull($adherent);

        $this->assertDatabaseHas('inscriptions', [
            'id_adherent' => $adherent->id,
            'a_paye'      => 'En attente',
            'montant'     => 210, // 200€ + 10€
        ]);
    }

    public function test_hors_saison_fevrier_applique_reduction_50_euros(): void
    {
        Carbon::setTestNow(Carbon::create(2027, 2, 10, 12, 0, 0));

        $activite = $this->creerAtelier('Poterie', 200);
        $token    = 'tok_fevrier_reduc';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(15000, \Mockery::any(), $token, \Mockery::any(), \Mockery::any()) // 150€
                ->andReturn('https://sandbox.helloasso.com/checkout-ok');
        });

        $this->withSession([
            $this->sessionKey($token) => $this->sessionDeBase([
                'activites_selectionnees' => [$activite->id],
            ]),
        ])->post(route('adhesion.next', ['token' => $token]), [
            'current_step'  => 10,
            'mode_paiement' => 'helloasso',
        ])->assertRedirect('https://sandbox.helloasso.com/checkout-ok');
    }

    // =========================================================================
    // BLOC 5 – FINALISATION PRÉ-INSCRIPTION EN SEPTEMBRE
    // =========================================================================

    public function test_finalisation_preinscription_septembre_solde_helloasso_et_statut_paye(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 15, 10, 0, 0));

        $token    = 'tok_finalisation_sept';
        $adherent = $this->creerAdherent('jean@test.fr');

        $inscription = Inscription::create([
            'id_adherent'      => $adherent->id,
            'saison'           => \App\Models\Saison::current(),
            'date_inscription' => '2026-07-15',
            'type_adhesion'    => 'atelier',
            'a_paye'           => 'pre_inscrit',
            'montant'          => 210.0,
            'renouvellement'   => false,
        ]);

        Paiement::create([
            'id_adherent'   => $adherent->id,
            'montant'       => 50.0,
            'source'        => 'HelloAsso',
            'date_paiement' => '2026-07-15',
            'commentaire'   => 'Acompte pré-inscription',
        ]);

        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(16000, \Mockery::any(), $token, \Mockery::any(), \Mockery::pattern('/Solde/')) // 160€
                ->andReturn('https://sandbox.helloasso.com/checkout-solde-ok');
        });

        $sessionData = [
            'is_adherent'          => 'oui',
            'numero_adherent'      => $adherent->numero_adherent,
            'statut_juridique'     => 'personne_physique',
            'type_activite'        => 'atelier',
            '_adherent_id'         => $adherent->id,
            '_pre_inscription_id'  => $inscription->id,
            '_last_completed'      => 16,
        ];

        $responseStep16 = $this->withSession([
            $this->sessionKey($token) => $sessionData,
        ])->post(route('adhesion.next', ['token' => $token]), [
            'current_step'           => 16,
            'action_pre_inscription' => 'pay_balance',
        ]);

        $responseStep16->assertRedirect('https://sandbox.helloasso.com/checkout-solde-ok');

        $sessionApresStep16 = $responseStep16->getSession()->get($this->sessionKey($token));

        $responseRetour = $this->withSession([
            $this->sessionKey($token) => $sessionApresStep16,
        ])->get(route('adhesion.helloasso.return', [
            'token'  => $token,
            'status' => 'success',
        ]));

        $this->assertDatabaseHas('paiement', [
            'id_adherent' => $adherent->id,
            'montant'     => 160.0,
            'source'      => 'HelloAsso',
        ]);

        $this->assertDatabaseHas('inscriptions', [
            'id'     => $inscription->id,
            'a_paye' => Inscription::EN_ATTENTE,
        ]);

        $responseRetour->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 11]));
    }

    public function test_finalisation_preinscription_reste_zero_valide_sans_helloasso(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 15, 10, 0, 0));

        $token    = 'tok_finalisation_zero';
        $adherent = $this->creerAdherent('jean@test.fr');

        $inscription = Inscription::create([
            'id_adherent'      => $adherent->id,
            'saison'           => \App\Models\Saison::current(),
            'date_inscription' => '2026-07-15',
            'type_adhesion'    => 'atelier',
            'a_paye'           => 'pre_inscrit',
            'montant'          => 50.0,
            'renouvellement'   => false,
        ]);

        Paiement::create([
            'id_adherent'   => $adherent->id,
            'montant'       => 50.0,
            'source'        => 'HelloAsso',
            'date_paiement' => '2026-07-15',
            'commentaire'   => 'Acompte pré-inscription',
        ]);

        $this->mock(HelloAssoService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('createCheckout');
        });

        $sessionData = [
            'is_adherent'          => 'oui',
            'numero_adherent'      => $adherent->numero_adherent,
            'statut_juridique'     => 'personne_physique',
            'type_activite'        => 'atelier',
            '_adherent_id'         => $adherent->id,
            '_pre_inscription_id'  => $inscription->id,
            '_last_completed'      => 16,
        ];

        $response = $this->withSession([
            $this->sessionKey($token) => $sessionData,
        ])->post(route('adhesion.next', ['token' => $token]), [
            'current_step'           => 16,
            'action_pre_inscription' => 'pay_balance',
        ]);

        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 11]));

        $this->assertDatabaseHas('inscriptions', [
            'id'     => $inscription->id,
            'a_paye' => Inscription::EN_ATTENTE,
        ]);
    }

    public function test_reinscription_adherent_existant_coche_renouvellement_a_true(): void
    {
        $activite = $this->creerAtelier('Dessin', 200);
        $adherent = $this->creerAdherent('ancien@test.fr');
        $token    = 'tok_reinscription';

        $sessionDepart = $this->sessionDeBase([
            'is_adherent'             => 'oui',
            'numero_adherent'         => $adherent->numero_adherent,
            'activites_selectionnees' => [$activite->id],
            '_last_completed'         => 9,
        ]);

        $responseStep10 = $this->withSession([$this->sessionKey($token) => $sessionDepart])
            ->post(route('adhesion.next', ['token' => $token]), [
                'current_step'  => 10,
                'mode_paiement' => 'interne',
            ]);

        $sessionApresStep10 = $responseStep10->getSession()->get($this->sessionKey($token));
        $this->withSession([$this->sessionKey($token) => $sessionApresStep10])
            ->get(route('adhesion.show', ['token' => $token, 'step' => 11]))
            ->assertStatus(200);

        $this->assertDatabaseHas('inscriptions', [
            'id_adherent'    => $adherent->id,
            'renouvellement' => 1,
        ]);
    }
}
