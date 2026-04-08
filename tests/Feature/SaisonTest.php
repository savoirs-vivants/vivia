<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Saison;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SaisonTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }


    public function test_la_saison_courante_est_bien_celle_active_en_bdd()
    {
        $saison = Saison::where('est_active', true)->first();

        if (!$saison) {
            $this->markTestSkipped('Aucune saison active trouvée dans la base de test.');
        }

        $this->assertEquals($saison->nom, Saison::current());
    }

    public function test_on_peut_recuperer_une_saison_specifique_par_son_nom()
    {
        $saisonAleatoire = Saison::first();

        $trouvee = Saison::where('nom', $saisonAleatoire->nom)->first();

        $this->assertEquals($saisonAleatoire->id, $trouvee->id);
    }

    public function test_current_retourne_2026_2027_le_1er_septembre()
    {
        Saison::query()->update(['est_active' => false]);

        Carbon::setTestNow(Carbon::create(2026, 9, 1, 0, 0, 0));

        $this->assertEquals('2026-2027', Saison::current());
    }

    public function test_current_retourne_2025_2026_la_veille_du_basculement()
    {
        Saison::query()->update(['est_active' => false]);

        Carbon::setTestNow(Carbon::create(2026, 8, 31, 23, 59, 59));

        $this->assertEquals('2025-2026', Saison::current());
    }

    public function test_current_retourne_la_bonne_saison_en_milieu_dannee()
    {
        Saison::query()->update(['est_active' => false]);

        Carbon::setTestNow(Carbon::create(2026, 3, 15));
        $this->assertEquals('2025-2026', Saison::current());

        Carbon::setTestNow(Carbon::create(2026, 11, 1));
        $this->assertEquals('2026-2027', Saison::current());
    }

    public function test_current_prioritise_la_bdd_sur_le_calcul_par_date()
    {
        $saisonActive = Saison::where('est_active', true)->first();

        if (!$saisonActive) {
            $this->markTestSkipped('Aucune saison active en base pour ce test.');
        }

        Carbon::setTestNow(Carbon::create(2099, 9, 1));

        $this->assertEquals($saisonActive->nom, Saison::current());
        $this->assertNotEquals('2099-2100', Saison::current());
    }

    public function test_syncActive_desactive_les_anciennes_saisons()
    {
        Saison::query()->update(['est_active' => true]);
        $this->assertGreaterThan(1, Saison::where('est_active', true)->count());

        $year = now()->month >= 9 ? now()->year : now()->year - 1;
        $nomAttendu = $year . '-' . ($year + 1);

        if (!Saison::where('nom', $nomAttendu)->exists()) {
            $this->markTestSkipped("La saison $nomAttendu n'existe pas en base de test, insertion impossible (pas d'AUTO_INCREMENT).");
        }

        Saison::syncActive();

        $this->assertEquals(1, Saison::where('est_active', true)->count());
        $this->assertEquals($nomAttendu, Saison::where('est_active', true)->first()->nom);
    }

    public function test_une_seule_saison_est_active_apres_syncActive()
    {
        $year = now()->month >= 9 ? now()->year : now()->year - 1;
        $nomAttendu = $year . '-' . ($year + 1);

        if (!Saison::where('nom', $nomAttendu)->exists()) {
            $this->markTestSkipped("La saison $nomAttendu n'existe pas en base de test.");
        }

        Saison::syncActive();

        $this->assertEquals(1, Saison::where('est_active', true)->count());
    }
}
