<?php

namespace Tests\Feature;

use App\Models\Conversacion;
use App\Models\Producto;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalArtesanoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_inicio_responde_con_exito(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Portal del Artesano');
        $response->assertSee('Comercio Justo Directo');
    }

    public function test_catalogo_muestra_obras_y_filtros(): void
    {
        $response = $this->get('/catalogo');
        $response->assertStatus(200);
        $response->assertSee('Piezas de Arte Tradicional Cusqueño');
        $response->assertSee('Platería');
        $response->assertSee('Textiles');
    }

    public function test_catalogo_soporta_busqueda_json(): void
    {
        $response = $this->getJson('/catalogo?json=1&q=Chakana');
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure(['data', 'count', 'pagination']);
    }

    public function test_detalle_producto_muestra_trazabilidad_y_oculta_reglas_internas(): void
    {
        $producto = Producto::where('aprobado_hub', true)->first();

        $response = $this->get('/catalogo/'.$producto->id);
        $response->assertStatus(200);
        $response->assertSee($producto->nombre_prod);
        $response->assertSee($producto->certificado_autenticidad_codigo);
        // Reglas internas entre empresa y proveedor no deben exponerse al cliente final
        $response->assertDontSee('85%');
        $response->assertDontSee('Transparencia de Comercio Justo');
    }

    public function test_carrito_agrega_producto_y_calcula_subtotales(): void
    {
        $producto = Producto::where('aprobado_hub', true)->first();

        $response = $this->postJson('/carrito/agregar', [
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertEquals(2, session('carrito')[$producto->id]['cantidad']);
    }

    public function test_carrito_valida_stock_maximo_disponible(): void
    {
        $producto = Producto::where('aprobado_hub', true)->first();
        $stockExcedido = $producto->stock + 100;

        $response = $this->postJson('/carrito/agregar', [
            'producto_id' => $producto->id,
            'cantidad' => $stockExcedido,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_carrito_actualiza_cantidad_incrementar_y_decrementar(): void
    {
        $producto = Producto::where('aprobado_hub', true)->first();

        // 1. Agregar producto al carrito
        $this->postJson('/carrito/agregar', [
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);

        // 2. Incrementar (+ 1)
        $incRes = $this->postJson('/carrito/actualizar', [
            'producto_id' => $producto->id,
            'accion' => 'incrementar',
        ]);
        $incRes->assertStatus(200);
        $this->assertEquals(3, session('carrito')[$producto->id]['cantidad']);

        // 3. Decrementar (- 1)
        $decRes = $this->postJson('/carrito/actualizar', [
            'producto_id' => $producto->id,
            'accion' => 'decrementar',
        ]);
        $decRes->assertStatus(200);
        $this->assertEquals(2, session('carrito')[$producto->id]['cantidad']);
    }

    public function test_carrito_elimina_producto_con_boton_x(): void
    {
        $producto = Producto::where('aprobado_hub', true)->first();

        $this->postJson('/carrito/agregar', [
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ]);
        $this->assertArrayHasKey($producto->id, session('carrito'));

        $removeRes = $this->postJson('/carrito/eliminar', [
            'producto_id' => $producto->id,
        ]);
        $removeRes->assertStatus(200);
        $this->assertArrayNotHasKey($producto->id, session('carrito', []));
    }

    public function test_carrito_vista_contiene_botones_y_formularios(): void
    {
        $producto = Producto::where('aprobado_hub', true)->first();

        $this->postJson('/carrito/agregar', [
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);

        $response = $this->get('/carrito');
        $response->assertStatus(200);
        $response->assertSee('cart-qty-btn');
        $response->assertSee('cart-remove-btn');
        $response->assertSee('action="'.route('carrito.update').'"', false);
        $response->assertSee('action="'.route('carrito.remove').'"', false);
    }

    public function test_vistas_contienen_elementos_de_anadir_al_carrito(): void
    {
        $producto = Producto::where('aprobado_hub', true)->first();

        // 1. Inicio contiene botones .btn-quick-add
        $inicio = $this->get('/');
        $inicio->assertStatus(200);
        $inicio->assertSee('btn-quick-add');

        // 2. Catálogo contiene botones .btn-quick-add
        $catalogo = $this->get('/catalogo');
        $catalogo->assertStatus(200);
        $catalogo->assertSee('btn-quick-add');

        // 3. Detalle contiene el formulario y botón
        $detalle = $this->get('/catalogo/'.$producto->id);
        $detalle->assertStatus(200);
        $detalle->assertSee('id="form-add-to-cart"', false);
        $detalle->assertSee('btn-quick-add');
    }

    public function test_checkout_requiere_autenticacion(): void
    {
        $response = $this->get('/checkout');
        $response->assertRedirect('/login');
    }

    public function test_checkout_procesa_pedido_con_transaccion_db(): void
    {
        $cliente = User::where('rol', 'cliente')->first();
        $producto = Producto::where('aprobado_hub', true)->where('stock', '>=', 2)->first();

        $stockInicial = $producto->stock;

        // Simular carrito en sesión
        session()->put('carrito', [
            $producto->id => [
                'id' => $producto->id,
                'nombre' => $producto->nombre_prod,
                'precio' => (float) $producto->precio,
                'cantidad' => 2,
            ],
        ]);

        $response = $this->actingAs($cliente)->post('/checkout', [
            'direccion_envio' => 'Calle Recoleta 120, Cusco',
            'pais_destino' => 'Perú',
            'tipo_envio' => 'Nacional',
            'telefono_contacto' => '+51 984 123 456',
            'metodo_pago' => 'Tarjeta de Crédito / Débito',
            'consentimiento_datos' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $cliente->id,
            'tipo_envio' => 'Nacional',
        ]);

        // Verificar descuento de stock
        $producto->refresh();
        $this->assertEquals($stockInicial - 2, $producto->stock);
    }

    public function test_checkout_procesa_pedido_con_envio_exclusivo_region_cusco(): void
    {
        $cliente = User::where('rol', 'cliente')->first();
        $producto = Producto::where('aprobado_hub', true)->where('stock', '>=', 1)->first();

        session()->put('carrito', [
            $producto->id => [
                'id' => $producto->id,
                'nombre' => $producto->nombre_prod,
                'precio' => (float) $producto->precio,
                'cantidad' => 1,
            ],
        ]);

        $response = $this->actingAs($cliente)->post('/checkout', [
            'direccion_envio' => 'Calle Saphy 340, Centro Histórico, Cusco',
            'pais_destino' => 'Cusco (Ciudad / Urbano)',
            'tipo_envio' => 'Cusco Urbano',
            'telefono_contacto' => '+51 984 000 111',
            'metodo_pago' => 'Billetera Digital / Transferencia',
            'consentimiento_datos' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $cliente->id,
            'pais_destino' => 'Cusco (Ciudad / Urbano)',
            'tipo_envio' => 'Cusco Urbano',
            'costo_envio' => 10.00,
        ]);
    }

    public function test_checkout_procesa_pedido_con_recojo_en_tienda(): void
    {
        $cliente = User::where('rol', 'cliente')->first();
        $producto = Producto::where('aprobado_hub', true)->where('stock', '>=', 1)->first();

        session()->put('carrito', [
            $producto->id => [
                'id' => $producto->id,
                'nombre' => $producto->nombre_prod,
                'precio' => (float) $producto->precio,
                'cantidad' => 1,
            ],
        ]);

        $response = $this->actingAs($cliente)->post('/checkout', [
            'direccion_envio' => 'Recojo en Tienda: HUB Central Cusco (Calle Triunfo 392, Centro Histórico)',
            'pais_destino' => 'Cusco (Recojo en Tienda)',
            'tipo_envio' => 'Recojo en Tienda',
            'telefono_contacto' => '+51 984 000 222',
            'metodo_pago' => 'Pago en Efectivo (Contra entrega en Cusco)',
            'consentimiento_datos' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $cliente->id,
            'tipo_envio' => 'Recojo en Tienda',
            'costo_envio' => 0.00,
        ]);
    }

    public function test_checkout_vista_muestra_cobertura_exclusiva_cusco(): void
    {
        $cliente = User::where('rol', 'cliente')->first();
        $producto = Producto::where('aprobado_hub', true)->first();

        session()->put('carrito', [
            $producto->id => [
                'id' => $producto->id,
                'nombre' => $producto->nombre_prod,
                'precio' => (float) $producto->precio,
                'cantidad' => 1,
            ],
        ]);

        $response = $this->actingAs($cliente)->get('/checkout');
        $response->assertStatus(200);
        $response->assertSee('Región Cusco');
        $response->assertSee('Delivery a Domicilio');
        $response->assertSee('Recojo en Tienda');
        $response->assertSee('Cusco Urbano');
        $response->assertSee('Valle Sagrado y Provincias');
        $response->assertSee('Pago en Efectivo');
        $response->assertDontSee('Tarjeta Bancaria');
        $response->assertDontSee('Internacional Asegurado');
        $response->assertDontSee('Estados Unidos');
    }

    public function test_panel_hub_restringido_a_admin(): void
    {
        $cliente = User::where('rol', 'cliente')->first();
        $admin = User::where('rol', 'admin_hub')->first();

        // Cliente ordinario debe recibir 403
        $this->actingAs($cliente)->get('/admin-hub')->assertStatus(403);

        // Admin HUB debe acceder correctamente
        $this->actingAs($admin)->get('/admin-hub')->assertStatus(200);
    }

    public function test_artesano_puede_crear_producto_subiendo_archivo_de_imagen(): void
    {
        Storage::fake('public');

        $artesanoUser = User::where('rol', 'artesano')->first();
        $file = UploadedFile::fake()->image('manta_andina.jpg', 800, 600);

        $response = $this->actingAs($artesanoUser)->post('/artesano/productos', [
            'nombre_prod' => 'Manta Andina Ceremonial de Pisac',
            'descripcion_prod' => 'Tejida a mano en telar de cintura tradicional usando lana de alpaca teñida.',
            'categoria' => 'Textiles',
            'subtipo' => 'Decoración',
            'precio' => 240.00,
            'stock' => 3,
            'tamano' => '150 x 100 cm',
            'colores' => 'Tintes naturales',
            'materiales_origen' => 'Lana de alpaca 100% hilada a mano',
            'imagen_archivo' => $file,
        ]);

        $response->assertRedirect('/artesano/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('productos', [
            'nombre_prod' => 'Manta Andina Ceremonial de Pisac',
            'categoria' => 'Textiles',
        ]);

        $producto = Producto::where('nombre_prod', 'Manta Andina Ceremonial de Pisac')->first();
        $this->assertNotNull($producto);
        $this->assertStringContainsString('storage/productos/obra_', $producto->imagen_url);
    }

    public function test_artesano_puede_crear_producto_con_url_alternativa(): void
    {
        $artesanoUser = User::where('rol', 'artesano')->first();

        $response = $this->actingAs($artesanoUser)->post('/artesano/productos', [
            'nombre_prod' => 'Vasija Ceremonial Kero Cusqueño',
            'descripcion_prod' => 'Réplica en barro cocido con motivos incaicos polícromos de Sacsayhuamán.',
            'categoria' => 'Cerámica',
            'subtipo' => 'Decoración',
            'precio' => 110.00,
            'stock' => 5,
            'tamano' => '25 cm alto',
            'colores' => 'Ocre y negro',
            'materiales_origen' => 'Arcilla cusqueña y pigmentos minerales',
            'imagen_url' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=600&q=80',
        ]);

        $response->assertRedirect('/artesano/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('productos', [
            'nombre_prod' => 'Vasija Ceremonial Kero Cusqueño',
            'imagen_url' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=600&q=80',
        ]);
    }

    public function test_artesano_puede_crear_producto_con_recuadros_separados_de_dimensiones(): void
    {
        $artesanoUser = User::where('rol', 'artesano')->first();

        $response = $this->actingAs($artesanoUser)->post('/artesano/productos', [
            'nombre_prod' => 'Camino de Mesa Ollantaytambo',
            'descripcion_prod' => 'Tejido artesanal tradicional con tintes vegetales y bordados cusqueños.',
            'categoria' => 'Textiles',
            'subtipo' => 'Decoración',
            'precio' => 175.00,
            'stock' => 4,
            'dim_largo' => '180',
            'dim_ancho' => '30',
            'dim_alto' => '',
            'dim_unidad' => 'cm',
            'colores' => 'Nogal y añil',
            'materiales_origen' => 'Lana de oveja y alpaca',
            'imagen_url' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=600&q=80',
        ]);

        $response->assertRedirect('/artesano/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('productos', [
            'nombre_prod' => 'Camino de Mesa Ollantaytambo',
            'tamano' => '180 x 30 cm',
        ]);
    }

    public function test_usuario_puede_enviar_mensaje_en_chat_via_json(): void
    {
        $conversacion = Conversacion::first();
        $cliente = $conversacion->cliente;

        $response = $this->actingAs($cliente)->postJson("/chat/{$conversacion->id}/mensaje", [
            'mensaje' => 'Hola maestro, ¿cuál es el tiempo de envío a Lima?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('mensaje.es_mio', true);

        $this->assertDatabaseHas('mensajes', [
            'conversacion_id' => $conversacion->id,
            'remitente_id' => $cliente->id,
            'mensaje' => 'Hola maestro, ¿cuál es el tiempo de envío a Lima?',
        ]);
    }

    public function test_usuario_puede_enviar_mensaje_en_chat_via_formulario_html(): void
    {
        $conversacion = Conversacion::first();
        $cliente = $conversacion->cliente;

        $response = $this->actingAs($cliente)->post("/chat/{$conversacion->id}/mensaje", [
            'mensaje' => 'Mensaje enviado por fallback de formulario nativo',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('mensajes', [
            'conversacion_id' => $conversacion->id,
            'remitente_id' => $cliente->id,
            'mensaje' => 'Mensaje enviado por fallback de formulario nativo',
        ]);
    }

    public function test_artesano_puede_responder_en_su_conversacion(): void
    {
        $conversacion = Conversacion::first();
        $artesanoUser = $conversacion->artesano->user;

        $response = $this->actingAs($artesanoUser)->postJson("/chat/{$conversacion->id}/mensaje", [
            'mensaje' => '¡Hola! El envío toma de 2 a 3 días hábiles desde Cusco.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('mensajes', [
            'conversacion_id' => $conversacion->id,
            'remitente_id' => $artesanoUser->id,
            'mensaje' => '¡Hola! El envío toma de 2 a 3 días hábiles desde Cusco.',
        ]);
    }

    public function test_usuario_ajeno_recibe_403_al_intentar_enviar_mensaje(): void
    {
        $conversacion = Conversacion::first();
        $usuarioAjeno = User::factory()->create([
            'rol' => 'cliente',
        ]);

        $response = $this->actingAs($usuarioAjeno)->postJson("/chat/{$conversacion->id}/mensaje", [
            'mensaje' => 'Intento de intromisión',
        ]);

        $response->assertStatus(403);
    }
}
