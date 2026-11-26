<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal del Artesano | Comercio Justo & Dropshipping Cusco')</title>
    <meta name="description" content="@yield('meta_description', 'Plataforma de comercio electrónico y showrooming con impacto social. Conectamos directamente a los artesanos de Cusco (San Blas, Pisac, Chinchero, San Pedro) con compradores internacionales, respaldados por el HUB logístico de certificación digital.')">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="google-site-verification" content="googlea031ed47a730fcf4">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Metadatos Open Graph (Facebook, WhatsApp, LinkedIn) -->
    <meta property="og:locale" content="es_PE">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Portal del Artesano Cusco">
    <meta property="og:title" content="@yield('title', 'Portal del Artesano | Comercio Justo & Dropshipping Cusco')">
    <meta property="og:description" content="@yield('meta_description', 'Conectamos a artesanos cusqueños con el mundo con autenticidad certificada y comercio justo.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('meta_image', asset('images/og-cover.jpg'))">

    <!-- Metadatos Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Portal del Artesano Cusco')">
    <meta name="twitter:description" content="@yield('meta_description', 'Arte tradicional andino auténtico certificado directamente desde los talleres de Cusco.')">
    <meta name="twitter:image" content="@yield('meta_image', asset('images/og-cover.jpg'))">

    <!-- Datos Estructurados JSON-LD (Schema.org) para Google Search -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "Portal del Artesano Cusco",
        "url": "{{ url('/') }}",
        "description": "Showrooming y comercio justo directo de piezas artesanales de Cusco al mundo",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": "{{ route('catalogo.index') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <!-- Tipografía optimizada y estilos modulares (con cache busting) -->
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}?v={{ time() }}">
    <script src="{{ asset('js/app.js') }}?v={{ time() }}" defer></script>
    <script>
        window.PORTAL_CURRENCY = {
            active: "{{ $monedaActiva ?? 'PEN' }}",
            rate: {{ $tipoCambio ?? 3.75 }},
            changeUrl: "{{ route('currency.set') }}",
            rateUrl: "{{ route('currency.rate') }}"
        };
        window.PORTAL_ROUTES = {
            cartAdd: "{{ route('carrito.add') }}",
            cartUpdate: "{{ route('carrito.update') }}",
            cartRemove: "{{ route('carrito.remove') }}",
            cartClear: "{{ route('carrito.clear') }}",
            catalog: "{{ route('catalogo.index') }}",
            @auth
                @if(Auth::user()->isArtesano() || Auth::user()->isAdminHub())
                    checkArtesanoOrders: "{{ route('artesano.pedidos.verificar') }}",
                @endif
            @endauth
        };
        window.PORTAL_USER = {
            id: {{ Auth::id() ?? 'null' }},
            isArtesano: {{ Auth::check() && (Auth::user()->isArtesano() || Auth::user()->isAdminHub()) ? 'true' : 'false' }}
        };
    </script>
    @stack('styles')
</head>
<body>
    <!-- Enlace de salto accesible para lectores de pantalla -->
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

    <!-- 1. Barra de Anuncio de Envío Internacional Seguro -->
    <aside class="announcement-bar" aria-label="Información de despacho internacional y HUB">
        <div class="container">
            <div>
                ✈️ <span class="announcement-highlight">Envío Internacional Seguro desde Cusco:</span> Lleva el arte andino a tu país sin exceso de equipaje ni intermediarios.
            </div>
            <div class="announcement-links">
                <span>📍 HUB Acopio: San Blas - Cusco</span>
                <span>🛡️ Certificación Digital 100% Auténtica</span>
            </div>
        </div>
    </aside>

    <!-- 2. Cabecera y Navegación Principal -->
    <header class="site-header" role="banner">
        <div class="container nav-container">
            <!-- Logotipo del Portal -->
            <a href="{{ route('home') }}" class="site-logo" aria-label="Portal del Artesano Cusco - Ir a página de inicio">
                <span class="logo-icon" aria-hidden="true">❖</span>
                <span>Portal del Artesano</span>
            </a>

            <!-- Navegación Semántica -->
            <nav class="site-nav" role="navigation" aria-label="Menú principal de navegación">
                <ul class="site-nav-links">
                    <li>
                        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
                    </li>
                    <li>
                        <a href="{{ route('catalogo.index') }}" class="{{ request()->routeIs('catalogo.*') ? 'active' : '' }}">Catálogo de Obras</a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}#trazabilidad-cultural">Trazabilidad & Comercio Justo</a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}#comunidades">Comunidades de Cusco</a>
                    </li>
                </ul>
            </nav>

            <!-- Acciones de Usuario y Carrito de Compras -->
            <div class="nav-actions">
                <!-- Selector de Moneda: Soles (S/) / Dólares ($) con Tipo de Cambio en Vivo -->
                <div class="currency-switch-wrapper" aria-label="Selector de divisa">
                    <div class="currency-toggle-group" id="currency-toggle-group" role="group" aria-label="Cambiar entre Soles y Dólares">
                        <button type="button" 
                                class="currency-toggle-btn {{ ($monedaActiva ?? 'PEN') === 'PEN' ? 'active' : '' }}" 
                                data-currency="PEN" 
                                title="Mostrar precios en Soles (S/)">
                            S/
                        </button>
                        <button type="button" 
                                class="currency-toggle-btn {{ ($monedaActiva ?? 'PEN') === 'USD' ? 'active' : '' }}" 
                                data-currency="USD" 
                                title="Mostrar precios en Dólares ($)">
                            $
                        </button>
                    </div>
                    <span class="currency-rate-chip" id="currency-rate-chip" title="Tipo de cambio del día actualizado en tiempo real">
                        T.C. $1 ≈ S/ {{ number_format($tipoCambio ?? 3.41, 2) }}
                    </span>
                </div>

                <!-- Acciones de Usuario en Escritorio (ocultas en móviles/tablets <= 991px) -->
                <div class="desktop-user-actions">
                    @auth
                        @if(Auth::user()->isAdminHub())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-earth btn-sm" aria-label="Ir al panel del HUB logístico">
                                🏛️ Panel HUB
                            </a>
                            <a href="{{ route('admin.chats.resumen') }}" class="btn btn-secondary btn-sm" title="Supervisión de chats de compradores y artesanos">
                                💬 Chats HUB
                            </a>
                        @elseif(Auth::user()->isArtesano())
                            <a href="{{ route('artesano.dashboard') }}" class="btn btn-outline-earth btn-sm" aria-label="Ir al taller del artesano">
                                🛠️ Mi Taller
                            </a>
                            <a href="{{ route('chat.index') }}" class="btn btn-secondary btn-sm" title="Mensajes con compradores">
                                💬 Mensajes
                            </a>
                        @else
                            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary btn-sm" aria-label="Ver mis pedidos">
                                📦 Mis Pedidos
                            </a>
                            <a href="{{ route('chat.index') }}" class="btn btn-outline-earth btn-sm" title="Mensajes con artesanos">
                                💬 Mensajes
                            </a>
                        @endif

                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm" title="Cerrar sesión" aria-label="Cerrar sesión de {{ Auth::user()->nombre }}">
                                Salir
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Registrarse</a>
                    @endauth
                </div>

                <!-- Carrito de Compras con Contador Reactivo -->
                @php
                    $sessionCart = session()->get('carrito', []);
                    $cartCount = array_sum(array_column($sessionCart, 'cantidad'));
                @endphp
                <a href="{{ route('carrito.index') }}" class="btn-cart-nav" aria-label="Carrito de compras con {{ $cartCount }} artículos">
                    <span aria-hidden="true">🛒</span>
                    <span class="btn-cart-label">Carrito</span>
                    <span class="cart-counter" id="nav-cart-counter">{{ $cartCount }}</span>
                </a>

                <!-- Botón de Menú Hamburguesa para Móvil y Tablet (<= 991px) -->
                <button type="button" 
                        class="mobile-nav-toggle" 
                        id="mobile-nav-toggle" 
                        aria-label="Abrir menú de navegación" 
                        aria-expanded="false" 
                        aria-controls="mobile-nav-drawer">
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Overlay y Drawer Deslizable de Navegación Móvil -->
    <div class="mobile-drawer-overlay" id="mobile-drawer-overlay" aria-hidden="true"></div>
    <aside class="mobile-nav-drawer" id="mobile-nav-drawer" role="dialog" aria-modal="true" aria-label="Menú principal de navegación móvil">
        <div class="mobile-drawer-header">
            <div class="mobile-drawer-brand">
                <span class="logo-icon" aria-hidden="true">❖</span>
                <span class="brand-text">Portal del Artesano</span>
            </div>
            <button type="button" class="mobile-drawer-close" id="mobile-drawer-close" aria-label="Cerrar menú">
                ✕
            </button>
        </div>

        @auth
            <div class="mobile-drawer-user-info">
                <div class="user-avatar-circle">
                    {{ strtoupper(substr(Auth::user()->nombre, 0, 1)) }}
                </div>
                <div class="user-details">
                    <span class="user-name">{{ Auth::user()->nombre }} {{ Auth::user()->apellido }}</span>
                    <span class="user-badge">
                        @if(Auth::user()->isAdminHub())
                            🏛️ Administrador HUB Logístico
                        @elseif(Auth::user()->isArtesano())
                            🛠️ Maestro Artesano (Taller)
                        @else
                            👤 Cliente Comprador
                        @endif
                    </span>
                </div>
            </div>
        @endauth

        <nav class="mobile-drawer-nav" aria-label="Enlaces de navegación móvil">
            <ul class="mobile-nav-list">
                <li>
                    <a href="{{ route('home') }}" class="mobile-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <span class="nav-icon">🏠</span> Inicio
                    </a>
                </li>
                <li>
                    <a href="{{ route('catalogo.index') }}" class="mobile-nav-link {{ request()->routeIs('catalogo.*') ? 'active' : '' }}">
                        <span class="nav-icon">🏺</span> Catálogo de Obras
                    </a>
                </li>
                <li>
                    <a href="{{ route('home') }}#trazabilidad-cultural" class="mobile-nav-link">
                        <span class="nav-icon">📜</span> Trazabilidad & Comercio Justo
                    </a>
                </li>
                <li>
                    <a href="{{ route('home') }}#comunidades" class="mobile-nav-link">
                        <span class="nav-icon">🏔️</span> Comunidades de Cusco
                    </a>
                </li>
                <li>
                    <a href="{{ route('carrito.index') }}" class="mobile-nav-link {{ request()->routeIs('carrito.*') ? 'active' : '' }}">
                        <span class="nav-icon">🛒</span> Mi Carrito
                        <span class="cart-counter" style="position: static; margin-left: auto;">{{ $cartCount }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('seguridad.labs') }}" class="mobile-nav-link {{ request()->routeIs('seguridad.labs') ? 'active' : '' }}">
                        <span class="nav-icon">🛡️</span> Lab de Seguridad
                    </a>
                </li>
            </ul>
        </nav>

        <div class="mobile-drawer-divider"></div>

        <!-- Acciones de Cuenta y Sesión en Móvil -->
        <div class="mobile-drawer-actions">
            @auth
                @if(Auth::user()->isAdminHub())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-earth btn-block">
                        🏛️ Panel HUB Logístico
                    </a>
                    <a href="{{ route('admin.chats.resumen') }}" class="btn btn-secondary btn-block">
                        💬 Supervisión Chats HUB
                    </a>
                @elseif(Auth::user()->isArtesano())
                    <a href="{{ route('artesano.dashboard') }}" class="btn btn-outline-earth btn-block">
                        🛠️ Mi Taller Artesanal
                    </a>
                    <a href="{{ route('chat.index') }}" class="btn btn-secondary btn-block">
                        💬 Mensajes con Compradores
                    </a>
                @else
                    <a href="{{ route('pedidos.index') }}" class="btn btn-secondary btn-block">
                        📦 Mis Pedidos
                    </a>
                    <a href="{{ route('chat.index') }}" class="btn btn-outline-earth btn-block">
                        💬 Mensajes con Artesanos
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST" style="margin-top: 12px;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-block" style="color: var(--color-danger); border-color: rgba(184, 51, 42, 0.35);">
                        Cerrar Sesión
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-secondary btn-block">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-block">Registrarse</a>
                <div style="text-align: center; margin-top: 12px;">
                    <a href="{{ route('register') }}" style="font-size: 0.85rem; color: var(--color-earth-primary); text-decoration: underline;">
                        ¿Eres artesano? Registra tu taller aquí
                    </a>
                </div>
            @endauth
        </div>

        <div class="mobile-drawer-footer">
            <span class="drawer-footer-note">📍 HUB San Blas &bull; Cusco, Perú</span>
            <span class="drawer-footer-note">T.C. $1 ≈ S/ {{ number_format($tipoCambio ?? 3.41, 2) }}</span>
        </div>
    </aside>

    <!-- 3. Alertas Flash del Sistema -->
    <div class="container" style="margin-top: 20px;">
        @if(session('success'))
            <div class="alert alert-success" role="alert" aria-live="polite">
                <span>✓</span> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" role="alert" aria-live="assertive">
                <span>⚠️</span> {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning" role="alert">
                <span>ℹ️</span> {{ session('warning') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info" role="alert">
                <span>ℹ️</span> {{ session('info') }}
            </div>
        @endif
    </div>

    <!-- 4. Contenido Principal Dinámico -->
    <main id="main-content" role="main">
        @yield('content')
    </main>

    <!-- 5. Pie de Página Semántico -->
    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3 class="footer-title">Portal del Artesano Cusco</h3>
                    <p style="font-size: 0.9rem; color: rgba(245, 242, 235, 0.8); line-height: 1.6; margin-bottom: 14px;">
                        Plataforma de dropshipping y showrooming cultural. Apoyamos el comercio justo y directo para los maestros artesanos de Cusco, integrando acopio certificado y despacho internacional seguro mediante nuestro HUB logístico.
                    </p>
                    <span class="badge badge-fairtrade">Comercio Justo y Retribución Directa</span>
                </div>

                <div>
                    <h4 class="footer-title">Comunidades Aliadas</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('catalogo.index', ['q' => 'San Blas']) }}">Barrio Tradicional de San Blas</a></li>
                        <li><a href="{{ route('catalogo.index', ['q' => 'Pisac']) }}">Valle Sagrado de Pisac</a></li>
                        <li><a href="{{ route('catalogo.index', ['q' => 'Chinchero']) }}">Asociaciones de Chinchero</a></li>
                        <li><a href="{{ route('catalogo.index', ['q' => 'San Pedro']) }}">Mercado Central de San Pedro</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Enlaces Directos</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('catalogo.index') }}">Catálogo Completo</a></li>
                        <li><a href="{{ route('carrito.index') }}">Carrito de Compras</a></li>
                        <li><a href="{{ route('seguridad.labs') }}">🛡️ Laboratorio de Seguridad (PortSwigger)</a></li>
                        <li><a href="{{ route('register') }}">Registrar mi Taller</a></li>
                        <li><a href="{{ route('login') }}">Acceso HUB Logístico</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Garantía & Seguridad</h4>
                    <p style="font-size: 0.85rem; color: rgba(245, 242, 235, 0.75); margin-bottom: 12px;">
                        Cada pieza cuenta con un <strong>Certificado Digital de Autenticidad</strong> verificado en taller y embalaje especial para viajes transoceánicos.
                    </p>
                    <p style="font-size: 0.8rem; color: rgba(245, 242, 235, 0.65);">
                        Cumplimiento estricto con la <strong>Ley N.º 29733</strong> de Protección de Datos Personales de la República del Perú.
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; {{ date('Y') }} Portal del Artesano Cusco. Todos los derechos reservados. Desarrollado con Laravel 11.
                </div>
                <div>
                    Hecho con orgullo en los Andes peruanos 🇵🇪
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts Modulares Vanilla ES6 -->
    @stack('scripts')
</body>
</html>
