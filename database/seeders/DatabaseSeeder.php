<?php

namespace Database\Seeders;

use App\Models\Artesano;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Administrador del HUB Logístico Cusco
        $admin = User::create([
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza Farfán',
            'name' => 'Carlos Mendoza Farfán',
            'correo' => 'admin@portalartesano.pe',
            'email' => 'admin@portalartesano.pe',
            'telefono' => '+51 984 112 233',
            'direccion' => 'HUB Logístico Regional Cusco, Av. El Sol 450, Wanchaq',
            'password' => Hash::make('secret123'),
            'rol' => 'admin_hub',
        ]);

        // 2. Cliente Turista / Comprador Internacional
        $cliente = User::create([
            'nombre' => 'Sophie',
            'apellido' => 'Laurent',
            'name' => 'Sophie Laurent',
            'correo' => 'turista@gmail.com',
            'email' => 'turista@gmail.com',
            'telefono' => '+33 6 12 34 56 78',
            'direccion' => '24 Rue de la Paix, Paris, Francia (Hospedada temporalmente en Cusco)',
            'password' => Hash::make('secret123'),
            'rol' => 'cliente',
        ]);

        // =========================================================================
        // ARTESANO 1: Don Edwin Quispe
        // Galería en Cusco (Calle Marqués): café de La Convención, chocolate de cacao chuncho y textiles.
        // =========================================================================
        $userEdwin = User::create([
            'nombre' => 'Edwin',
            'apellido' => 'Quispe Huallpa',
            'name' => 'Edwin Quispe Huallpa',
            'correo' => 'edwin@portalartesano.pe',
            'email' => 'edwin@portalartesano.pe',
            'telefono' => '+51 984 556 789',
            'direccion' => 'Galería Artesanal Marqués N° 238, Centro Histórico de Cusco',
            'password' => Hash::make('secret123'),
            'rol' => 'artesano',
        ]);

        $artesanoEdwin = Artesano::create([
            'user_id' => $userEdwin->id,
            'nombre' => 'Edwin',
            'apellido' => 'Quispe Huallpa',
            'direccion_arte' => 'Galería Marqués & Valle de La Convención, Cusco',
            'telefono' => '+51 984 556 789',
            'historia_taller' => 'Con más de 28 años de trayectoria en el Centro Histórico de Cusco, Don Edwin Quispe lidera una red cooperativa que une a caficultores agroforestales de La Convención (Quillabamba) con maestros chocolateros y tejedores tradicionales. Su taller promueve el cultivo de cacao chuncho nativo orgánico y la preservación de telares de pedal andinos.',
            'foto_perfil' => 'https://picsum.photos/seed/edwin-artesano/400/400',
            'estado_validacion' => true, // Validado por el HUB
        ]);

        // =========================================================================
        // ARTESANO 2: Marco Polo Baca
        // San Blas: joyería de plata cusqueña 950 y réplicas de arte colonial/andino.
        // =========================================================================
        $userMarco = User::create([
            'nombre' => 'Marco Polo',
            'apellido' => 'Baca Palomino',
            'name' => 'Marco Polo Baca Palomino',
            'correo' => 'marcopolo@portalartesano.pe',
            'email' => 'marcopolo@portalartesano.pe',
            'telefono' => '+51 984 334 112',
            'direccion' => 'Barrio Tradicional de San Blas, Cuesta de San Blas 512, Cusco',
            'password' => Hash::make('secret123'),
            'rol' => 'artesano',
        ]);

        $artesanoMarco = Artesano::create([
            'user_id' => $userMarco->id,
            'nombre' => 'Marco Polo',
            'apellido' => 'Baca Palomino',
            'direccion_arte' => 'Barrio de San Blas, Cusco',
            'telefono' => '+51 984 334 112',
            'historia_taller' => 'Heredero de cuatro generaciones de maestros orfebres e imagineros en el emblemático barrio de San Blas. Marco Polo Baca domina la técnica ancestral del cincelado, repujado y filigrana en plata 950, inspirándose en los retablos del barroco andino cusqueño y la iconografía solar incaica.',
            'foto_perfil' => 'https://picsum.photos/seed/marcopolo-artesano/400/400',
            'estado_validacion' => true, // Validado por el HUB
        ]);

        // =========================================================================
        // ARTESANO 3: Sra. María Huamán Condori
        // Asociación Textil en San Pedro / Chinchero: tejidos tradicionales en lana de alpaca virgen con tintes naturales.
        // =========================================================================
        $userMaria = User::create([
            'nombre' => 'María',
            'apellido' => 'Huamán Condori',
            'name' => 'María Huamán Condori',
            'correo' => 'maria@portalartesano.pe',
            'email' => 'maria@portalartesano.pe',
            'telefono' => '+51 984 778 990',
            'direccion' => 'Asociación de Tejedoras Andinas Munay Away, Chinchero y Mercado San Pedro, Cusco',
            'password' => Hash::make('secret123'),
            'rol' => 'artesano',
        ]);

        $artesanoMaria = Artesano::create([
            'user_id' => $userMaria->id,
            'nombre' => 'María',
            'apellido' => 'Huamán Condori',
            'direccion_arte' => 'Chinchero & Mercado San Pedro, Cusco',
            'telefono' => '+51 984 778 990',
            'historia_taller' => 'Maestra tejedora quechua originaria de Chinchero y portavoz de la Asociación Munay Away en el Mercado Central de San Pedro. Dedicada al rescate de la iconografía "pallay" con lana 100% alpaca virgen hilada con rueca (pushka) y teñida exclusivamente con cochinilla, qolle, chilca y sales volcánicas andinas.',
            'foto_perfil' => 'https://picsum.photos/seed/maria-artesana/400/400',
            'estado_validacion' => true, // Validado por el HUB
        ]);

        // =========================================================================
        // PRODUCTOS DE DON EDWIN QUISPE
        // =========================================================================
        $p1 = Producto::create([
            'artesano_id' => $artesanoEdwin->id,
            'nombre_prod' => 'Café Gourmet Geisha de Altura La Convención (250g)',
            'descripcion_prod' => 'Café de especialidad cosechado a mano a más de 1,850 m.s.n.m. en el Valle de La Convención, tostado medio artesanal con notas a jazmín, miel de abeja andina y bergamota.',
            'categoria' => 'Decoración', // o Gourmet/Accesorios
            'subtipo' => 'Agroartesanal',
            'precio' => 45.00,
            'en_oferta' => true,
            'precio_oferta' => 36.00,
            'descuento_porcentaje' => 20,
            'etiqueta_promocion' => '20% OFF',
            'stock' => 35,
            'tamano' => '250 gramos',
            'colores' => 'Marrón tostado',
            'puntuacion' => 4.95,
            'materiales_origen' => 'Granos arábica variedad Geisha 100% orgánicos, empaque biodegradable con válvula desgasificadora',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-EDW01',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80',
        ]);

        $p2 = Producto::create([
            'artesano_id' => $artesanoEdwin->id,
            'nombre_prod' => 'Caja de Chocolates Finos de Cacao Chuncho 70% con Sal de Maras',
            'descripcion_prod' => 'Tabletas de chocolate de origen único elaborado con cacao chuncho ancestral de La Convención y cristales puros de las Salineras de Maras.',
            'categoria' => 'Decoración',
            'subtipo' => 'Gourmet Artesanal',
            'precio' => 38.00,
            'stock' => 40,
            'tamano' => '150 gramos (6 piezas)',
            'colores' => 'Cacao oscuro',
            'puntuacion' => 4.90,
            'materiales_origen' => 'Cacao chuncho silvestre, panela orgánica, sal rosada de Maras cosechada a mano',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-EDW02',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?auto=format&fit=crop&w=800&q=80',
        ]);

        $p3 = Producto::create([
            'artesano_id' => $artesanoEdwin->id,
            'nombre_prod' => 'Camino de Mesa Telar Andino Valle Sagrado',
            'descripcion_prod' => 'Camino de mesa tejido en telar artesanal de pedal por tejedores de la red de Don Edwin. Diseños geométricos que representan las terrazas agrícolas de Pisac.',
            'categoria' => 'Textiles',
            'subtipo' => 'Decoración',
            'precio' => 120.00,
            'stock' => 12,
            'tamano' => '180 x 35 cm',
            'colores' => 'Ocre tierra, Mostaza y Carbón',
            'puntuacion' => 4.88,
            'materiales_origen' => 'Fibra de alpaca con mezcla de algodón orgánico teñido con tintes vegetales',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-EDW03',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=800&q=80',
        ]);

        // =========================================================================
        // PRODUCTOS DE MARCO POLO BACA
        // =========================================================================
        $p4 = Producto::create([
            'artesano_id' => $artesanoMarco->id,
            'nombre_prod' => 'Collar Chakana Cósmica en Plata 950 y Crisocola',
            'descripcion_prod' => 'Dije ceremonial de la cruz andina Chakana finamente repujado en plata peruana ley 950 con incrustación central de piedra crisocola andina.',
            'categoria' => 'Platería',
            'subtipo' => 'Accesorios',
            'precio' => 280.00,
            'en_oferta' => true,
            'precio_oferta' => 238.00,
            'descuento_porcentaje' => 15,
            'etiqueta_promocion' => 'Oferta Especial',
            'stock' => 8,
            'tamano' => 'Dije 3.5 cm / Cadena 50 cm',
            'colores' => 'Plata brillante y Turquesa andino',
            'puntuacion' => 5.00,
            'materiales_origen' => 'Plata certificada ley 950 de Huancavelica, piedra crisocola natural pulida a mano en taller de San Blas',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-MPB01',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80',
        ]);

        $p5 = Producto::create([
            'artesano_id' => $artesanoMarco->id,
            'nombre_prod' => 'Aretes Filigrana Sol de San Blas en Plata 950',
            'descripcion_prod' => 'Aretes colgantes trabajados en técnica milimétrica de filigrana cusqueña, con detalles de hojas de coca y rayos solares incaicos.',
            'categoria' => 'Platería',
            'subtipo' => 'Accesorios',
            'precio' => 195.00,
            'stock' => 15,
            'tamano' => '4.2 cm de largo',
            'colores' => 'Plata 950 bruñida',
            'puntuacion' => 4.92,
            'materiales_origen' => 'Hilos de plata 950 trefilados y soldados a mano con fuelle tradicional',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-MPB02',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=800&q=80',
        ]);

        $p6 = Producto::create([
            'artesano_id' => $artesanoMarco->id,
            'nombre_prod' => 'Cáliz Virreinal Repujado estilo Barroco Cusqueño',
            'descripcion_prod' => 'Pieza exclusiva de orfebrería de exhibición. Réplica fiel del arte colonial virreinal con hojas de acanto y querubines andinos.',
            'categoria' => 'Platería',
            'subtipo' => 'Decoración',
            'precio' => 650.00,
            'stock' => 3,
            'tamano' => 'Alto 22 cm, Base 10 cm',
            'colores' => 'Plata envejecida patinada',
            'puntuacion' => 5.00,
            'materiales_origen' => 'Plata maciza 950, técnica de cincelado y pátina de óxido natural',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-MPB03',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1582738411706-bfc8e691d1c2?auto=format&fit=crop&w=800&q=80',
        ]);

        // =========================================================================
        // PRODUCTOS DE SRA. MARÍA HUAMÁN CONDORI
        // =========================================================================
        $p7 = Producto::create([
            'artesano_id' => $artesanoMaria->id,
            'nombre_prod' => 'Poncho Andino Ceremonial en Lana 100% Baby Alpaca',
            'descripcion_prod' => 'Poncho tradicional de alta montaña tejido con telar de cintura (awaq), con iconografía de las constelaciones andinas y lagunas sagradas de Chinchero.',
            'categoria' => 'Textiles',
            'subtipo' => 'Ropa',
            'precio' => 420.00,
            'en_oferta' => true,
            'precio_oferta' => 336.00,
            'descuento_porcentaje' => 20,
            'etiqueta_promocion' => 'Promoción Exclusiva',
            'stock' => 6,
            'tamano' => '120 x 140 cm (Estándar)',
            'colores' => 'Rojo cochinilla, Añil y Nogal',
            'puntuacion' => 4.98,
            'materiales_origen' => 'Lana 100% Baby Alpaca virgen esquilada responsablemente, teñida con insecto de cochinilla silvestre y flor de qolle',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-MHC01',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1590736969955-71cc94801759?auto=format&fit=crop&w=800&q=80',
        ]);

        $p8 = Producto::create([
            'artesano_id' => $artesanoMaria->id,
            'nombre_prod' => 'Chalina Clásica Extra Suave en Baby Alpaca Natural',
            'descripcion_prod' => 'Chalina de textura sedosa y calidez térmica inigualable sin peso excesivo. Teñida con tonos naturales sin químicos sintéticos.',
            'categoria' => 'Textiles',
            'subtipo' => 'Accesorios',
            'precio' => 160.00,
            'stock' => 20,
            'tamano' => '190 x 30 cm con flecos trenzados',
            'colores' => 'Vicuña natural y Crema andino',
            'puntuacion' => 4.94,
            'materiales_origen' => '100% fibra de alpaca seleccionada a mano, tintes orgánicos de hojas de chilca',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-MHC02',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1520903920243-00d872a2d1c9?auto=format&fit=crop&w=800&q=80',
        ]);

        $p9 = Producto::create([
            'artesano_id' => $artesanoMaria->id,
            'nombre_prod' => 'Cojín Étnico Pallay Chinchero con Flecos',
            'descripcion_prod' => 'Funda de cojín decorativo con bordados finos de fauna andina (cóndor, vicuña y picaflor) y grecas de protección comunal.',
            'categoria' => 'Textiles',
            'subtipo' => 'Decoración',
            'precio' => 95.00,
            'stock' => 14,
            'tamano' => '45 x 45 cm',
            'colores' => 'Terracota, Ocre y Azul andino',
            'puntuacion' => 4.87,
            'materiales_origen' => 'Lana de alpaca y ovino criollo, cremallera oculta y forro de liencillo de algodón',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-MHC03',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&w=800&q=80',
        ]);

        // Pieza adicional para Cerámica (Pisac)
        $p10 = Producto::create([
            'artesano_id' => $artesanoMarco->id,
            'nombre_prod' => 'Torito de Pucará Ceremonial Policromado en Arcilla',
            'descripcion_prod' => 'Símbolo andino de prosperidad, fertilidad y protección del hogar. Pintado a mano con pigmentos minerales tradicionales y barniz natural.',
            'categoria' => 'Cerámica',
            'subtipo' => 'Decoración',
            'precio' => 85.00,
            'en_oferta' => true,
            'precio_oferta' => 68.00,
            'descuento_porcentaje' => 20,
            'etiqueta_promocion' => 'Oferta Pisac',
            'stock' => 18,
            'tamano' => 'Alto 18 cm, Largo 15 cm',
            'colores' => 'Tierra roja, Blanco tiza y Dorado',
            'puntuacion' => 4.96,
            'materiales_origen' => 'Arcilla cusqueña cocida a leña en horno tradicional, pigmentos minerales andinos',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-CER01',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80',
        ]);

        // Pieza adicional para Imaginería
        $p11 = Producto::create([
            'artesano_id' => $artesanoMarco->id,
            'nombre_prod' => 'Arcángel Arcabucero Cusqueño con Pan de Oro',
            'descripcion_prod' => 'Escultura de imaginería cusqueña tradicional inspirada en la Escuela de Pintura del Cusco virreinal, policromada con acabado en pan de oro.',
            'categoria' => 'Imaginería',
            'subtipo' => 'Decoración',
            'precio' => 340.00,
            'stock' => 4,
            'tamano' => 'Alto 32 cm',
            'colores' => 'Rojo carmesí, Dorado pan de oro y Azul marino',
            'puntuacion' => 5.00,
            'materiales_origen' => 'Madera de cedro tallada, pasta de maguey, yeso mate y láminas de pan de oro fino',
            'certificado_autenticidad_codigo' => 'CERT-CUSCO-2026-IMG01',
            'aprobado_hub' => true,
            'imagen_url' => 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?auto=format&fit=crop&w=800&q=80',
        ]);

        // 3. Crear un Pedido Demostrativo con Trazabilidad y Certificación
        $pedidoDemo = Pedido::create([
            'cliente_id' => $cliente->id,
            'cant_producto' => 2,
            'direccion_envio' => '24 Rue de la Paix, 75002 Paris, Francia',
            'pais_destino' => 'Francia',
            'tipo_envio' => 'Internacional con seguro',
            'envoltura_regalo' => true,
            'mensaje_regalo' => 'Un pedazo del alma andina de Cusco para la familia Laurent en París.',
            'subtotal' => 440.00,
            'comision_curaduria_hub' => 66.00,
            'costo_envio' => 85.00,
            'total' => 537.00, // 440 + 85 + 12 (regalo)
            'estado_pedido' => 'en_acopio_hub',
            'codigo_seguimiento' => 'HUB-CUSCO-FR78921',
            'notas' => 'Pedido con seguro internacional de transporte contra pérdida y daño.',
        ]);

        $pedidoDemo->productos()->attach([
            $p4->id => ['cantidad' => 1, 'precio_unitario_historico' => 280.00],
            $p8->id => ['cantidad' => 1, 'precio_unitario_historico' => 160.00],
        ]);

        // 4. Conversación de Chat Demostrativa (Cliente <-> Artesano con Resumen para HUB)
        $conversacionDemo = Conversacion::create([
            'cliente_id' => $cliente->id,
            'artesano_id' => $artesanoEdwin->id,
            'pedido_id' => $pedidoDemo->id,
            'producto_id' => $p4->id,
            'asunto' => 'Consulta de personalización: Poncho Chinchero Tradicional',
            'categoria_consulta' => 'Personalización y Medidas',
            'estado' => 'abierto',
            'necesita_intervencion_hub' => false,
            'ultimo_mensaje_at' => now(),
        ]);

        Mensaje::create([
            'conversacion_id' => $conversacionDemo->id,
            'remitente_id' => $cliente->id,
            'mensaje' => '¡Hola maestro Edwin! He visto su Poncho Tradicional Chinchero en el catálogo. ¿Sería posible tejerlo con 10 cm adicionales de largo para mi esposo en París?',
            'leido' => true,
            'created_at' => now()->subMinutes(25),
        ]);

        Mensaje::create([
            'conversacion_id' => $conversacionDemo->id,
            'remitente_id' => $userEdwin->id,
            'mensaje' => '¡Allianllachu Sophie! Con mucho gusto. Al ser tejido a mano en telar de cuatro estacas con pura lana de alpaca, puedo añadirle los 10 cm respetando los pallays tradicionales de Chinchero. Tomará unos 3 a 4 días en mi taller antes de llevarlo al HUB de acopio.',
            'leido' => true,
            'created_at' => now()->subMinutes(18),
        ]);

        Mensaje::create([
            'conversacion_id' => $conversacionDemo->id,
            'remitente_id' => $cliente->id,
            'mensaje' => '¡Maravilloso maestro! Ya he procesado la compra y solicité el empaque de regalo artesanal con tarjeta QR para enviárselo directamente a mi familia.',
            'leido' => true,
            'created_at' => now()->subMinutes(10),
        ]);

        Mensaje::create([
            'conversacion_id' => $conversacionDemo->id,
            'remitente_id' => $userEdwin->id,
            'mensaje' => 'Perfecto Sophie. Ya tengo la orden visible en mi panel con las instrucciones del regalo y el código QR listo para imprimir y adjuntar. ¡Sumaq sonqowan!',
            'leido' => false,
            'created_at' => now()->subMinutes(3),
        ]);

        $conversacionDemo->actualizarResumenHub();
    }
}
