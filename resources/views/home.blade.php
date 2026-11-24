@extends('layouts.app')

@section('title', 'Portal del Artesano Cusco | Arte Andino, Comercio Justo y Exportación')

@section('content')
    <!-- 1. Hero Slider Carousel: Obras de Arte & Ofertas de Temporada -->
    <section class="hero-slider-section" id="hero-slider-section" aria-label="Carrusel principal de obras cusqueñas y promociones">
        <div class="hero-slider-container" id="hero-slider-container">
            <div class="hero-slides-track" id="hero-slides-track">
                @foreach($heroSlides as $index => $slide)
                    <div class="hero-slide" 
                         style="background-image: url('{{ $slide->imagen_url }}');" 
                         data-slide-index="{{ $index }}">
                        <div class="container">
                            <div class="hero-slide-content">
                                <div class="hero-slide-info">
                                    @if($slide->en_oferta)
                                        <span class="hero-badge-tag offer">
                                            🔥 {{ $slide->etiqueta_promocion ?: 'Oferta Especial' }} &bull; -{{ $slide->porcentaje_descuento_calculado ?? 20 }}% OFF
                                        </span>
                                    @else
                                        <span class="hero-badge-tag">
                                            ❖ Pieza de Colección Tradicional
                                        </span>
                                    @endif

                                    <h1 class="hero-slide-title">
                                        {{ $slide->nombre_prod }}
                                    </h1>

                                    <div class="hero-slide-meta">
                                        <span>📍 {{ $slide->artesano->direccion_arte }}</span>
                                        <span>&bull;</span>
                                        <span>Taller: {{ $slide->artesano->nombre_completo }}</span>
                                        <span>&bull;</span>
                                        <span style="color: var(--color-fairtrade-green);">★ {{ number_format($slide->puntuacion, 1) }}</span>
                                    </div>

                                    <p class="hero-slide-desc">
                                        {{ Str::limit($slide->descripcion_prod, 175) }}
                                    </p>

                                    <!-- Bloque de Precio Adaptativo PEN / USD -->
                                    <div class="hero-price-wrap product-price-wrap" 
                                         data-price-pen="{{ $slide->precio_efectivo }}" 
                                         data-original-price-pen="{{ $slide->en_oferta ? $slide->precio : '' }}"
                                         data-is-offer="{{ $slide->en_oferta ? 'true' : 'false' }}">
                                        @if($slide->en_oferta)
                                            <del class="hero-price-original price-original-del" title="Precio regular anterior">
                                                @if(($monedaActiva ?? 'PEN') === 'USD')
                                                    $ {{ number_format($slide->precio / ($tipoCambio ?? 3.41), 2) }}
                                                @else
                                                    S/ {{ number_format($slide->precio, 2) }}
                                                @endif
                                            </del>
                                        @endif

                                        <span class="hero-price-current price-main {{ $slide->en_oferta ? 'is-offer' : '' }}">
                                            @if(($monedaActiva ?? 'PEN') === 'USD')
                                                $ {{ number_format($slide->precio_efectivo / ($tipoCambio ?? 3.41), 2) }}
                                            @else
                                                S/ {{ number_format($slide->precio_efectivo, 2) }}
                                            @endif
                                        </span>

                                        <span class="hero-price-code price-currency">
                                            {{ ($monedaActiva ?? 'PEN') === 'USD' ? 'USD' : 'PEN' }}
                                        </span>

                                        <span class="hero-price-alt price-secondary">
                                            @if(($monedaActiva ?? 'PEN') === 'USD')
                                                (≈ S/ {{ number_format($slide->precio_efectivo, 2) }} PEN)
                                            @else
                                                (≈ $ {{ number_format($slide->precio_efectivo / ($tipoCambio ?? 3.41), 2) }} USD)
                                            @endif
                                        </span>
                                    </div>

                                    <div class="hero-slide-actions">
                                        <button type="button" 
                                                class="btn btn-accent btn-quick-add" 
                                                data-id="{{ $slide->id }}"
                                                aria-label="Añadir {{ $slide->nombre_prod }} al carrito">
                                            🛒 Añadir al Carrito
                                        </button>
                                        <a href="{{ route('catalogo.show', $slide->id) }}" class="btn btn-secondary" style="border-color: rgba(255,255,255,0.4); color: var(--color-white);">
                                            Ver Ficha & Trazabilidad
                                        </a>
                                        <a href="{{ route('catalogo.index') }}" style="color: var(--color-gold); font-size: 0.9rem; font-weight: 600; text-decoration: underline; margin-left: 8px;">
                                            Ver Todo el Catálogo →
                                        </a>
                                    </div>
                                </div>

                                <!-- Tarjeta Flotante con Fotografía Auténtica -->
                                <div class="hero-slide-media">
                                    <div class="hero-media-card">
                                        <img src="{{ $slide->imagen_url }}" alt="{{ $slide->nombre_prod }}" class="hero-media-img" loading="eager">
                                        <span class="hero-media-cert-badge">✓ Certificado HUB</span>
                                        <div class="hero-media-overlay">
                                            <strong>Materiales:</strong> {{ Str::limit($slide->materiales_origen, 65) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Controles de Flechas -->
            <button type="button" class="hero-arrow-btn prev" id="hero-prev-btn" aria-label="Ver obra anterior">
                ❮
            </button>
            <button type="button" class="hero-arrow-btn next" id="hero-next-btn" aria-label="Ver siguiente obra">
                ❯
            </button>

            <!-- Dots de Navegación -->
            <div class="hero-dots-container" id="hero-dots-container">
                @foreach($heroSlides as $i => $s)
                    <button type="button" 
                            class="hero-dot {{ $i === 0 ? 'active' : '' }}" 
                            data-index="{{ $i }}" 
                            aria-label="Ir a diapositiva {{ $i + 1 }}"></button>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 2. Carrusel Interactivo: Todas las Obras y Ofertas de Temporada -->
    <section class="hero-carousel-section" id="carrusel-obras" aria-label="Carrusel con todas las obras y ofertas">
        <div class="container">
            <div class="carousel-header">
                <div>
                    <span class="section-tag" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span>✨</span> Vitrina en Vivo · Cusco a tu Hogar
                    </span>
                    <h2 class="section-main-title" style="margin-top: 4px; margin-bottom: 6px;">
                        Todas las Obras & Ofertas Especiales
                    </h2>
                    <p style="color: var(--color-charcoal-muted); font-size: 0.95rem; max-width: 620px;">
                        Explora todas las creaciones de nuestros talleres cusqueños: piezas de colección a precio regular y selecciones con descuento y comercio justo garantizado.
                    </p>
                </div>

                <!-- Controles del Carrusel (Flechas y Contador) -->
                <div class="carousel-controls-wrap">
                    <span class="carousel-counter-label" id="carousel-items-count">{{ $todosLosProductos->count() }} obras</span>
                    <div class="carousel-nav-buttons">
                        <button type="button" class="carousel-nav-btn prev" id="carousel-prev-btn" aria-label="Ver productos anteriores">
                            ❮
                        </button>
                        <button type="button" class="carousel-nav-btn next" id="carousel-next-btn" aria-label="Ver productos siguientes">
                            ❯
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros Rápidos del Carrusel -->
            <div class="carousel-filter-bar" role="tablist" aria-label="Filtrar carrusel por categoría">
                <button type="button" class="carousel-filter-btn active" data-filter="all" role="tab" aria-selected="true">
                    Todos ({{ $todosLosProductos->count() }})
                </button>
                <button type="button" class="carousel-filter-btn offer-filter" data-filter="ofertas" role="tab" aria-selected="false">
                    🔥 Solo Ofertas ({{ $todosLosProductos->where('en_oferta', true)->count() }})
                </button>
                <button type="button" class="carousel-filter-btn" data-filter="Textiles" role="tab" aria-selected="false">
                    Textiles ({{ $todosLosProductos->where('categoria', 'Textiles')->count() }})
                </button>
                <button type="button" class="carousel-filter-btn" data-filter="Platería" role="tab" aria-selected="false">
                    Platería ({{ $todosLosProductos->where('categoria', 'Platería')->count() }})
                </button>
                <button type="button" class="carousel-filter-btn" data-filter="Cerámica" role="tab" aria-selected="false">
                    Cerámica ({{ $todosLosProductos->where('categoria', 'Cerámica')->count() }})
                </button>
                <button type="button" class="carousel-filter-btn" data-filter="Decoración" role="tab" aria-selected="false">
                    Decoración ({{ $todosLosProductos->where('categoria', 'Decoración')->count() }})
                </button>
            </div>

            <!-- Track del Carrusel Deslizable -->
            <div class="carousel-track-wrapper">
                <div class="carousel-track" id="home-carousel-track">
                    @foreach($todosLosProductos as $prod)
                        <article class="product-card carousel-card {{ $prod->en_oferta ? 'is-offer' : '' }}" 
                                 data-category="{{ $prod->categoria }}"
                                 data-is-offer="{{ $prod->en_oferta ? 'true' : 'false' }}"
                                 aria-label="Pieza: {{ $prod->nombre_prod }}">
                            <div class="product-thumb-wrap">
                                <img src="{{ $prod->imagen_url }}" alt="Fotografía de {{ $prod->nombre_prod }}" class="product-thumb" loading="lazy">
                                @if($prod->en_oferta)
                                    <span class="product-tag-offer">
                                        🔥 -{{ $prod->porcentaje_descuento_calculado ?? 20 }}% {{ $prod->etiqueta_promocion ? '· ' . $prod->etiqueta_promocion : 'OFERTA' }}
                                    </span>
                                @else
                                    <span class="product-tag-regular">
                                        ✓ Colección
                                    </span>
                                @endif
                                <span class="product-tag-origin">📍 {{ $prod->artesano->direccion_arte }}</span>
                            </div>

                            <div class="product-card-body">
                                <div class="product-category-row">
                                    <span>{{ $prod->categoria }}</span>
                                    <span class="product-artisan-name">Por {{ $prod->artesano->nombre_completo }}</span>
                                </div>
                                <h3 class="product-title">
                                    <a href="{{ route('catalogo.show', $prod->id) }}">{{ $prod->nombre_prod }}</a>
                                </h3>
                                <p class="product-materials">
                                    <strong>Origen:</strong> {{ Str::limit($prod->materiales_origen, 65) }}
                                </p>

                                <div class="product-price-row">
                                    <div class="product-price-wrap" 
                                         data-price-pen="{{ $prod->precio_efectivo }}" 
                                         data-original-price-pen="{{ $prod->en_oferta ? $prod->precio : '' }}"
                                         data-is-offer="{{ $prod->en_oferta ? 'true' : 'false' }}">
                                        <div style="display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap;">
                                            @if($prod->en_oferta)
                                                <del class="price-original-del" title="Precio regular anterior">
                                                    @if(($monedaActiva ?? 'PEN') === 'USD')
                                                        $ {{ number_format($prod->precio / ($tipoCambio ?? 3.41), 2) }}
                                                    @else
                                                        S/ {{ number_format($prod->precio, 2) }}
                                                    @endif
                                                </del>
                                            @endif
                                            <span class="product-price-amount price-main {{ $prod->en_oferta ? 'price-offer' : '' }}">
                                                @if(($monedaActiva ?? 'PEN') === 'USD')
                                                    $ {{ number_format($prod->precio_efectivo / ($tipoCambio ?? 3.41), 2) }}
                                                @else
                                                    S/ {{ number_format($prod->precio_efectivo, 2) }}
                                                @endif
                                            </span>
                                            <span class="product-price-currency price-currency">
                                                {{ ($monedaActiva ?? 'PEN') === 'USD' ? 'USD' : 'PEN' }}
                                            </span>
                                        </div>
                                        <span class="price-secondary">
                                            @if(($monedaActiva ?? 'PEN') === 'USD')
                                                ≈ S/ {{ number_format($prod->precio_efectivo, 2) }} PEN
                                            @else
                                                ≈ $ {{ number_format($prod->precio_efectivo / ($tipoCambio ?? 3.41), 2) }} USD
                                            @endif
                                        </span>
                                    </div>
                                    <span style="font-size: 0.85rem; color: var(--color-fairtrade-green); font-weight: 700;">
                                        ★ {{ number_format($prod->puntuacion, 1) }}
                                    </span>
                                </div>

                                <div class="product-card-actions">
                                    <a href="{{ route('catalogo.show', $prod->id) }}" class="btn btn-secondary btn-sm">Ver Detalles</a>
                                    <button type="button" class="btn btn-accent btn-sm btn-quick-add" data-id="{{ $prod->id }}">
                                        + Carrito
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Bloque de Valor Cultural y HUB Logístico (3 Iconos de Impacto y Dropshipping) -->
    <section class="cultural-value-section" id="trazabilidad-cultural" aria-label="Ventajas y modelo del Portal del Artesano">
        <div class="container">
            <div class="section-title-wrap">
                <span class="section-tag">Modelo Innovador</span>
                <h2 class="section-main-title">¿Cómo Funciona el Portal del Artesano?</h2>
                <p style="color: var(--color-charcoal-muted); max-width: 650px; margin: 8px auto 0;">
                    Eliminamos los intermediarios abusivos y las limitaciones de equipaje para los viajeros, garantizando trazabilidad y autenticidad.
                </p>
            </div>

            <div class="value-grid">
                <!-- Icono 1: Comercio Justo y Showrooming -->
                <article class="value-card">
                    <div class="value-icon-box" aria-hidden="true">🤝</div>
                    <h3 class="value-card-title">Comercio Justo Directo</h3>
                    <p class="value-card-desc">
                        El valor de cada adquisición respalda directamente a los talleres familiares de Cusco sin intermediarios. En el showroom o web aprecias la obra con autenticidad y retribución justa.
                    </p>
                </article>

                <!-- Icono 2: Certificación de Autenticidad Digital -->
                <article class="value-card">
                    <div class="value-icon-box" aria-hidden="true">📜</div>
                    <h3 class="value-card-title">Certificado Digital de Autenticidad</h3>
                    <p class="value-card-desc">
                        Cada pieza posee un código único y trazabilidad verificada por el HUB. Conoce el nombre del maestro creador, su comunidad andina y los materiales ancestrales utilizados.
                    </p>
                </article>

                <!-- Icono 3: Dropshipping sin Peso en el Equipaje -->
                <article class="value-card">
                    <div class="value-icon-box" aria-hidden="true">✈️</div>
                    <h3 class="value-card-title">Viaja Sin Peso en el Equipaje</h3>
                    <p class="value-card-desc">
                        No cargues piezas pesadas ni frágiles durante tu viaje por Perú. El HUB logístico realiza el acopio, embalaje con seguro internacional y despacho directo a tu país.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <!-- 3. Categorías Destacadas -->
    <section class="container" style="padding-top: 60px;" aria-label="Categorías artesanales">
        <div class="section-title-wrap">
            <span class="section-tag">Herencia Viva</span>
            <h2 class="section-main-title">Especialidades de Nuestra Tierra</h2>
        </div>

        <div class="categories-grid">
            @foreach($categorias as $cat)
                <a href="{{ route('catalogo.index', ['categoria' => $cat['filtro'] ?? $cat['nombre']]) }}" class="category-card" aria-label="Ver piezas de {{ $cat['nombre'] }}">
                    <img src="{{ $cat['imagen'] }}" alt="Categoría {{ $cat['nombre'] }}" class="category-img" loading="lazy">
                    <div class="category-overlay">
                        <span class="category-name">{{ $cat['nombre'] }}</span>
                        @if(!empty($cat['badge']))
                            <span style="display: inline-block; background-color: var(--color-terracotta); color: #fff; font-size: 0.72rem; font-weight: 700; padding: 2px 10px; border-radius: var(--radius-pill); margin-top: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">
                                {{ $cat['badge'] }}
                            </span>
                        @endif
                        <span class="category-subtext">{{ $cat['subtitulo'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 4. Piezas Destacadas con Certificación -->
    <section class="container" style="padding-bottom: 60px;" aria-label="Piezas destacadas">
        <div class="section-title-wrap">
            <span class="section-tag">Selección Curada</span>
            <h2 class="section-main-title">Obras Maestras Recientes</h2>
            <p style="color: var(--color-charcoal-muted);">Piezas certificadas listas para envío nacional e internacional.</p>
        </div>

        <div class="products-grid">
            @foreach($destacados as $prod)
                <article class="product-card" aria-label="Pieza artesanal: {{ $prod->nombre_prod }}">
                    <div class="product-thumb-wrap">
                        <img src="{{ $prod->imagen_url }}" alt="Fotografía de {{ $prod->nombre_prod }}" class="product-thumb" loading="lazy">
                        @if($prod->en_oferta)
                            <span class="product-tag-offer">
                                🔥 -{{ $prod->porcentaje_descuento_calculado ?? 20 }}%
                            </span>
                        @endif
                        <span class="product-tag-origin">📍 {{ $prod->artesano->direccion_arte }}</span>
                        <span class="product-tag-cert" title="Certificado digital validado por el HUB">✓ HUB Certificado</span>
                    </div>
                    <div class="product-card-body">
                        <div class="product-category-row">
                            <span>{{ $prod->categoria }}</span>
                            <span class="product-artisan-name">Por {{ $prod->artesano->nombre_completo }}</span>
                        </div>
                        <h3 class="product-title">
                            <a href="{{ route('catalogo.show', $prod->id) }}">{{ $prod->nombre_prod }}</a>
                        </h3>
                        <p class="product-materials">
                            <strong>Origen:</strong> {{ $prod->materiales_origen }}
                        </p>
                        <div class="product-price-row">
                            <div class="product-price-wrap" 
                                 data-price-pen="{{ $prod->precio_efectivo }}" 
                                 data-original-price-pen="{{ $prod->en_oferta ? $prod->precio : '' }}"
                                 data-is-offer="{{ $prod->en_oferta ? 'true' : 'false' }}">
                                <div style="display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap;">
                                    @if($prod->en_oferta)
                                        <del class="price-original-del" title="Precio regular anterior">
                                            @if(($monedaActiva ?? 'PEN') === 'USD')
                                                $ {{ number_format($prod->precio / ($tipoCambio ?? 3.41), 2) }}
                                            @else
                                                S/ {{ number_format($prod->precio, 2) }}
                                            @endif
                                        </del>
                                    @endif
                                    <span class="product-price-amount price-main {{ $prod->en_oferta ? 'price-offer' : '' }}">
                                        @if(($monedaActiva ?? 'PEN') === 'USD')
                                            $ {{ number_format($prod->precio_efectivo / ($tipoCambio ?? 3.41), 2) }}
                                        @else
                                            S/ {{ number_format($prod->precio_efectivo, 2) }}
                                        @endif
                                    </span>
                                    <span class="product-price-currency price-currency">
                                        {{ ($monedaActiva ?? 'PEN') === 'USD' ? 'USD' : 'PEN' }}
                                    </span>
                                </div>
                                <span class="price-secondary">
                                    @if(($monedaActiva ?? 'PEN') === 'USD')
                                        ≈ S/ {{ number_format($prod->precio_efectivo, 2) }} PEN
                                    @else
                                        ≈ $ {{ number_format($prod->precio_efectivo / ($tipoCambio ?? 3.41), 2) }} USD
                                    @endif
                                </span>
                            </div>
                            <span style="font-size: 0.85rem; color: var(--color-fairtrade-green); font-weight: 700;">
                                ★ {{ number_format($prod->puntuacion, 1) }}
                            </span>
                        </div>
                        <div class="product-card-actions">
                            <a href="{{ route('catalogo.show', $prod->id) }}" class="btn btn-secondary btn-sm">Ver Detalles</a>
                            <button type="button" class="btn btn-accent btn-sm btn-quick-add" data-id="{{ $prod->id }}">
                                + Carrito
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div style="text-align: center; margin-top: 10px;">
            <a href="{{ route('catalogo.index') }}" class="btn btn-primary">Explorar Catálogo Completo ({{ \App\Models\Producto::aprobados()->count() }} obras) →</a>
        </div>
    </section>

    <!-- 5. Maestros Artesanos de Cusco (Inspirados en entrevistas reales) -->
    <section class="cultural-value-section" id="comunidades" style="background-color: var(--color-cream-surface);">
        <div class="container">
            <div class="section-title-wrap">
                <span class="section-tag">Creadores de Identidad</span>
                <h2 class="section-main-title">Maestros de los Barrios y Comunidades</h2>
            </div>

            <div class="value-grid">
                @foreach($artesanosDestacados as $artesano)
                    <article class="value-card" style="text-align: left;">
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                            <img src="{{ $artesano->foto_perfil }}" alt="Maestro {{ $artesano->nombre_completo }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-earth-primary);">
                            <div>
                                <h3 style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--color-charcoal-dark); margin-bottom: 2px;">
                                    {{ $artesano->nombre_completo }}
                                </h3>
                                <span style="font-size: 0.825rem; color: var(--color-earth-primary); font-weight: 600;">
                                    📍 {{ $artesano->direccion_arte }}
                                </span>
                            </div>
                        </div>
                        <p style="font-size: 0.88rem; color: var(--color-charcoal-muted); line-height: 1.5; margin-bottom: 14px;">
                            {{ Str::limit($artesano->historia_taller, 170) }}
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--color-cream-border); padding-top: 10px;">
                            <span class="badge badge-fairtrade">Taller Acreditado</span>
                            <a href="{{ route('catalogo.index', ['q' => $artesano->nombre]) }}" style="font-size: 0.85rem; font-weight: 600; color: var(--color-terracotta);">
                                Ver sus obras ({{ $artesano->productos_count }}) →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
