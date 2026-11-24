<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_tipo_cambio_retorna_tasa_valida(): void
    {
        $response = $this->getJson('/api/tipo-cambio');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure(['status', 'rate_usd_to_pen', 'active_currency', 'reference']);

        $rate = (float) $response->json('rate_usd_to_pen');
        $this->assertGreaterThan(0, $rate);
    }

    public function test_cambio_de_moneda_persiste_en_sesion(): void
    {
        $response = $this->postJson('/cambiar-moneda', [
            'moneda' => 'USD',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('active_currency', 'USD');

        $this->assertEquals('USD', session('moneda_activa'));
    }

    public function test_vistas_incluyen_selector_de_moneda_y_tipo_de_cambio(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('currency-toggle-group', false);
        $response->assertSee('S/');
        $response->assertSee('$');
        $response->assertSee('T.C. $1 ≈ S/');
    }
}
