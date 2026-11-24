@extends('layouts.app')

@section('title', 'Dedicatoria Digital & Certificado de Autenticidad | Portal del Artesano Cusco')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Breadcrumb & Botones de Acción Superior -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <nav aria-label="Navegación" style="font-size: 0.9rem; color: var(--color-charcoal-muted);">
            <a href="{{ route('home') }}">Inicio</a> &gt; 
            <span>Dedicatoria Digital</span> &gt;
            <span style="font-family: monospace; color: var(--color-earth-primary);">{{ $pedido->codigo_seguimiento }}</span>
        </nav>

        <div style="display: flex; gap: 10px; align-items: center;" class="no-print">
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                🖨️ Imprimir Tarjeta de Regalo
            </button>
            <button type="button" class="btn btn-outline-earth btn-sm" onclick="copiarEnlaceDedicatoria()">
                🔗 Copiar Enlace
            </button>
            @auth
                @if(Auth::user()->isArtesano())
                    <a href="{{ route('artesano.dashboard') }}" class="btn btn-secondary btn-sm">
                        ← Panel Artesano
                    </a>
                @elseif(Auth::user()->isAdminHub())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                        ← Panel HUB Admin
                    </a>
                @else
                    <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-secondary btn-sm">
                        ← Ver Mi Pedido
                    </a>
                @endif
            @endauth
        </div>
    </div>

    <!-- TARJETA PRINCIPAL DE DEDICATORIA ARTESANAL (DISEÑO TIPO PERGAMINO CUSQUEÑO) -->
    <div class="dedicatoria-card-container">
        <!-- Cabecera de la Tarjeta con Sello Andino -->
        <div class="dedicatoria-header">
            <div class="dedicatoria-seal">
                <span class="seal-icon">✦</span>
                <span class="seal-text">PORTAL DEL ARTESANO • CUSCO - PERÚ</span>
                <span class="seal-icon">✦</span>
            </div>
            <h1 class="dedicatoria-title">Dedicatoria Especial & Certificación Digital</h1>
            <p class="dedicatoria-subtitle">
                Una pieza del alma milenaria de los Andes, enviada con amor y respeto por el comercio justo.
            </p>
        </div>

        <div class="dedicatoria-grid">
            <!-- Columna Izquierda: Mensaje y Detalles -->
            <div class="dedicatoria-body">
                <div class="dedicatoria-message-box">
                    <div class="dedicatoria-quotes">“</div>
                    <p class="dedicatoria-message-text">
                        {{ $pedido->mensaje_regalo ?: 'Que este pedazo de historia, tejido con devoción en el ombligo del mundo, ilumine tu hogar y te recuerde la magia eterna del Cusco.' }}
                    </p>
                    <div class="dedicatoria-quotes-close">”</div>
                </div>

                <div class="dedicatoria-meta-grid">
                    <div class="dedicatoria-meta-item">
                        <span class="meta-label">De:</span>
                        <strong class="meta-val">{{ $pedido->cliente->nombre_completo }}</strong>
                    </div>
                    <div class="dedicatoria-meta-item">
                        <span class="meta-label">Destino:</span>
                        <strong class="meta-val">{{ $pedido->pais_destino }}</strong>
                    </div>
                    <div class="dedicatoria-meta-item">
                        <span class="meta-label">Fecha:</span>
                        <strong class="meta-val">{{ $pedido->created_at->format('d/m/Y') }}</strong>
                    </div>
                    <div class="dedicatoria-meta-item">
                        <span class="meta-label">Código Tracking:</span>
                        <strong class="meta-val" style="font-family: monospace; color: var(--color-earth-primary);">{{ $pedido->codigo_seguimiento }}</strong>
                    </div>
                </div>

                <div class="dedicatoria-quechua-box">
                    <span style="font-weight: 700; color: #8A5A1B;">«Sumaq sonqowan»</span>
                    <span style="font-size: 0.85rem; color: #6E543C; margin-left: 6px;">
                        (En Quechua: <em>"Hecho y entregado con un hermoso corazón"</em>)
                    </span>
                </div>
            </div>

            <!-- Columna Derecha: Código QR Oficial de la Dedicatoria -->
            <div class="dedicatoria-qr-panel">
                <div class="qr-stamp-box">
                    <div class="qr-title-badge">CÓDIGO QR DIGITAL</div>
                    <div class="qr-image-wrapper">
                        <img src="{{ $pedido->qr_dedicatoria_url }}" 
                             alt="QR Dedicatoria Digital - {{ $pedido->codigo_seguimiento }}"
                             class="qr-code-img">
                    </div>
                    <p class="qr-instructions">
                        Escanea este código con la cámara de tu celular para abrir esta dedicatoria interactiva en cualquier momento.
                    </p>
                    <div class="qr-status-pill">
                        ✓ Autenticidad Verificada en Blockchain / HUB Cusco
                    </div>
                </div>
            </div>
        </div>

        <!-- Obras Artesanales que componen este Regalo -->
        <div class="dedicatoria-products-section">
            <h2 class="products-section-title">
                Piezas de Arte Tradicional Incluidas en este Envío
            </h2>
            <div class="dedicatoria-products-grid">
                @foreach($pedido->productos as $item)
                    <div class="gift-prod-card">
                        <img src="{{ $item->imagen_url }}" alt="{{ $item->nombre_prod }}" class="gift-prod-img">
                        <div class="gift-prod-info">
                            <h3 class="gift-prod-name">{{ $item->nombre_prod }}</h3>
                            <div class="gift-prod-artisan">
                                Maestro: <strong>{{ $item->artesano->nombre_completo }}</strong>
                            </div>
                            <div class="gift-prod-location">
                                📍 {{ $item->artesano->direccion_arte }}
                            </div>
                            <div class="gift-prod-materials">
                                <strong>Materiales:</strong> {{ $item->materiales_origen }}
                            </div>
                            <div class="gift-prod-cert">
                                Certificado HUB: <code>{{ $item->certificado_autenticidad_codigo }}</code>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pie de Certificación y Trazabilidad -->
        <div class="dedicatoria-footer-seal">
            <div style="display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <span>🛡️ <strong>HUB Logístico San Blas - Cusco</strong></span>
                <span>•</span>
                <span>🤝 <strong>Apoyo Directo a las Familias Artesanas</strong></span>
                <span>•</span>
                <span>✈️ <strong>Embalaje Protegido y Certificado</strong></span>
            </div>
        </div>
    </div>
</div>

<script>
function copiarEnlaceDedicatoria() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('¡Enlace de la dedicatoria copiado al portapapeles! Puedes compartirlo por WhatsApp o correo.');
    }).catch(() => {
        prompt('Copia el siguiente enlace:', url);
    });
}
</script>

<style>
/* Estilos exclusivos para la Tarjeta de Dedicatoria Digital */
.dedicatoria-card-container {
    background: #FFFDF9;
    border: 2px solid #D4A359;
    border-radius: 16px;
    padding: 36px 40px;
    box-shadow: 0 10px 30px rgba(84, 51, 16, 0.08);
    position: relative;
    max-width: 960px;
    margin: 0 auto;
}

.dedicatoria-header {
    text-align: center;
    border-bottom: 2px solid #EFE4D2;
    padding-bottom: 24px;
    margin-bottom: 28px;
}

.dedicatoria-seal {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 2px;
    color: #8C531B;
    background: #F8EED9;
    padding: 6px 16px;
    border-radius: 30px;
    border: 1px solid #D4A359;
    margin-bottom: 12px;
}

.dedicatoria-title {
    font-family: var(--font-serif);
    font-size: 2.1rem;
    color: #3B2613;
    margin: 8px 0;
}

.dedicatoria-subtitle {
    font-size: 1rem;
    color: #7A624E;
    max-width: 680px;
    margin: 0 auto;
}

.dedicatoria-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 32px;
    align-items: center;
}

.dedicatoria-message-box {
    background: #FDF9F0;
    border-left: 4px solid #C88A2C;
    border-radius: 8px;
    padding: 20px 24px;
    position: relative;
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.02);
}

.dedicatoria-quotes {
    font-size: 2.5rem;
    line-height: 1;
    color: #C88A2C;
    font-family: Georgia, serif;
}

.dedicatoria-quotes-close {
    font-size: 2.5rem;
    line-height: 1;
    color: #C88A2C;
    font-family: Georgia, serif;
    text-align: right;
    margin-top: -10px;
}

.dedicatoria-message-text {
    font-family: var(--font-serif);
    font-size: 1.25rem;
    font-style: italic;
    line-height: 1.7;
    color: #2F1E0F;
    margin: 8px 0;
}

.dedicatoria-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
    background: #FAF5EB;
    padding: 16px;
    border-radius: 8px;
    border: 1px solid #EADBCC;
}

.dedicatoria-meta-item {
    font-size: 0.9rem;
}

.meta-label {
    display: block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #8C735E;
    font-weight: 600;
}

.meta-val {
    color: #382414;
    font-size: 0.95rem;
}

.dedicatoria-quechua-box {
    margin-top: 16px;
    padding: 10px 14px;
    background: #F5EADB;
    border-radius: 6px;
    display: inline-block;
    border-left: 3px solid #8A5A1B;
}

.dedicatoria-qr-panel {
    text-align: center;
}

.qr-stamp-box {
    background: #FFFFFF;
    border: 2px dashed #C88A2C;
    border-radius: 12px;
    padding: 20px 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
}

.qr-title-badge {
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    color: #C88A2C;
    margin-bottom: 12px;
}

.qr-image-wrapper {
    background: #FAF8F5;
    padding: 12px;
    display: inline-block;
    border-radius: 8px;
    border: 1px solid #EFE4D2;
}

.qr-code-img {
    width: 190px;
    height: 190px;
    display: block;
    border-radius: 4px;
}

.qr-instructions {
    font-size: 0.8rem;
    color: #7A624E;
    margin-top: 12px;
    line-height: 1.4;
}

.qr-status-pill {
    margin-top: 10px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #2E6B38;
    background: #E8F5E9;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-block;
}

.dedicatoria-products-section {
    margin-top: 36px;
    border-top: 2px solid #EFE4D2;
    padding-top: 28px;
}

.products-section-title {
    font-family: var(--font-serif);
    font-size: 1.35rem;
    color: #382414;
    margin-bottom: 20px;
    text-align: center;
}

.dedicatoria-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.gift-prod-card {
    background: #FAF6ED;
    border: 1px solid #E7DBCB;
    border-radius: 10px;
    padding: 16px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
}

.gift-prod-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #D8C6B1;
    flex-shrink: 0;
}

.gift-prod-info {
    flex: 1;
    font-size: 0.85rem;
}

.gift-prod-name {
    font-family: var(--font-serif);
    font-size: 1rem;
    color: #2D1D0F;
    margin: 0 0 4px 0;
}

.gift-prod-artisan {
    color: #5C4533;
    margin-bottom: 2px;
}

.gift-prod-location {
    color: #8C735E;
    font-size: 0.78rem;
    margin-bottom: 4px;
}

.gift-prod-materials {
    font-size: 0.78rem;
    color: #5C4533;
    margin-bottom: 6px;
}

.gift-prod-cert code {
    font-family: monospace;
    font-size: 0.75rem;
    background: #E8DDCD;
    color: #8A4614;
    padding: 2px 6px;
    border-radius: 4px;
}

.dedicatoria-footer-seal {
    margin-top: 32px;
    border-top: 1px solid #EFE4D2;
    padding-top: 18px;
    font-size: 0.85rem;
    color: #7A624E;
    text-align: center;
}

/* Modo Impresión (Tarjeta de regalo imprimible) */
@media print {
    .site-header, .site-footer, .announcement-bar, .no-print, nav {
        display: none !important;
    }
    body {
        background: #FFFFFF !important;
        color: #000000 !important;
    }
    .dedicatoria-card-container {
        border: 2px solid #333333 !important;
        box-shadow: none !important;
        padding: 20px !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}

@media (max-width: 768px) {
    .dedicatoria-card-container {
        padding: 24px 18px;
    }
    .dedicatoria-grid {
        grid-template-columns: 1fr;
    }
    .dedicatoria-title {
        font-size: 1.6rem;
    }
}
</style>
@endsection
