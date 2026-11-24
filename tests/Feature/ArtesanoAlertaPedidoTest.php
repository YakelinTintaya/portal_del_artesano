<?php

namespace Tests\Feature;

use App\Models\Artesano;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtesanoAlertaPedidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitud_no_autenticada_a_verificar_pedidos_es_redirigida(): void
    {
        $response = $this->get('/artesano/api/verificar-pedidos');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_artesano_recibe_alerta_cuando_llega_un_nuevo_pedido_pendiente(): void
    {
        // 1. Crear usuario artesano y su perfil
        $userArtesano = User::factory()->create([
            'rol' => 'artesano',
            'nombre' => 'Don Edwin',
            'apellido' => 'Quispe',
            'name' => 'Don Edwin Quispe',
        ]);

        $artesano = Artesano::create([
            'user_id' => $userArtesano->id,
            'nombre' => 'Don Edwin',
            'apellido' => 'Quispe',
            'direccion_arte' => 'Barrio San Blas, Cusco',
            'telefono' => '+51 984 123 456',
            'historia_taller' => 'Maestro tejedor andino con más de 30 años de experiencia.',
            'estado_validacion' => true,
        ]);

        // 2. Crear producto del artesano
        $producto = Producto::create([
            'artesano_id' => $artesano->id,
            'nombre_prod' => 'Poncho Cusqueño Tradicional',
            'descripcion_prod' => 'Tejido a mano con lana de alpaca virgen en telar de cuatro estacas.',
            'categoria' => 'Textiles',
            'precio' => 280.00,
            'stock' => 5,
            'materiales_origen' => 'Lana de alpaca',
            'certificado_autenticidad_codigo' => 'CERT-TEST-001',
            'aprobado_hub' => true,
            'imagen_url' => 'https://example.com/poncho.jpg',
        ]);

        // 3. Crear cliente y orden pendiente
        $cliente = User::factory()->create([
            'rol' => 'cliente',
            'nombre' => 'Sophie',
            'apellido' => 'Laurent',
            'name' => 'Sophie Laurent',
        ]);

        $pedido = Pedido::create([
            'cliente_id' => $cliente->id,
            'cant_producto' => 1,
            'direccion_envio' => 'Paris, Francia',
            'pais_destino' => 'Francia',
            'tipo_envio' => 'Internacional con seguro',
            'envoltura_regalo' => true,
            'mensaje_regalo' => '¡Un regalo desde Cusco!',
            'subtotal' => 280.00,
            'comision_curaduria_hub' => 42.00,
            'costo_envio' => 85.00,
            'total' => 377.00,
            'estado_pedido' => 'pendiente',
            'codigo_seguimiento' => 'HUB-CUSCO-TEST123',
        ]);

        $pedido->productos()->attach($producto->id, [
            'cantidad' => 1,
            'precio_unitario_historico' => 280.00,
        ]);

        // 4. El artesano consulta el endpoint de verificación
        $response = $this->actingAs($userArtesano)->getJson('/artesano/api/verificar-pedidos?ultimo_id=0');

        $response->assertStatus(200);
        $response->assertJsonPath('hay_nuevos', true);
        $response->assertJsonPath('total_pendientes', 1);
        $response->assertJsonPath('pedidos.0.id', $pedido->id);
        $response->assertJsonPath('pedidos.0.cliente_nombre', 'Sophie Laurent');
        $response->assertJsonPath('pedidos.0.envoltura_regalo', true);
        $response->assertJsonPath('pedidos.0.mensaje_regalo', '¡Un regalo desde Cusco!');

        // 5. Si vuelve a consultar con el ID ya conocido, no hay nuevos
        $response2 = $this->actingAs($userArtesano)->getJson('/artesano/api/verificar-pedidos?ultimo_id='.$pedido->id);
        $response2->assertStatus(200);
        $response2->assertJsonPath('hay_nuevos', false);
    }
}
