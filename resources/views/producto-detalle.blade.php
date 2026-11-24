@extends('layouts.app')

@section('title', $producto->nombre_prod . ' | Trazabilidad & Certificado Cusco')

@section('content')
<div class="container">
    <!-- Breadcrumb accesible -->
    <nav aria-label="Ruta de navegación" style="padding: 18px 0 6px; font-size: 0.85rem; color: var(--color-charcoal-muted);">
        <ol style="display: flex; gap: 8px; list-style: none;">
            <li><a href="{{ route('home') }}" style="color: var(--color-earth-primary);">Inicio</a></li>
            <li>/</li>
            <li><a href="{{ route('catalogo.index') }}" style="color: var(--color-earth-primary);">Catálogo</a></li>
            <li>/</li>
            <li><a href="{{ route('catalogo.index', ['categoria' => $producto->categoria]) }}" style="color: var(--color-earth-primary);">{{ $producto->categoria }}</a></li>
            <li>/</li>
            <li aria-current="page" style="color: var(--color-charcoal-dark); font-weight: 600;">{{ $producto->nombre_prod }}</li>
        </ol>
    </nav>

    <div class="product-detail-layout">
        <!-- 1. Galería de Fotografía de la Pieza & Mapa del Taller -->
        <div class="product-gallery">
            <img src="{{ $producto->imagen_url }}" 
                 alt="Fotografía en alta definición de la obra: {{ $producto->nombre_prod }}" 
                 class="gallery-main-img" 
                 id="main-product-image">
            
            <div style="margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap;">
                <span class="badge badge-community">📍 Comunidad: {{ $producto->artesano->direccion_arte }}</span>
                <span class="badge badge-hub">🛡️ Aprobado HUB Logístico</span>
            </div>

            <!-- Mapa de Ubicación del Taller & Tienda en Cusco -->
            <div class="workshop-location-card" style="margin-top: 22px; background: #FFFFFF; border: 1.5px solid #E5DACB; border-radius: var(--radius-md); padding: 18px; box-shadow: 0 4px 16px rgba(84, 51, 16, 0.06);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; color: #8A4614; letter-spacing: 1px;">
                            📍 Trazabilidad Geográfica
                        </div>
                        <h3 style="font-family: var(--font-serif); font-size: 1.15rem; color: #2C1E11; margin: 2px 0 0 0;">
                            Taller & Tienda del Maestro Artesano
                        </h3>
                    </div>
                    <span class="badge" style="background: #E8F5E9; color: #2E6B38; font-size: 0.72rem; font-weight: 700;">
                        ✓ Taller Físico en Cusco
                    </span>
                </div>

                <!-- Dirección y Datos de Visita -->
                <div style="font-size: 0.86rem; color: #4A3320; line-height: 1.45; margin-bottom: 12px; background: #FAF6ED; padding: 10px 14px; border-radius: 6px; border-left: 3px solid #C88A2C;">
                    <div style="margin-bottom: 4px;">
                        <strong>🏬 Dirección:</strong> {{ $producto->artesano->direccion_completa }}
                    </div>
                    <div style="font-size: 0.8rem; color: #7A624E; display: flex; gap: 14px; flex-wrap: wrap; margin-top: 4px;">
                        <span>🕒 Lun a Sáb: 9:00 AM - 7:30 PM</span>
                        <span>📞 {{ $producto->artesano->telefono }}</span>
                    </div>
                </div>

                <!-- Contenedor del Mapa Interactivo -->
                <div style="border-radius: 8px; overflow: hidden; border: 1px solid #D8C6B1; position: relative; background: #EFE9DE; height: 260px;">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        style="border: 0; display: block;" 
                        loading="lazy" 
                        allowfullscreen 
                        referrerpolicy="no-referrer-when-downgrade" 
                        title="Mapa de ubicación del taller de {{ $producto->artesano->nombre_completo }}"
                        src="{{ $producto->artesano->url_embed_maps }}">
                    </iframe>
                </div>

                <!-- Botones y Garantía de Acopio -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; flex-wrap: wrap; gap: 10px;">
                    <div style="font-size: 0.78rem; color: #7A624E; display: flex; align-items: center; gap: 6px;">
                        <span>🚚</span> <span>Acopio directo a 10 min del HUB Cusco</span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ $producto->artesano->url_google_maps }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="btn btn-outline-earth btn-sm" 
                           style="font-size: 0.78rem; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                            <span>🗺️</span> <span>Abrir en Google Maps</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Panel de Información, Trazabilidad y Compra -->
        <div class="product-info-panel">
            <div class="detail-badge-row">
                <span class="badge badge-community">{{ $producto->categoria }}</span>
                @if($producto->subtipo)
                    <span class="badge" style="background-color: var(--color-cream-border);">{{ $producto->subtipo }}</span>
                @endif
                <span style="font-size: 0.85rem; color: var(--color-fairtrade-green); font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    ★ {{ number_format($producto->puntuacion, 1) }} / 5.0 (Valoración de curaduría)
                </span>
            </div>

            <h1 class="detail-title">{{ $producto->nombre_prod }}</h1>

            <!-- Caja de Precio y Disponibilidad -->
            <div class="detail-price-box">
                @if($producto->en_oferta)
                    <div style="margin-bottom: 8px;">
                        <span class="product-tag-offer" style="position: static; font-size: 0.8rem; padding: 4px 12px;">
                            🔥 Oferta Especial: -{{ $producto->porcentaje_descuento_calculado ?? 20 }}% {{ $producto->etiqueta_promocion ? '· ' . $producto->etiqueta_promocion : '' }}
                        </span>
                    </div>
                @endif
                <div class="product-price-wrap" 
                     data-price-pen="{{ $producto->precio_efectivo }}" 
                     data-original-price-pen="{{ $producto->en_oferta ? $producto->precio : '' }}"
                     data-is-offer="{{ $producto->en_oferta ? 'true' : 'false' }}">
                    <div style="display: flex; align-items: baseline; flex-wrap: wrap; gap: 8px;">
                        @if($producto->en_oferta)
                            <del class="price-original-del" style="font-size: 1.1rem;" title="Precio regular anterior">
                                @if(($monedaActiva ?? 'PEN') === 'USD')
                                    $ {{ number_format($producto->precio / ($tipoCambio ?? 3.41), 2) }}
                                @else
                                    S/ {{ number_format($producto->precio, 2) }}
                                @endif
                            </del>
                        @endif
                        <span class="detail-price-main price-main {{ $producto->en_oferta ? 'price-offer' : '' }}">
                            @if(($monedaActiva ?? 'PEN') === 'USD')
                                $ {{ number_format($producto->precio_efectivo / ($tipoCambio ?? 3.41), 2) }}
                            @else
                                S/ {{ number_format($producto->precio_efectivo, 2) }}
                            @endif
                        </span>
                        <span class="price-currency" style="font-size: 0.95rem; color: var(--color-charcoal-muted);">
                            {{ ($monedaActiva ?? 'PEN') === 'USD' ? 'USD' : 'PEN' }}
                        </span>
                        <span class="price-secondary detail-price-alt">
                            @if(($monedaActiva ?? 'PEN') === 'USD')
                                (≈ S/ {{ number_format($producto->precio_efectivo, 2) }} PEN)
                            @else
                                (≈ $ {{ number_format($producto->precio_efectivo / ($tipoCambio ?? 3.41), 2) }} USD)
                            @endif
                        </span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                        Precio base justo + certificación digital incluida &bull; Tipo de cambio en vivo: 1 USD ≈ S/ {{ number_format($tipoCambio ?? 3.41, 2) }}
                    </div>
                </div>

                <div>
                    @if($producto->stock > 0)
                        <span class="stock-indicator in-stock">
                            ● En stock ({{ $producto->stock }} unidades disponibles)
                        </span>
                    @else
                        <span class="stock-indicator low-stock">
                            ✕ Agotado temporalmente en taller
                        </span>
                    @endif
                </div>
            </div>

            <div style="margin-bottom: 20px; font-size: 1rem; color: var(--color-charcoal-light); line-height: 1.6;">
                {{ $producto->descripcion_prod }}
            </div>

            <!-- Ficha de Atributos Físicos -->
            <div style="background-color: var(--color-cream-surface); border: 1px solid var(--color-cream-border); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 20px; font-size: 0.88rem;">
                <div style="margin-bottom: 6px;">
                    <strong>Dimensiones / Tamaño:</strong> {{ $producto->tamano ?? 'Estándar' }}
                </div>
                @if($producto->colores)
                    <div style="margin-bottom: 6px;">
                        <strong>Colores / Pigmentación:</strong> {{ $producto->colores }}
                    </div>
                @endif
                <div>
                    <strong>Materiales Empleados:</strong> {{ $producto->materiales_origen }}
                </div>
            </div>

            <!-- Controles de Compra -->
            @if($producto->stock > 0)
                <form action="{{ route('carrito.add') }}" method="POST" id="form-add-to-cart">
                    @csrf
                    <input type="hidden" name="producto_id" value="{{ $producto->id }}">

                    <div class="purchase-controls-row">
                        <div class="quantity-selector" aria-label="Selector de cantidad">
                            <button type="button" class="qty-btn qty-minus" aria-label="Disminuir cantidad">-</button>
                            <input type="number" 
                                   name="cantidad" 
                                   id="product-qty-input"
                                   class="qty-input" 
                                   value="1" 
                                   min="1" 
                                   max="{{ $producto->stock }}" 
                                   aria-label="Cantidad seleccionada">
                            <button type="button" class="qty-btn qty-plus" aria-label="Aumentar cantidad">+</button>
                        </div>

                        <button type="submit" 
                                class="btn btn-accent btn-quick-add-detail btn-quick-add" 
                                data-id="{{ $producto->id }}"
                                style="flex: 1; padding: 12px 24px;">
                            🛒 Añadir al Carrito
                        </button>
                    </div>
                </form>
            @endif

            <!-- Botón de Chat en Vivo con el Artesano Creador -->
            <div style="margin-top: 14px; margin-bottom: 20px;">
                @auth
                    <form action="{{ route('chat.iniciar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="artesano_id" value="{{ $producto->artesano_id }}">
                        <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                        <input type="hidden" name="asunto" value="Consulta sobre «{{ $producto->nombre_prod }}»">
                        <button type="submit" class="btn btn-outline-earth" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 16px;">
                            <span>💬</span> <strong>Chatear en Vivo con el Maestro Artesano</strong>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-earth" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 16px; text-decoration: none;">
                        <span>💬</span> <strong>Inicia sesión para Chatear con el Artesano</strong>
                    </a>
                @endauth
            </div>

            <!-- 3. FICHA DE TRAZABILIDAD CULTURAL Y TRANSPARENCIA -->
            <section class="traceability-card" aria-label="Trazabilidad cultural y comercio justo">
                <header class="traceability-header">
                    <img src="{{ $producto->artesano->foto_perfil ?? 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=400&q=80' }}" 
                         alt="Foto de perfil del maestro {{ $producto->artesano->nombre_completo }}" 
                         class="artisan-avatar">
                    <div class="artisan-meta">
                        <h4>Obra creada por {{ $producto->artesano->nombre_completo }}</h4>
                        <p>📍 {{ $producto->artesano->direccion_arte }}</p>
                    </div>
                </header>

                <p style="font-size: 0.88rem; color: var(--color-charcoal-light); margin-bottom: 16px; line-height: 1.5; font-style: italic;">
                    «{{ $producto->artesano->historia_taller }}»
                </p>

                <!-- Sello de Certificado Digital de Autenticidad -->
                <div class="certificate-badge-box">
                    <div class="cert-stamp-icon" aria-hidden="true">📜</div>
                    <div class="cert-text">
                        <h5>Sello de Autenticidad Digital HUB Cusco</h5>
                        <p style="font-size: 0.8rem; color: var(--color-charcoal-muted); margin-top: 2px;">
                            Certificado N.º: <code class="cert-code">{{ $producto->certificado_autenticidad_codigo }}</code>
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- 4. Sección Inferior: Shop Similar (Productos Similares) -->
    @if($similares->count() > 0)
        <section style="margin-top: 50px; padding-bottom: 60px;" aria-label="Productos similares">
            <div class="section-title-wrap">
                <span class="section-tag">Más de Nuestras Comunidades</span>
                <h3 class="section-main-title">Piezas Similares que Podrían Gustarte (Shop Similar)</h3>
            </div>

            <div class="products-grid">
                @foreach($similares as $sim)
                    <article class="product-card" aria-label="Pieza similar: {{ $sim->nombre_prod }}">
                        <div class="product-thumb-wrap">
                            <img src="{{ $sim->imagen_url }}" alt="Fotografía de {{ $sim->nombre_prod }}" class="product-thumb" loading="lazy">
                            <span class="product-tag-origin">📍 {{ $sim->artesano->direccion_arte }}</span>
                        </div>
                        <div class="product-card-body">
                            <div class="product-category-row">
                                <span>{{ $sim->categoria }}</span>
                                <span class="product-artisan-name">Por {{ $sim->artesano->nombre_completo }}</span>
                            </div>
                            <h4 class="product-title">
                                <a href="{{ route('catalogo.show', $sim->id) }}">{{ $sim->nombre_prod }}</a>
                            </h4>
                            <div class="product-price-row">
                                <div class="product-price-wrap" data-price-pen="{{ $sim->precio }}">
                                    <span class="product-price-amount price-main">
                                        @if(($monedaActiva ?? 'PEN') === 'USD')
                                            $ {{ number_format($sim->precio / ($tipoCambio ?? 3.75), 2) }}
                                        @else
                                            S/ {{ number_format($sim->precio, 2) }}
                                        @endif
                                    </span>
                                    <span class="price-secondary">
                                        @if(($monedaActiva ?? 'PEN') === 'USD')
                                            ≈ S/ {{ number_format($sim->precio, 2) }} PEN
                                        @else
                                            ≈ $ {{ number_format($sim->precio / ($tipoCambio ?? 3.75), 2) }} USD
                                        @endif
                                    </span>
                                </div>
                                <a href="{{ route('catalogo.show', $sim->id) }}" class="btn btn-secondary btn-sm">Ver Obra</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
