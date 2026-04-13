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

/**
 * Tests du flux d'inscription/pré-inscription.
 *
 * Couverture :
 *   BLOC 1 – Pré-inscription HelloAsso en juillet (type = atelier)
 *   BLOC 2 – Paiement interne en juillet (ticket et inscription)
 *   BLOC 3 – Soutien / Recherche participative en juillet
 *   BLOC 4 – Hors-saison (régression, août exclu intentionnellement)
 *
 * ⚠️  AVANT DE LANCER CES TESTS :
 *     Supprimer la ligne Carbon::setTestNow(...) présente dans next()
 *     du contrôleur AdherentFormulaireController. Ce code de test ne doit
 *     jamais se retrouver en production.
 */
class PreInscriptionTest extends TestCase
{
    use DatabaseTransactions;

    // =========================================================================
    // SETUP / TEARDOWN
    // =========================================================================

    protected function setUp(): void
    {
        parent::setUp();
        // Par défaut, on est le 15 juillet 2026 (période de pré-inscription)
        Carbon::setTestNow(Carbon::create(2026, 7, 15, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Indispensable : réinitialiser après chaque test
        parent::tearDown();
    }

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================

    /**
     * Crée une activité de type atelier (type DB = 'activite') avec les
     * champs minimum requis par le contrôleur.
     *
     * Note : dans la DB, les ateliers ont type='activite' (≠ la valeur
     * 'atelier' du champ formData['type_activite'] qui représente le choix
     * de l'utilisateur dans le formulaire).
     */
    private function creerAtelier(string $nom = 'Poterie', int $tarif = 200): Activite
    {
        return Activite::create([
            'nom'         => $nom,
            'type'        => 'activite', // ← type DB pour un atelier
            'tarif'       => $tarif,
            'is_archived' => false,
        ]);
    }

    /**
     * Session de base simulant un nouvel adhérent non-mineur, non-structure,
     * arrivé à l'étape 9 (juste avant Paiement).
     */
    private function sessionDeBase(array $overrides = []): array
    {
        return array_merge([
            'type_activite'  => 'atelier',
            'statut_juridique' => 'personne_physique',
            'nom'            => 'Dupont',
            'prenom'         => 'Jean',
            'mail'           => 'jean@test.fr',
            'is_adherent'    => 'non',
            '_last_completed' => 9,
        ], $overrides);
    }

    /** Retourne le token de session associé à une clé standard */
    private function sessionKey(string $token): string
    {
        return "adhesion_{$token}";
    }

    // =========================================================================
    // BLOC 1 – PRÉ-INSCRIPTION HELLOASSO EN JUILLET (type = atelier)
    // =========================================================================

    /**
     * En juillet, le checkout HelloAsso pour 1 atelier à 200€ doit envoyer
     * seulement 50€ (5 000 centimes) — jamais le plein tarif.
     */
    public function test_preinscription_helloasso_un_atelier_facture_50_euros_acompte(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $token    = 'tok_preinsc_1_atelier';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(
                    5000,             // 50 € = 5 000 centimes  ← assertion principale
                    \Mockery::any(),  // payerInfo
                    $token,
                    \Mockery::any(),  // route de retour
                    \Mockery::any()   // label
                )
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

        // L'adhérent doit être créé AVANT la redirection vers HelloAsso
        $adherent = Adherent::where('mail', 'jean@test.fr')->first();
        $this->assertNotNull($adherent, "L'adhérent doit être persisté avant le checkout.");

        // Inscription en pré-inscrit, montant = tarif réel (200€) + cotisation (10€)
        // car Inscription::montant représente le coût total de la saison,
        // pas l'acompte encaissé.
        $this->assertDatabaseHas('inscriptions', [
            'id_adherent'   => $adherent->id,
            'a_paye'        => 'pre_inscrit',
            'montant'       => 210,   // 200 activité + 10 cotisation (tarif réel)
            'type_adhesion' => 'atelier',
        ]);
    }

    /**
     * En juillet, 2 ateliers → acompte HelloAsso = 100€ (2 × 50€).
     */
    public function test_preinscription_helloasso_deux_ateliers_facture_100_euros_acompte(): void
    {
        $a1    = $this->creerAtelier('Poterie', 200);
        $a2    = $this->creerAtelier('Peinture', 150);
        $token = 'tok_preinsc_2_ateliers';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(10000, \Mockery::any(), $token, \Mockery::any(), \Mockery::any())
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

    /**
     * Après le retour HelloAsso (succès), un paiement de 50€ est créé
     * avec le commentaire "Acompte pré-inscription".
     * Le flag _paiement1_cree est posé pour éviter le double-enregistrement.
     */
    public function test_preinscription_helloasso_return_cree_paiement_acompte_avec_commentaire(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $adherent = Adherent::factory()->create(['mail' => 'jean@test.fr']);
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

        // Redirigé vers step 10 (pour payer la cotisation ensuite)
        $response->assertRedirect();

        $this->assertDatabaseHas('paiements', [
            'id_adherent' => $adherent->id,
            'montant'     => 50.0,
            'source'      => 'HelloAsso',
            'commentaire' => 'Acompte pré-inscription',
        ]);

        // Le flag doit être posé pour bloquer un double-enregistrement
        $sessionApresRetour = $response->getSession()->get($this->sessionKey($token));
        $this->assertTrue(
            $sessionApresRetour['_paiement1_cree'] ?? false,
            'Le flag _paiement1_cree doit être posé après retour HelloAsso.'
        );
    }

    /**
     * Si helloassoReturn est appelé une 2e fois (retry HelloAsso), aucun
     * paiement supplémentaire ne doit être créé (idempotence).
     */
    public function test_preinscription_helloasso_return_idempotent_pas_de_doublon(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $adherent = Adherent::factory()->create(['mail' => 'jean@test.fr']);
        $token    = 'tok_preinsc_idempotent';

        $sessionData = $this->sessionDeBase([
            'activites_selectionnees' => [$activite->id],
            '_adherent_id'            => $adherent->id,
            '_paiement1_cree'         => true,   // ← déjà traité une 1ère fois
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
    // BLOC 2 – PAIEMENT INTERNE EN JUILLET (ticket + inscription)
    // =========================================================================

    /**
     * En juillet avec paiement interne, l'étape 11 doit afficher un ticket
     * contenant l'acompte à 50€ et la cotisation à 10€, soit 60€ au total.
     * Aucun enregistrement Paiement automatique ne doit être créé
     * (le paiement se fait manuellement en présentiel).
     */
    public function test_paiement_interne_juillet_ticket_acompte_et_cotisation(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $adherent = Adherent::factory()->create(['mail' => 'jean@test.fr']);
        $token    = 'tok_interne_ticket';

        // On pré-renseigne _adherent_id pour que show() n'en crée pas un second
        $sessionData = $this->sessionDeBase([
            'activites_selectionnees' => [$activite->id],
            'mode_paiement'           => 'interne',
            '_adherent_id'            => $adherent->id,
            '_last_completed'         => 10,
        ]);

        $response = $this->withSession([$this->sessionKey($token) => $sessionData])
            ->get(route('adhesion.show', ['token' => $token, 'step' => 11]));

        $response->assertStatus(200);

        // --- Vérification du ticket ---
        $response->assertViewHas('ticket', function (?array $ticket) {
            if ($ticket === null) {
                return false;
            }

            $lignes = collect($ticket['lignes']);

            // Ligne acompte : nom = "Poterie (Acompte)", prix = 50,00€
            $ligneActivite = $lignes->first(fn($l) => $l['nom'] === 'Poterie (Acompte)');
            if (! $ligneActivite || (float) $ligneActivite['prix'] !== 50.0) {
                return false;
            }

            // Ligne cotisation : nom contient "Adhésion annuelle", prix = 10,00€
            $ligneCotisation = $lignes->first(fn($l) => str_contains($l['nom'], 'Adhésion annuelle'));
            if (! $ligneCotisation || (float) $ligneCotisation['prix'] !== 10.0) {
                return false;
            }

            // Total = 50 + 10 = 60€
            return (float) $ticket['total'] === 60.0;
        });

        // --- Aucun Paiement automatique en BDD pour le mode interne ---
        $this->assertDatabaseMissing('paiements', [
            'id_adherent' => $adherent->id,
        ]);
    }

    /**
     * Flux complet paiement interne en juillet :
     * valider step 10 → step 11 crée l'adhérent avec statut pre_inscrit
     * et le montant total réel (pas l'acompte).
     */
    public function test_paiement_interne_juillet_inscription_pre_inscrit_montant_reel(): void
    {
        $activite = $this->creerAtelier('Poterie', 200);
        $token    = 'tok_interne_inscription';

        // 1. Valider l'étape 10 en mode interne
        $sessionDepart = $this->sessionDeBase([
            'activites_selectionnees' => [$activite->id],
            '_last_completed'         => 9,
        ]);

        $responseStep10 = $this->withSession([$this->sessionKey($token) => $sessionDepart])
            ->post(route('adhesion.next', ['token' => $token]), [
                'current_step'  => 10,
                'mode_paiement' => 'interne',
            ]);

        $responseStep10->assertRedirect(
            route('adhesion.show', ['token' => $token, 'step' => 11])
        );

        // 2. Afficher le step 11 (déclenche sauvegarderAdherent)
        $sessionApresStep10 = $responseStep10->getSession()->get($this->sessionKey($token));

        $this->withSession([$this->sessionKey($token) => $sessionApresStep10])
            ->get(route('adhesion.show', ['token' => $token, 'step' => 11]))
            ->assertStatus(200);

        // 3. Vérifier la DB
        $adherent = Adherent::where('mail', 'jean@test.fr')->first();
        $this->assertNotNull($adherent, "L'adhérent doit avoir été créé à l'étape 11.");

        $this->assertDatabaseHas('inscriptions', [
            'id_adherent'   => $adherent->id,
            'a_paye'        => 'pre_inscrit',
            'montant'       => 210,       // 200€ tarif réel + 10€ cotisation
            'type_adhesion' => 'atelier',
            'renouvellement' => false,
        ]);
    }

    // =========================================================================
    // BLOC 3 – SOUTIEN / RECHERCHE PARTICIPATIVE EN JUILLET
    // =========================================================================

    /**
     * En juillet, un nouvel adhérent "soutien" n'a aucune activité à payer.
     * HelloAsso ne doit PAS être appelé (pas de createCheckout).
     * Le contrôleur redirige vers step 10 en posant paiement1_done
     * pour indiquer que le 1er paiement (activités) est "traité" (inexistant).
     */
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

        // Redirigé vers step 10 (attente paiement cotisation)
        $response->assertRedirect(route('adhesion.show', ['token' => $token, 'step' => 10]));

        // paiement1_done marqué dans la session
        $this->assertTrue(
            $response->getSession()->get("paiement1_done_{$token}", false),
            'paiement1_done doit être posé pour un soutien sans activité.'
        );
    }

    /**
     * Même comportement pour "recherche participative".
     */
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
        $this->assertTrue($response->getSession()->get("paiement1_done_{$token}", false));
    }

    /**
     * Retour HelloAsso 2 (cotisation) pour un soutien :
     * - Paiement de 10€ créé avec le commentaire attendu
     * - Redirection vers step 11
     */
    public function test_soutien_juillet_retour_helloasso2_cree_cotisation_10_euros(): void
    {
        $adherent = Adherent::factory()->create(['mail' => 'jean@test.fr']);
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

        // Paiement de 10€ avec le bon commentaire
        $this->assertDatabaseHas('paiements', [
            'id_adherent' => $adherent->id,
            'montant'     => 10.0,
            'source'      => 'HelloAsso',
            'commentaire' => 'Cotisation annuelle via HelloAsso',
        ]);

        // Un seul paiement, pas de doublon
        $this->assertSame(
            1,
            Paiement::where('id_adherent', $adherent->id)->count(),
            'Exactement 1 paiement de cotisation doit être créé.'
        );
    }

    /**
     * Flux complet soutien + paiement interne en juillet :
     * l'inscription doit être "en_attente" (pas pre_inscrit car soutien ≠ atelier)
     * et le montant = 10€ (cotisation seule, pas de tarif d'activité).
     */
    public function test_soutien_juillet_paiement_interne_inscription_en_attente_10_euros(): void
    {
        $token = 'tok_soutien_interne';

        $sessionDepart = $this->sessionDeBase([
            'type_activite'           => 'soutien',
            'activites_selectionnees' => [],
            '_last_completed'         => 9,
        ]);

        // Valider step 10 en interne
        $responseStep10 = $this->withSession([$this->sessionKey($token) => $sessionDepart])
            ->post(route('adhesion.next', ['token' => $token]), [
                'current_step'  => 10,
                'mode_paiement' => 'interne',
            ]);

        $responseStep10->assertRedirect(
            route('adhesion.show', ['token' => $token, 'step' => 11])
        );

        // Afficher step 11 → crée l'adhérent
        $sessionApresStep10 = $responseStep10->getSession()->get($this->sessionKey($token));

        $this->withSession([$this->sessionKey($token) => $sessionApresStep10])
            ->get(route('adhesion.show', ['token' => $token, 'step' => 11]))
            ->assertStatus(200);

        $adherent = Adherent::where('mail', 'jean@test.fr')->first();
        $this->assertNotNull($adherent);

        $this->assertDatabaseHas('inscriptions', [
            'id_adherent'   => $adherent->id,
            'a_paye'        => 'en_attente',   // ← soutien n'est pas une pré-inscription
            'montant'       => 10.0,            // ← uniquement la cotisation
            'type_adhesion' => 'soutien',
        ]);
    }

    // =========================================================================
    // BLOC 4 – HORS SAISON (test de non-régression)
    // =========================================================================

    /**
     * En octobre, un atelier doit être facturé au plein tarif (200€),
     * jamais à 50€.
     */
    public function test_hors_saison_octobre_facture_plein_tarif_pas_acompte(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 15, 12, 0, 0));

        $activite = $this->creerAtelier('Poterie', 200);
        $token    = 'tok_octobre_plein_tarif';

        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(
                    20000,            // 200€ = 20 000 centimes ← plein tarif
                    \Mockery::any(),
                    $token,
                    \Mockery::any(),
                    \Mockery::any()
                )
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
            'a_paye'      => 'en_attente',   // ← pas de pré-inscription en octobre
            'montant'     => 210,             // 200€ activité + 10€ cotisation
        ]);
    }

    /**
     * En février, un atelier doit appliquer la réduction de 50€ (tarif proraté)
     * et NE PAS être traité comme une pré-inscription.
     */
    public function test_hors_saison_fevrier_applique_reduction_50_euros(): void
    {
        Carbon::setTestNow(Carbon::create(2027, 2, 10, 12, 0, 0));

        $activite = $this->creerAtelier('Poterie', 200);
        $token    = 'tok_fevrier_reduc';

        // Tarif attendu en février : 200 - 50 = 150€
        $this->mock(HelloAssoService::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(15000, \Mockery::any(), $token, \Mockery::any(), \Mockery::any())
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
            'a_paye'      => 'en_attente',
            'montant'     => 160, // 150€ activité réduite + 10€ cotisation
        ]);
    }
}
