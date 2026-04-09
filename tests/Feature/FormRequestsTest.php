<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Adherent;
use App\Models\Activite;
use App\Models\Ressourcerie;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FormRequestsTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // CompleteInscriptionRequest
    // -------------------------------------------------------------------------

    public function test_inscription_echoue_si_mot_de_passe_sans_chiffre()
    {
        User::factory()->create([
            'firstname'        => 'Test',
            'invitation_token' => 'test-token-chiffre',
            'is_registered'    => false,
        ]);

        $response = $this->post(route('inscription.complete', 'test-token-chiffre'), [
            'password'              => 'Password!',
            'password_confirmation' => 'Password!',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_inscription_echoue_si_mot_de_passe_sans_symbole()
    {
        User::factory()->create([
            'firstname'        => 'Test',
            'invitation_token' => 'test-token-symbole',
            'is_registered'    => false,
        ]);

        $response = $this->post(route('inscription.complete', 'test-token-symbole'), [
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_inscription_echoue_si_confirmation_incorrecte()
    {
        User::factory()->create([
            'firstname'        => 'Test',
            'invitation_token' => 'test-token-confirm',
            'is_registered'    => false,
        ]);

        $response = $this->post(route('inscription.complete', 'test-token-confirm'), [
            'password'              => 'Password1!',
            'password_confirmation' => 'DifferentPass1!',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_inscription_reussit_avec_mot_de_passe_valide()
    {
        User::factory()->create([
            'firstname'        => 'Test',
            'invitation_token' => 'test-token-valide',
            'is_registered'    => false,
        ]);

        $response = $this->post(route('inscription.complete', 'test-token-valide'), [
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    // -------------------------------------------------------------------------
    // StoreActiviteRequest
    // -------------------------------------------------------------------------

    public function test_store_activite_echoue_sans_champs_requis()
    {
        $admin = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('activites.store'), []);

        $response->assertSessionHasErrors(['type', 'nom']);
    }

    public function test_store_activite_echoue_avec_type_invalide()
    {
        $admin = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('activites.store'), [
            'type' => 'cours',
            'nom'  => 'Test activité',
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_store_activite_reussit_avec_donnees_valides()
    {
        $admin = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('activites.store'), [
            'type'           => 'activite',
            'nom'            => 'Test activité valide',
            'tarif'          => '',
            'adresse'        => '',
            'ville'          => '',
            'dossier_action' => 'none',
        ]);

        // On vérifie que la validation passe (pas d'erreurs de validation)
        $response->assertSessionHasNoErrors();
    }

    // -------------------------------------------------------------------------
    // UpdateActiviteRequest
    // -------------------------------------------------------------------------

    public function test_update_activite_echoue_sans_champs_requis()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $activite = Activite::first();

        if (!$activite) {
            $this->markTestSkipped('Aucune activité en base de données.');
        }

        $response = $this->actingAs($admin)->put(route('activites.update', $activite), []);

        $response->assertSessionHasErrors(['type', 'nom']);
    }

    // -------------------------------------------------------------------------
    // AbandonnerAdherentRequest
    // -------------------------------------------------------------------------

    public function test_abandonner_echoue_sans_motif()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $activite = Activite::whereHas('adherents')->first();
        $adherent = $activite?->adherents()->first();

        if (!$activite || !$adherent) {
            $this->markTestSkipped('Aucune activité avec adhérent trouvée.');
        }

        $response = $this->actingAs($admin)
            ->post(route('activites.abandonner', [$activite, $adherent]), []);

        $response->assertSessionHasErrors(['motif_sortie']);
    }

    // -------------------------------------------------------------------------
    // CommentaireAdherentRequest
    // -------------------------------------------------------------------------

    public function test_commentaire_echoue_si_vide()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent en base de données.');
        }

        $response = $this->actingAs($admin)
            ->post(route('adherents.commentaire', $adherent), ['commentaire' => '']);

        $response->assertSessionHasErrors(['commentaire']);
    }

    public function test_commentaire_echoue_si_trop_long()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent en base de données.');
        }

        $response = $this->actingAs($admin)
            ->post(route('adherents.commentaire', $adherent), [
                'commentaire' => str_repeat('a', 2001),
            ]);

        $response->assertSessionHasErrors(['commentaire']);
    }

    public function test_commentaire_reussit_avec_texte_valide()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent en base de données.');
        }

        $response = $this->actingAs($admin)
            ->post(route('adherents.commentaire', $adherent), [
                'commentaire' => 'Ceci est un commentaire de test.',
            ]);

        $response->assertSessionHasNoErrors();
    }

    // -------------------------------------------------------------------------
    // AjouterVersementRequest
    // -------------------------------------------------------------------------

    public function test_versement_echoue_sans_montant()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent en base de données.');
        }

        $response = $this->actingAs($admin)
            ->post(route('adherents.versement', $adherent), []);

        $response->assertSessionHasErrors(['montant_versement']);
    }

    public function test_versement_echoue_avec_montant_zero()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent en base de données.');
        }

        $response = $this->actingAs($admin)
            ->post(route('adherents.versement', $adherent), [
                'montant_versement' => 0,
            ]);

        $response->assertSessionHasErrors(['montant_versement']);
    }

    public function test_versement_echoue_avec_montant_non_numerique()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent en base de données.');
        }

        $response = $this->actingAs($admin)
            ->post(route('adherents.versement', $adherent), [
                'montant_versement' => 'vingt euros',
            ]);

        $response->assertSessionHasErrors(['montant_versement']);
    }

    // -------------------------------------------------------------------------
    // UpdateFicheAdherentRequest
    // -------------------------------------------------------------------------

    public function test_update_fiche_echoue_sans_prenom_ni_nom()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent en base de données.');
        }

        $response = $this->actingAs($admin)
            ->put(route('adherents.update-fiche', $adherent), []);

        $response->assertSessionHasErrors(['prenom', 'nom']);
    }

    public function test_update_fiche_echoue_avec_email_invalide()
    {
        $admin    = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $adherent = Adherent::first();

        if (!$adherent) {
            $this->markTestSkipped('Aucun adhérent en base de données.');
        }

        $response = $this->actingAs($admin)
            ->put(route('adherents.update-fiche', $adherent), [
                'prenom' => 'Jean',
                'nom'    => 'Dupont',
                'mail'   => 'pas-un-email',
            ]);

        $response->assertSessionHasErrors(['mail']);
    }

    // -------------------------------------------------------------------------
    // UpdateProfileRequest
    // -------------------------------------------------------------------------

    public function test_profil_echoue_sans_email()
    {
        $user = User::factory()->create(['firstname' => 'Test', 'role' => 'admin']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'firstname' => 'Jean',
            'name'      => 'Dupont',
            'email'     => '',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_profil_mot_de_passe_echoue_sans_chiffre()
    {
        $user = User::factory()->create(['firstname' => 'Test', 'role' => 'admin']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'firstname'             => 'Jean',
            'name'                  => 'Dupont',
            'email'                 => $user->email,
            'password'              => 'Password!',
            'password_confirmation' => 'Password!',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_profil_mot_de_passe_echoue_sans_symbole()
    {
        $user = User::factory()->create(['firstname' => 'Test', 'role' => 'admin']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'firstname'             => 'Jean',
            'name'                  => 'Dupont',
            'email'                 => $user->email,
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_profil_reussit_sans_changement_de_mot_de_passe()
    {
        $user = User::factory()->create(['firstname' => 'Test', 'role' => 'admin']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'firstname' => 'Jean',
            'name'      => 'Dupont',
            'email'     => $user->email,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
    }

    // -------------------------------------------------------------------------
    // StoreDossierRequest
    // -------------------------------------------------------------------------

    public function test_store_dossier_echoue_sans_nom()
    {
        $admin = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('dossiers.store'), []);

        $response->assertSessionHasErrors(['nom']);
    }

    public function test_store_dossier_reussit_avec_nom_valide()
    {
        $admin = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('dossiers.store'), [
            'nom' => 'Dossier de test',
        ]);

        $response->assertSessionHasNoErrors();
    }

    // -------------------------------------------------------------------------
    // StoreRessourcerieRequest
    // -------------------------------------------------------------------------

    public function test_store_ressourcerie_echoue_sans_champs_requis()
    {
        $admin = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('ressourcerie.store'), []);

        $response->assertSessionHasErrors(['nom', 'type_tarif']);
    }

    public function test_store_ressourcerie_echoue_avec_type_tarif_invalide()
    {
        $admin = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('ressourcerie.store'), [
            'nom'        => 'Marteau',
            'type_tarif' => 'tarif_inexistant',
        ]);

        $response->assertSessionHasErrors(['type_tarif']);
    }

    public function test_store_ressourcerie_reussit_avec_donnees_valides()
    {
        $admin = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('ressourcerie.store'), [
            'nom'        => 'Perceuse de test',
            'type_tarif' => 'tarif_particulier',
        ]);

        // On vérifie que la validation passe (pas d'erreurs de validation)
        $response->assertSessionHasNoErrors();
    }

    // -------------------------------------------------------------------------
    // UpdateRessourcerieRequest
    // -------------------------------------------------------------------------

    public function test_update_ressourcerie_echoue_sans_champs_requis()
    {
        $admin       = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $ressourcerie = Ressourcerie::first();

        if (!$ressourcerie) {
            $this->markTestSkipped('Aucune ressource en base de données.');
        }

        $response = $this->actingAs($admin)
            ->put(route('ressourcerie.update', $ressourcerie), []);

        $response->assertSessionHasErrors(['nom', 'type_tarif']);
    }

    public function test_update_ressourcerie_reussit_avec_donnees_valides()
    {
        $admin       = User::factory()->create(['firstname' => 'Admin', 'role' => 'admin']);
        $ressourcerie = Ressourcerie::first();

        if (!$ressourcerie) {
            $this->markTestSkipped('Aucune ressource en base de données.');
        }

        $response = $this->actingAs($admin)
            ->put(route('ressourcerie.update', $ressourcerie), [
                'nom'        => 'Ressource modifiée',
                'type_tarif' => 'tarif_structure',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('ressourcerie.index'));
    }
}
