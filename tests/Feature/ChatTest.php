<?php

namespace Tests\Feature;

use App\Models\Artesano;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_puede_iniciar_chat_con_artesano(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $artesanoUser = User::factory()->create(['rol' => 'artesano']);

        $artesano = Artesano::create([
            'user_id' => $artesanoUser->id,
            'nombre' => 'Don Edwin',
            'apellido' => 'Quispe',
            'direccion_arte' => 'San Blas, Cusco',
            'telefono' => '+51 984 000 111',
            'historia_taller' => 'Taller de tejido andino.',
        ]);

        $response = $this->actingAs($cliente)->post('/chat/iniciar', [
            'artesano_id' => $artesano->id,
            'asunto' => 'Consulta sobre poncho',
            'mensaje' => 'Hola maestro, ¿hace envíos?',
        ]);

        $this->assertDatabaseHas('conversaciones', [
            'cliente_id' => $cliente->id,
            'artesano_id' => $artesano->id,
            'asunto' => 'Consulta sobre poncho',
        ]);

        $conversacion = Conversacion::first();

        $this->assertDatabaseHas('mensajes', [
            'conversacion_id' => $conversacion->id,
            'remitente_id' => $cliente->id,
            'mensaje' => 'Hola maestro, ¿hace envíos?',
        ]);

        $response->assertRedirect(route('chat.show', $conversacion->id));
    }

    public function test_artesano_puede_responder_y_se_actualiza_resumen_hub(): void
    {
        $cliente = User::factory()->create([
            'rol' => 'cliente',
            'nombre' => 'Sophie',
            'apellido' => 'Laurent',
            'name' => 'Sophie Laurent',
        ]);
        $artesanoUser = User::factory()->create([
            'rol' => 'artesano',
            'nombre' => 'Edwin',
            'apellido' => 'Quispe',
            'name' => 'Edwin Quispe',
        ]);

        $artesano = Artesano::create([
            'user_id' => $artesanoUser->id,
            'nombre' => 'Edwin',
            'apellido' => 'Quispe',
            'direccion_arte' => 'San Blas',
            'telefono' => '+51 984 123 456',
            'historia_taller' => 'Taller de platería y textiles.',
        ]);

        $conversacion = Conversacion::create([
            'cliente_id' => $cliente->id,
            'artesano_id' => $artesano->id,
            'asunto' => 'Consulta de tallas',
            'estado' => 'abierto',
        ]);

        // Mensaje del cliente
        Mensaje::create([
            'conversacion_id' => $conversacion->id,
            'remitente_id' => $cliente->id,
            'mensaje' => '¿Tiene talla L?',
        ]);

        // Artesano responde mediante endpoint JSON
        $response = $this->actingAs($artesanoUser)->postJson("/chat/{$conversacion->id}/mensaje", [
            'mensaje' => 'Sí tenemos disponible en talla L.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('mensajes', [
            'conversacion_id' => $conversacion->id,
            'remitente_id' => $artesanoUser->id,
            'mensaje' => 'Sí tenemos disponible en talla L.',
        ]);

        // Verificar que el resumen para el HUB se actualizó automáticamente
        $conversacion->refresh();
        $this->assertNotNull($conversacion->resumen_admin);
        $this->assertStringContainsString('Sophie', $conversacion->resumen_admin);
        $this->assertStringContainsString('talla L', $conversacion->resumen_admin);
    }

    public function test_admin_hub_puede_ver_resumenes_de_los_chats(): void
    {
        $admin = User::factory()->create(['rol' => 'admin_hub']);
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $artesanoUser = User::factory()->create(['rol' => 'artesano']);

        $artesano = Artesano::create([
            'user_id' => $artesanoUser->id,
            'nombre' => 'Marco',
            'apellido' => 'Baca',
            'direccion_arte' => 'Pisac',
            'telefono' => '+51 984 777 888',
            'historia_taller' => 'Orfebrería inca.',
        ]);

        $conversacion = Conversacion::create([
            'cliente_id' => $cliente->id,
            'artesano_id' => $artesano->id,
            'asunto' => 'Consulta de platería',
            'resumen_admin' => 'Resumen de prueba para el HUB.',
            'estado' => 'abierto',
        ]);

        $response = $this->actingAs($admin)->get('/admin-hub/chats-resumen');

        $response->assertStatus(200);
        $response->assertSee('Resúmenes de Chats', false);
        $response->assertSee('Resumen de prueba para el HUB.');
    }
}
