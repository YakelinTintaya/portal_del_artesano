@extends('layouts.app')

@section('title', 'Catálogo de Piezas de Arte Cusqueño | Portal del Artesano')

@section('content')
<div class="container">
    <!-- Breadcrumb accesible -->
    <nav aria-label="Ruta de navegación" style="padding: 18px 0 6px; font-size: 0.85rem; color: var(--color-charcoal-muted);">
        <ol style="display: flex; gap: 8px; list-style: none;">
            <li><a href="{{ route('home') }}" style="color: var(--color-earth-primary);">Inicio</a></li>
            <li>/</li>
            <li aria-current="page" style="color: var(--color-charcoal-dark); font-weight: 600;">Catálogo de Obras</li>
        </ol>
    </nav>

    <div class="catalog-layout">
        <!-- 1. Barra Lateral de Filtros Dinámicos -->
        <aside class="catalog-sidebar" aria-label="Filtros del catálogo artesanal">
            <div class="sidebar-title">
                <span>Filtros Dinámicos</span>
                <a href="{{ route('catalogo.index') }}" style="font-size: 0.8rem; font-family: var(--font-body); color: var(--color-terracotta);" title="Reiniciar todos los filtros">
                    Limpiar
                </a>
            </div>

            <!-- Búsqueda en tiempo real mediante JavaScript ES6 -->
            <div class="filter-group">
                <label for="catalog-search-input" class="filter-label">Buscar por pieza o artesano:</label>
                <div class="search-input-wrap">
                    <span class="search-icon-inside" aria-hidden="true">🔍</span>
                    <input type="search" 
                           id="catalog-search-input" 
                           class="search-input" 
                           placeholder="Ej. Alpaca, plata, San Blas..." 
                           value="{{ request('q') }}"
                           aria-label="Buscar piezas artesanales">
                </div>
            </div>

            <!-- Filtro de Categoría -->
            <div class="filter-group">
                <label for="filter-category" class="filter-label">Categoría Tradicional:</label>
                <select id="filter-category" class="filter-select" aria-label="Seleccionar categoría">
                    <option value="todas">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat }}" {{ strtolower(request('categoria', '')) === strtolower($cat) ? 'selected' : '' }}>
                            {{ $cat === 'Ofertas' ? '🔥 Ofertas & Promociones' : $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro de Tamaño -->
            <div class="filter-group">
                <label for="filter-size" class="filter-label">Dimensión / Tamaño:</label>
                <select id="filter-size" class="filter-select" aria-label="Seleccionar dimensión">
                    <option value="todos">Todos los tamaños</option>
                    @foreach($tamanos as $tam)
                        <option value="{{ $tam }}" {{ request('tamano') === $tam ? 'selected' : '' }}>
                            {{ $tam }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro de Rango de Precio en Soles (PEN) -->
            <div class="filter-group">
                <label class="filter-label">Rango de Precio Justo (S/):</label>
                <div class="price-inputs-row">
                    <input type="number" 
                           id="filter-price-min" 
                           class="price-input" 
                           placeholder="Mín S/" 
                           min="0" 
                           value="{{ request('precio_min') }}"
                           aria-label="Precio mínimo en soles">
                    <input type="number" 
                           id="filter-price-max" 
                           class="price-input" 
                           placeholder="Máx S/" 
                           min="0" 
                           value="{{ request('precio_max') }}"
                           aria-label="Precio máximo en soles">
                </div>
            </div>

            <!-- Ordenamiento -->
            <div class="filter-group">
                <label for="filter-sort" class="filter-label">Ordenar catálogo por:</label>
                <select id="filter-sort" class="filter-select" aria-label="Criterio de ordenamiento">
                    <option value="recientes" {{ request('sort') === 'recientes' ? 'selected' : '' }}>Nuevas creaciones</option>
                    <option value="precio_asc" {{ request('sort') === 'precio_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                    <option value="precio_desc" {{ request('sort') === 'precio_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                    <option value="puntuacion" {{ request('sort') === 'puntuacion' ? 'selected' : '' }}>Mejor valoración</option>
                </select>
            </div>

            <div style="background-color: var(--color-fairtrade-light); padding: 14px; border-radius: var(--radius-sm); border: 1px solid rgba(46,111,64,0.2); font-size: 0.8rem; color: var(--color-fairtrade-green);">
                🛡️ <strong>Garantía HUB Cusco:</strong> Cada obra incluye certificado digital y trazabilidad del taller de origen.
            </div>
        </aside>

        <!-- 2. Grilla Responsiva de Productos (CSS Grid) -->
        <section aria-label="Resultados del catálogo">
            <header class="products-header-bar">
                <h1 style="font-family: var(--font-serif); font-size: 1.6rem; color: var(--color-charcoal-dark);">
                    Piezas de Arte Tradicional Cusqueño
                </h1>
                <div id="catalog-results-count" style="font-size: 0.9rem; color: var(--color-charcoal-muted); font-weight: 500;" aria-live="polite">
                    Mostrando {{ $productos->total() }} obras auténticas
                </div>
            </header>

            <div class="products-grid" id="catalog-products-grid">
                @forelse($productos as $prod)
                    <article class="product-card" aria-label="Pieza artesanal: {{ $prod->nombre_prod }}">
                        <div class="product-thumb-wrap">
                            <img src="{{ $prod->imagen_url }}" 
                                 alt="Fotografía auténtica de la obra: {{ $prod->nombre_prod }}" 
                                 class="product-thumb" 
                                 loading="lazy">
                            @if($prod->en_oferta)
                                <span class="product-tag-offer">
                                    🔥 -{{ $prod->porcentaje_descuento_calculado ?? 20 }}%
                                </span>
                            @endif
                            <span class="product-tag-origin">
                                📍 {{ $prod->artesano->direccion_arte }}
                            </span>
                            <span class="product-tag-cert" title="Certificado de Autenticidad Digital HUB">
                                ✓ Auténtico
                            </span>
                        </div>
                        <div class="product-card-body">
                            <div class="product-category-row">
                                <span>{{ $prod->categoria }}</span>
                                <span class="product-artisan-name">Por {{ $prod->artesano->nombre_completo }}</span>
                            </div>
                            <h2 class="product-title">
                                <a href="{{ route('catalogo.show', $prod->id) }}">{{ $prod->nombre_prod }}</a>
                            </h2>
                            <p class="product-materials">
                                <strong>Materiales:</strong> {{ $prod->materiales_origen }}
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
                                <a href="{{ route('catalogo.show', $prod->id) }}" 
                                   class="btn btn-secondary btn-sm" 
                                   aria-label="Ver detalles y trazabilidad de {{ $prod->nombre_prod }}">
                                    Detalles
                                </a>
                                <button type="button" 
                                        class="btn btn-accent btn-sm btn-quick-add" 
                                        data-id="{{ $prod->id }}" 
                                        aria-label="Añadir {{ $prod->nombre_prod }} al carrito de compras">
                                    + Carrito
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                        <p style="font-size: 1.2rem; color: var(--color-charcoal-muted); margin-bottom: 12px;">
                            No se encontraron piezas artesanales con los criterios solicitados.
                        </p>
                        <a href="{{ route('catalogo.index') }}" class="btn btn-primary btn-sm">Restablecer Catálogo</a>
                    </div>
                @endforelse
            </div>

            <!-- Paginación del catálogo artesanal -->
            <div class="catalog-pagination-wrap">
                {{ $productos->links() }}
            </div>
        </section>
    </div>
</div>
@endsection
