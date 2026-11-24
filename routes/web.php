<?php

use App\Http\Controllers\ArtesanoPanelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\HubAdminController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SecurityLabController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas: Inicio, Catálogo y Ficha de Trazabilidad
|--------------------------------------------------------------------------
*/
Route::get('/', [ProductoController::class, 'home'])->name('home');
Route::get('/catalogo', [ProductoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/{id}', [ProductoController::class, 'show'])->name('catalogo.show');
Route::get('/dedicatoria/{id_o_codigo}', [PedidoController::class, 'dedicatoria'])->name('pedidos.dedicatoria');

/*
|--------------------------------------------------------------------------
| Tipo de Cambio y Selector de Moneda (PEN / USD)
|--------------------------------------------------------------------------
*/
Route::get('/api/tipo-cambio', [CurrencyController::class, 'getRate'])->name('currency.rate');
Route::match(['GET', 'POST'], '/cambiar-moneda', [CurrencyController::class, 'setCurrency'])->name('currency.set');

/*
|--------------------------------------------------------------------------
| Módulo de Carrito de Compras (Shopping Basket)
|--------------------------------------------------------------------------
*/
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/agregar', [CarritoController::class, 'add'])->name('carrito.add');
Route::post('/carrito/actualizar', [CarritoController::class, 'update'])->name('carrito.update');
Route::post('/carrito/eliminar', [CarritoController::class, 'remove'])->name('carrito.remove');
Route::post('/carrito/vaciar', [CarritoController::class, 'clear'])->name('carrito.clear');

/*
|--------------------------------------------------------------------------
| Autenticación de Usuarios
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Checkout y Pedidos (Protegido por Autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [PedidoController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout', [PedidoController::class, 'store'])->name('checkout.store');
    Route::get('/pedidos/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::get('/mis-pedidos', [PedidoController::class, 'misPedidos'])->name('pedidos.index');

    // Chat en Vivo Cliente <-> Artesano
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/iniciar', [ChatController::class, 'iniciar'])->name('chat.iniciar');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{id}/mensaje', [ChatController::class, 'enviarMensaje'])->name('chat.enviar');
    Route::get('/chat/{id}/mensajes-nuevos', [ChatController::class, 'obtenerNuevosMensajes'])->name('chat.nuevos');
});

/*
|--------------------------------------------------------------------------
| Panel de Control del Artesano Cusqueño
|--------------------------------------------------------------------------
*/
Route::prefix('artesano')
    ->name('artesano.')
    ->middleware(['auth', 'role:artesano,admin_hub'])
    ->group(function () {
        Route::get('/dashboard', [ArtesanoPanelController::class, 'index'])->name('dashboard');
        Route::post('/taller', [ArtesanoPanelController::class, 'registrarTaller'])->name('taller.guardar');
        Route::get('/productos/crear', [ArtesanoPanelController::class, 'create'])->name('productos.create');
        Route::post('/productos', [ArtesanoPanelController::class, 'store'])->name('productos.store');
        Route::get('/productos/{id}/editar', [ArtesanoPanelController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{id}', [ArtesanoPanelController::class, 'update'])->name('productos.update');
        Route::post('/pedidos/{id}/confirmar', [ArtesanoPanelController::class, 'confirmarPreparacion'])->name('pedidos.confirmar');
        Route::get('/api/verificar-pedidos', [ArtesanoPanelController::class, 'verificarNuevosPedidos'])->name('pedidos.verificar');
    });

/*
|--------------------------------------------------------------------------
| Panel de Administración del HUB Logístico y Certificación Digital
|--------------------------------------------------------------------------
*/
Route::prefix('admin-hub')
    ->name('admin.')
    ->middleware(['auth', 'role:admin_hub'])
    ->group(function () {
        Route::get('/', [HubAdminController::class, 'index'])->name('dashboard');
        Route::post('/artesanos/{id}/toggle-validacion', [HubAdminController::class, 'toggleValidacionArtesano'])->name('artesanos.toggle');
        Route::post('/productos/{id}/toggle-aprobacion', [HubAdminController::class, 'toggleAprobacionProducto'])->name('productos.toggle');
        Route::post('/pedidos/{id}/estado', [HubAdminController::class, 'updateEstadoPedido'])->name('pedidos.estado');
        Route::get('/chats-resumen', [ChatController::class, 'adminResumenIndex'])->name('chats.resumen');
        Route::post('/chats/{id}/estado', [ChatController::class, 'adminActualizarEstado'])->name('chats.estado');
    });

/*
|--------------------------------------------------------------------------
| Laboratorio de Vulnerabilidades Web (PortSwigger Security Academy)
|--------------------------------------------------------------------------
*/
Route::get('/seguridad/labs', [SecurityLabController::class, 'index'])->name('seguridad.labs');
Route::post('/seguridad/labs/test/{id}', [SecurityLabController::class, 'testLab'])->name('seguridad.labs.test');
