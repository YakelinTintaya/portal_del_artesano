<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityVulnerabilitiesPortswiggerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Prueba que la página del laboratorio de seguridad carga correctamente con los 10 labs.
     */
    public function test_laboratorio_seguridad_carga_con_los_10_labs(): void
    {
        $response = $this->get('/seguridad/labs');

        $response->assertStatus(200);
        $response->assertSee('Laboratorio de Vulnerabilidades Web');
        $response->assertSee('Lab 1: SQL injection vulnerability in WHERE clause');
        $response->assertSee('Lab 2: SQL injection vulnerability allowing login bypass');
        $response->assertSee('Lab 3: Reflected XSS into HTML context with nothing encoded');
        $response->assertSee('Lab 4: Stored XSS into HTML context with nothing encoded');
        $response->assertSee('Lab 5: DOM XSS in document.write sink using source location.search');
        $response->assertSee('Lab 6: Unprotected admin functionality');
        $response->assertSee('Lab 7: Unprotected admin functionality with unpredictable URL');
        $response->assertSee('Lab 8: User ID controlled by request parameter');
        $response->assertSee('Lab 9: Exploiting LLM APIs with excessive agency');
        $response->assertSee('Lab 10: Username enumeration via different responses');
    }

    /**
     * Prueba que el endpoint AJAX de prueba del Lab 1 (SQLi WHERE) neutraliza la inyección.
     */
    public function test_simulacion_lab_1_sqli_neutralizado_por_eloquent(): void
    {
        $response = $this->postJson('/seguridad/labs/test/1', [
            'payload' => "' OR 1=1--",
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'resultado' => [
                'bloqueado' => true,
                'estado_seguridad' => 'PROTEGIDO (Vulnerabilidad Neutralizada)',
            ],
        ]);
    }

    /**
     * Prueba que el Lab 3 (Reflected XSS) aplica sanitización Blade / e().
     */
    public function test_simulacion_lab_3_reflected_xss_escapado(): void
    {
        $response = $this->postJson('/seguridad/labs/test/3', [
            'payload' => '<script>alert(1)</script>',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'resultado' => [
                'bloqueado' => true,
            ],
        ]);
    }

    /**
     * Prueba que el Lab 6 (Control de Acceso Admin) bloquea a usuarios no autenticados o clientes.
     */
    public function test_simulacion_lab_6_acceso_no_autorizado_bloqueado(): void
    {
        $response = $this->postJson('/seguridad/labs/test/6');

        $response->assertStatus(200);
        $response->assertJson([
            'resultado' => [
                'bloqueado' => true,
            ],
        ]);
    }

    /**
     * Prueba que el Lab 10 (Username enumeration) emite respuesta genérica y no diferencia correos.
     */
    public function test_simulacion_lab_10_username_enumeration_prevenido(): void
    {
        $response = $this->postJson('/seguridad/labs/test/10', [
            'payload' => 'admin@portalartesano.pe',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'resultado' => [
                'bloqueado' => true,
                'estado_seguridad' => 'PROTEGIDO (Respuesta Uniforme y Genérica)',
            ],
        ]);
    }
}
