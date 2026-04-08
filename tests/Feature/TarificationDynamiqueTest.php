<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Activite;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TarificationDynamiqueTest extends TestCase
{
    use DatabaseTransactions;

    protected $activite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activite = Activite::where('tarif', '>', 0)->first();
    }

    public function test_le_prix_baisse_bien_en_cours_d_annee()
    {
        $tarifDeBase = (float) str_replace(['€', ','], ['', '.'], $this->activite->tarif);

        Carbon::setTestNow(Carbon::parse('2024-10-15'));
        $controleur = new \App\Http\Controllers\AdherentFormulaireController();
        $prixOctobre = $this->invokePrivateMethod($controleur, 'calculerMontantActivites', [[$this->activite->id]]);

        $this->assertEquals($tarifDeBase, $prixOctobre);

        Carbon::setTestNow(Carbon::parse('2025-04-15'));
        $prixAvril = $this->invokePrivateMethod($controleur, 'calculerMontantActivites', [[$this->activite->id]]);

        $this->assertLessThan($tarifDeBase, $prixAvril);
    }

    private function invokePrivateMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
