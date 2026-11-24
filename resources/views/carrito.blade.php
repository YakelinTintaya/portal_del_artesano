@extends('layouts.app')

@section('title', 'Carrito de Compras | Portal del Artesano Cusco')

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px;">
    <!-- Breadcrumb -->
    <nav aria-label="Ruta de navegación" style="margin-bottom: 20px; font-size: 0.85rem; color: var(--color-charcoal-muted);">
        <ol style="display: flex; gap: 8px; list-style: none;">
            <li><a href="{{ route('home') }}" style="color: var(--color-earth-primary);">Inicio</a></li>
            <li>/</li>
            <li><a href="{{ route('catalogo.index') }}" style="color: var(--color-earth-primary);">Catálogo</a></li>
            <li>/</li>
            <li aria-current="page" style="color: var(--color-charcoal-dark); font-weight: 600;">Carrito de Compras</li>
        </ol>
    </nav>

    <h1 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-charcoal-dark); margin-bottom: 24px;">
        Tu Carrito de Compras
    </h1>

    @if(!empty($carrito) && count($carrito) > 0)
        <div class="cart-layout">
            <!-- 1. Tabla de Artículos en la Bolsa -->
            <section class="cart-items-card" aria-label="Artículos seleccionados">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th scope="col">Pieza Artesanal</th>
                            <th scope="col">Precio Base</th>
                            <th scope="col">Cantidad</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carrito as $id => $item)
                            <tr>
                                <td>
                                    <div class="cart-item-info">
                                        <img src="{{ $item['imagen_url'] ?? '' }}" alt="{{ $item['nombre'] ?? 'Pieza' }}" class="cart-thumb">
                                        <div>
                                            <a href="{{ route('catalogo.show', $item['id']) }}" style="font-weight: 600; font-family: var(--font-serif); color: var(--color-charcoal-dark);">
                                                {{ $item['nombre'] ?? 'Pieza Artesanal' }}
                                            </a>
                                            <div style="font-size: 0.8rem; color: var(--color-charcoal-muted);">
                                                Taller: {{ $item['artesano'] ?? 'Maestro Artesano' }} ({{ $item['comunidad'] ?? 'Cusco' }})
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--color-fairtrade-green); font-weight: 600;">
                                                ✓ Certificado Digital HUB
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    S/ {{ number_format($item['precio'], 2) }}
                                </td>
                                <td>
                                    <div class="quantity-selector" style="display: inline-flex; align-items: center;">
                                        <form action="{{ route('carrito.update') }}" method="POST" style="display: inline; margin: 0;">
                                            @csrf
                                            <input type="hidden" name="producto_id" value="{{ $item['id'] }}">
                                            <input type="hidden" name="accion" value="decrementar">
                                            <button type="submit" 
                                                    class="qty-btn cart-qty-btn" 
                                                    data-id="{{ $item['id'] }}" 
                                                    data-action="decrementar" 
                                                    aria-label="Disminuir cantidad de {{ $item['nombre'] }}">-</button>
                                        </form>
                                        <span style="display: inline-block; width: 36px; text-align: center; font-weight: 600;">
                                            {{ $item['cantidad'] }}
                                        </span>
                                        <form action="{{ route('carrito.update') }}" method="POST" style="display: inline; margin: 0;">
                                            @csrf
                                            <input type="hidden" name="producto_id" value="{{ $item['id'] }}">
                                            <input type="hidden" name="accion" value="incrementar">
                                            <button type="submit" 
                                                    class="qty-btn cart-qty-btn" 
                                                    data-id="{{ $item['id'] }}" 
                                                    data-action="incrementar" 
                                                    aria-label="Aumentar cantidad de {{ $item['nombre'] }}">+</button>
                                        </form>
                                    </div>
                                </td>
                                <td style="font-weight: 700; color: var(--color-charcoal-dark);">
                                    S/ {{ number_format($item['precio'] * $item['cantidad'], 2) }}
                                </td>
                                <td style="text-align: right;">
                                    <form action="{{ route('carrito.remove') }}" method="POST" style="display: inline; margin: 0;">
                                        @csrf
                                        <input type="hidden" name="producto_id" value="{{ $item['id'] }}">
                                        <button type="submit" 
                                                class="btn btn-secondary btn-sm cart-remove-btn" 
                                                data-id="{{ $item['id'] }}" 
                                                title="Retirar artículo"
                                                aria-label="Eliminar {{ $item['nombre'] }} del carrito">
                                            ✕
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Opción de Envoltura de Regalo (Gift Wrapping) -->
                <div class="gift-wrapping-box">
                    <input type="checkbox" 
                           id="gift-wrapping-checkbox" 
                           style="margin-top: 4px; cursor: pointer; width: 18px; height: 18px;" 
                           {{ $envolturaRegalo ? 'checked' : '' }}>
                    <label for="gift-wrapping-checkbox" style="cursor: pointer;">
                        <strong>🎁 ¿Deseas envoltura de regalo artesanal andina (+ S/ 12.00 PEN)?</strong>
                        <p style="font-size: 0.85rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                            Incluye caja rígida decorada con cinta de telar tradicional cusqueño, papel kraft biodegradable y tarjeta de dedicatoria personalizada con sello en cera del HUB.
                        </p>
                    </label>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px;">
                    <a href="{{ route('catalogo.index') }}" class="btn btn-secondary btn-sm">
                        ← Continuar Comprando
                    </a>
                    <form action="{{ route('carrito.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--color-danger); border-color: rgba(192,57,43,0.3);">
                            Vaciar Carrito
                        </button>
                    </form>
                </div>
            </section>

            <!-- 2. Resumen de la Orden y Desglose de Impacto -->
            <aside class="cart-summary-card" aria-label="Resumen de compra">
                <h2 class="summary-title">Resumen de Compra</h2>

                <div class="summary-row">
                    <span>Subtotal de piezas ({{ $totales['items_count'] }} ítems):</span>
                    <span>S/ {{ number_format($totales['subtotal'], 2) }}</span>
                </div>

                <div class="summary-row">
                    <span>Envoltura de regalo:</span>
                    <span>S/ {{ number_format($totales['costo_envoltura'], 2) }}</span>
                </div>

                <div class="summary-row" style="color: var(--color-charcoal-muted); font-size: 0.85rem;">
                    <span>Envío (Región Cusco):</span>
                    <span>Se calcula en Checkout</span>
                </div>

                <div class="summary-row total-row">
                    <span>Total Parcial:</span>
                    <span style="color: var(--color-charcoal-dark);">S/ {{ number_format($totales['total'], 2) }} PEN</span>
                </div>

                <!-- Garantía de Comercio Justo en el Carrito -->
                <div style="background-color: var(--color-fairtrade-light); border: 1px solid rgba(46,111,64,0.25); border-radius: var(--radius-sm); padding: 12px; margin: 18px 0; font-size: 0.82rem; color: var(--color-fairtrade-green);">
                    🤝 <strong>Comercio Justo Garantizado:</strong> Cada adquisición impulsa directamente el trabajo de las familias artesanas y la preservación del arte tradicional cusqueño.
                </div>

                <a href="{{ route('checkout.index') }}" class="btn btn-accent btn-block" style="padding: 14px 20px; font-size: 1.05rem;">
                    Proceder al Pago Seguro (Purchase) →
                </a>

                <p style="font-size: 0.78rem; text-align: center; color: var(--color-charcoal-muted); margin-top: 12px;">
                    🛡️ Compra protegida con certificación de origen cusqueño.
                </p>
            </aside>
        </div>
    @else
        <!-- Carrito Vacío -->
        <div style="background-color: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 60px 20px; text-align: center; max-width: 600px; margin: 0 auto;">
            <div style="font-size: 3rem; margin-bottom: 12px;" aria-hidden="true">🛒</div>
            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: 10px;">Tu carrito de compras está vacío</h2>
            <p style="color: var(--color-charcoal-muted); margin-bottom: 24px;">
                Descubre hermosos textiles de alpaca, platería virreinal y cerámicas tradicionales elaboradas a mano en Cusco.
            </p>
            <a href="{{ route('catalogo.index') }}" class="btn btn-primary">
                Explorar el Catálogo de Obras
            </a>
        </div>
    @endif
</div>
@endsection
