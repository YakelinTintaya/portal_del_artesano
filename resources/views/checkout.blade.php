@extends('layouts.app')

@section('title', 'Finalizar Compra Segura (Purchase) | Portal del Artesano')

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px;">
    <!-- Breadcrumb -->
    <nav aria-label="Ruta de navegación" style="margin-bottom: 20px; font-size: 0.85rem; color: var(--color-charcoal-muted);">
        <ol style="display: flex; gap: 8px; list-style: none;">
            <li><a href="{{ route('home') }}" style="color: var(--color-earth-primary);">Inicio</a></li>
            <li>/</li>
            <li><a href="{{ route('carrito.index') }}" style="color: var(--color-earth-primary);">Carrito de Compras</a></li>
            <li>/</li>
            <li aria-current="page" style="color: var(--color-charcoal-dark); font-weight: 600;">Checkout & Envío Internacional</li>
        </ol>
    </nav>

    <h1 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-charcoal-dark); margin-bottom: 24px;">
        Confirmación de Compra y Certificación de Envío
    </h1>

    <div class="checkout-grid">
        <!-- Columna 1: Formulario de Despacho y Pago -->
        <section class="checkout-form-card" aria-label="Formulario de datos de envío y pago">
            <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
                @csrf

                <!-- 1. Datos del Comprador -->
                <h2 class="form-section-title">
                    <span>1. Información del Destinatario</span>
                </h2>

                <div class="form-row-2col" style="margin-bottom: 16px;">
                    <div>
                        <label class="form-label" for="nombre_comprador">Nombres:</label>
                        <input type="text" id="nombre_comprador" class="form-control" value="{{ $user->nombre }}" readonly style="background-color: #EEE;">
                    </div>
                    <div>
                        <label class="form-label" for="apellido_comprador">Apellidos:</label>
                        <input type="text" id="apellido_comprador" class="form-control" value="{{ $user->apellido }}" readonly style="background-color: #EEE;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="correo_comprador">Correo de Notificación y Certificados:</label>
                    <input type="email" id="correo_comprador" class="form-control" value="{{ $user->correo ?? $user->email }}" readonly style="background-color: #EEE;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono_contacto">Teléfono de Contacto (con código de país) *:</label>
                    <input type="tel" 
                           id="telefono_contacto" 
                           name="telefono_contacto" 
                           class="form-control" 
                           placeholder="Ej. +51 984 000 000 ó +1 555 123 4567" 
                           value="{{ old('telefono_contacto', $user->telefono) }}" 
                           required>
                    <span style="font-size: 0.78rem; color: var(--color-charcoal-muted);">
                        Utilizado exclusivamente para coordinar la entrega local en Cusco.
                    </span>
                </div>

                <!-- 2. Dirección de Envío y Selección de Modalidad -->
                <h2 class="form-section-title" style="margin-top: 30px;">
                    <span>2. Modalidad de Entrega en la Región Cusco (Delivery o Recojo en Tienda)</span>
                </h2>

                <!-- Selector Principal: Delivery vs Recojo en Tienda -->
                <div class="form-group">
                    <label class="form-label">¿Cómo deseas recibir tus piezas artesanales? *:</label>
                    <div class="shipping-options-grid">
                        <label class="shipping-radio-card" id="card-mode-delivery" style="cursor: pointer;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="modalidad_entrega" id="mode_delivery" value="delivery" checked onchange="cambiarModalidadEntrega('delivery')">
                                <strong>🛵 Delivery a Domicilio</strong>
                            </div>
                            <span style="font-size: 0.8rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                                Despacho a tu puerta en Cusco o Valle Sagrado.
                            </span>
                        </label>

                        <label class="shipping-radio-card" id="card-mode-recojo" style="cursor: pointer;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="modalidad_entrega" id="mode_recojo" value="recojo" onchange="cambiarModalidadEntrega('recojo')">
                                <strong>🏬 Recojo en Tienda</strong>
                            </div>
                            <span style="font-size: 0.8rem; color: var(--color-fairtrade-green); font-weight: 700; margin-top: 4px;">
                                ¡GRATIS! (S/ 0.00) • Centro Histórico
                            </span>
                        </label>
                    </div>
                </div>

                <!-- BLOQUE A: DELIVERY A DOMICILIO -->
                <div id="seccion_delivery_campos">
                    <div class="form-group">
                        <label class="form-label" for="pais_destino">Zona de Entrega en Cusco *:</label>
                        <select name="pais_destino" id="pais_destino" class="form-control" onchange="actualizarCostoSegunDestino(this.value)">
                            <option value="Cusco (Ciudad / Urbano)">Cusco Ciudad - Zona Urbana (Centro Histórico, Wanchaq, San Sebastián, San Jerónimo, Santiago)</option>
                            <option value="Cusco (Valle Sagrado y Provincias)">Cusco - Valle Sagrado y Provincias (Urubamba, Pisac, Calca, Ollantaytambo, Chinchero, etc.)</option>
                        </select>
                        <span style="font-size: 0.78rem; color: var(--color-fairtrade-green); font-weight: 600; margin-top: 4px; display: block;">
                            📍 Cobertura logística exclusiva para entregas dentro de la Región del Cusco.
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tarifa de transporte según zona *:</label>
                        <div class="shipping-options-grid">
                            <label class="shipping-radio-card" id="card-shipping-urbano">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="radio" name="tipo_envio" id="tipo_envio_urbano" value="Cusco Urbano" checked onchange="actualizarCostoEnvio(10, 'Delivery Cusco Urbano')">
                                    <strong>Cusco Urbano</strong>
                                </div>
                                <span style="font-size: 0.85rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                                    Despacho local en distritos urbanos de Cusco (24 a 48 hrs).
                                </span>
                                <span style="font-weight: 700; color: var(--color-charcoal-dark); margin-top: 6px;">
                                    + S/ 10.00 PEN
                                </span>
                            </label>

                            <label class="shipping-radio-card" id="card-shipping-provincias">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="radio" name="tipo_envio" id="tipo_envio_provincias" value="Valle Sagrado y Provincias" onchange="actualizarCostoEnvio(20, 'Delivery Valle Sagrado')">
                                    <strong>Valle Sagrado y Provincias</strong>
                                </div>
                                <span style="font-size: 0.85rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                                    Despacho a Urubamba, Pisac, Ollantaytambo, Calca, Chinchero y provincias.
                                </span>
                                <span style="font-weight: 700; color: var(--color-earth-primary); margin-top: 6px;">
                                    + S/ 20.00 PEN
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="direccion_envio">Dirección Completa de Entrega (Calle, N°, Distrito / Urbanización / Referencia) *:</label>
                        <textarea name="direccion_envio" 
                                  id="direccion_envio" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Ej. Calle Saphy 340, Centro Histórico / Av. de la Cultura 720, Wanchaq (Ref. frente a la plaza)..." 
                                  required>{{ old('direccion_envio', $user->direccion) }}</textarea>
                    </div>
                </div>

                <!-- BLOQUE B: RECOJO EN TIENDA / HUB CUSCO -->
                <div id="seccion_recojo_tienda" style="display: none; background: #FAF5EB; border: 1.5px solid #D6BEA2; border-radius: 12px; padding: 20px; margin-bottom: 22px;">
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                        <div style="font-size: 2.2rem; line-height: 1;">🏬</div>
                        <div style="flex: 1;">
                            <h3 style="font-size: 1.05rem; color: #2D1D0F; margin: 0 0 6px; font-weight: 700; font-family: var(--font-serif);">
                                Punto Oficial de Recojo en Tienda / HUB Cusco
                            </h3>
                            <p style="margin: 0; font-size: 0.9rem; color: #3E2916; line-height: 1.5;">
                                📍 <strong>HUB Central de Artesanos de Cusco:</strong> Calle Triunfo 392, Centro Histórico (a solo 1 cuadra de la Plaza de Armas del Cusco).
                            </p>
                            <div style="margin-top: 10px; font-size: 0.82rem; color: var(--color-charcoal-muted); line-height: 1.5;">
                                🕒 <strong>Horario de Atención:</strong> Lunes a Sábado de 9:00 AM a 7:00 PM.<br>
                                📦 <strong>Disponibilidad:</strong> Tu pedido estará empaquetado y listo para retiro en 24 horas hábiles presentando tu DNI o código de pedido.<br>
                                💰 <strong>Costo del Servicio:</strong> <span style="color: var(--color-fairtrade-green); font-weight: 700;">¡Totalmente GRATIS (S/ 0.00 PEN)!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Opciones de Envoltura de Regalo con Dedicatoria Digital y QR -->
                <div class="form-group" style="background-color: #FFF9E6; border: 1.5px solid var(--color-gold); padding: 18px; border-radius: var(--radius-md); box-shadow: 0 4px 12px rgba(184, 134, 11, 0.08);">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="envoltura_regalo" id="checkout_envoltura" value="1" {{ $envolturaRegalo ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: var(--color-earth-primary); cursor: pointer;" onchange="toggleDedicatoriaPreview(this.checked)">
                            <label for="checkout_envoltura" style="font-weight: 700; cursor: pointer; color: var(--color-charcoal-dark); font-size: 1rem;">
                                🎁 Empaque artesanal de regalo con Dedicatoria Digital & Código QR (+ S/ 12.00 PEN)
                            </label>
                        </div>
                        <span class="badge" style="background: #F0DFC0; color: #8A4B08; font-size: 0.75rem; font-weight: 700;">
                            📱 Incluye Tarjeta QR
                        </span>
                    </div>

                    <div id="dedicatoria_fields_container" style="margin-top: 14px; {{ $envolturaRegalo ? '' : 'display: none;' }}">
                        <p style="font-size: 0.85rem; color: #7A5826; margin-bottom: 8px; line-height: 1.4;">
                            El maestro artesano preparará una tarjeta física personalizada que incluirá tu dedicatoria manuscrita y un <strong>Código QR Digital</strong>. Quien reciba el regalo podrá escanear el QR desde cualquier celular para leer tu mensaje y conocer a los artesanos creadores.
                        </p>
                        <input type="text" 
                               name="mensaje_regalo" 
                               id="mensaje_regalo_input"
                               class="form-control" 
                               placeholder="Escribe tu dedicatoria especial (ej: Con todo mi cariño desde las tierras del Cusco...)" 
                               maxlength="300"
                               value="{{ old('mensaje_regalo') }}"
                               oninput="actualizarPreviewDedicatoria(this.value)">

                        <!-- Vista Previa de la Tarjeta QR que se enviará -->
                        <div class="qr-live-preview-box" style="margin-top: 14px; background: #FFFFFF; border: 1.5px dashed #D4A359; border-radius: 10px; padding: 14px 18px; display: flex; align-items: center; gap: 18px;">
                            <div style="background: #FDF9F0; border: 1px solid #E8DCBE; padding: 8px; border-radius: 8px; text-align: center; flex-shrink: 0;">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=PORTAL-ARTESANO-CUSCO-DEDICATORIA-DEMO" alt="Preview QR" style="width: 72px; height: 72px; display: block; border-radius: 4px;">
                                <span style="font-size: 0.65rem; font-weight: 700; color: #8C531B; display: block; margin-top: 4px;">QR DIGITAL</span>
                            </div>
                            <div style="flex: 1; font-size: 0.85rem;">
                                <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #9E743A; letter-spacing: 0.5px;">
                                    ✦ Vista Previa de Tarjeta para el Destinatario
                                </div>
                                <div id="preview_mensaje_texto" style="font-family: var(--font-serif); font-style: italic; color: #2C1E11; font-size: 0.95rem; margin-top: 4px; line-height: 1.4;">
                                    «Escribe tu mensaje arriba para previsualizarlo aquí...»
                                </div>
                                <div style="font-size: 0.75rem; color: #8A6844; margin-top: 4px;">
                                    Visible para: <strong>Tú (Cliente)</strong>, <strong>Taller Artesano</strong> y <strong>HUB Logístico</strong>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Método de Pago -->
                <h2 class="form-section-title" style="margin-top: 30px;">
                    <span>3. Método de Pago Seguro</span>
                </h2>

                <div class="form-group">
                    <div class="shipping-options-grid">
                        <label class="shipping-radio-card">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="metodo_pago" value="Pago en Efectivo (Contra entrega en Cusco)" checked>
                                <strong>Pago en Efectivo</strong>
                            </div>
                            <span style="font-size: 0.78rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                                Contra entrega al recibir tu pedido en Cusco
                            </span>
                        </label>

                        <label class="shipping-radio-card">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="metodo_pago" value="Yape / Plin / Transferencia BCP Cusco">
                                <strong>Billetera Digital / Transferencia</strong>
                            </div>
                            <span style="font-size: 0.78rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                                Yape, Plin o Transferencia bancaria
                            </span>
                        </label>
                    </div>
                </div>

                <!-- 5. Consentimiento Legal - Ley N.º 29733 -->
                <div style="border-top: 1px solid var(--color-cream-border); padding-top: 18px; margin-top: 24px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 0.85rem; line-height: 1.4;">
                        <input type="checkbox" name="consentimiento_datos" id="consentimiento_datos" required style="margin-top: 3px; width: 18px; height: 18px;">
                        <span>
                            <strong>Consentimiento Informado de Datos Personales:</strong> Autorizo de manera libre, previa, expresa e inequívoca el tratamiento de mis datos personales para la gestión, coordinación y entrega de este pedido en la región del Cusco, de estricta conformidad con la <strong>Ley N.º 29733 (Ley de Protección de Datos Personales del Perú)</strong> y su Reglamento.
                        </span>
                    </label>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-accent btn-block" style="padding: 16px 24px; font-size: 1.1rem;">
                        🛡️ Confirmar y Certificar Compra
                    </button>
                </div>
            </form>
        </section>

        <!-- Columna 2: Resumen del Pedido y Transparencia -->
        <aside class="cart-summary-card" aria-label="Resumen de la orden">
            <h2 class="summary-title">Resumen de la Orden</h2>

            <div style="max-height: 320px; overflow-y: auto; margin-bottom: 20px; padding-right: 6px;">
                @foreach($carrito as $item)
                    <div style="display: flex; gap: 12px; margin-bottom: 14px; border-bottom: 1px solid var(--color-cream-border); padding-bottom: 12px;">
                        <img src="{{ $item['imagen_url'] ?? '' }}" alt="{{ $item['nombre'] ?? 'Pieza' }}" style="width: 54px; height: 54px; object-fit: cover; border-radius: var(--radius-sm);">
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.9rem; line-height: 1.2;">
                                {{ $item['nombre'] ?? 'Pieza artesanal' }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--color-charcoal-muted); margin-top: 2px;">
                                {{ $item['cantidad'] ?? 1 }}x S/ {{ number_format($item['precio'] ?? 0, 2) }} | {{ $item['artesano'] ?? 'Maestro Artesano' }}
                            </div>
                        </div>
                        <div style="font-weight: 700; font-size: 0.95rem;">
                            S/ {{ number_format(($item['precio'] ?? 0) * ($item['cantidad'] ?? 1), 2) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="summary-row">
                <span>Subtotal ({{ $cantTotal }} piezas):</span>
                <span>S/ {{ number_format($subtotal, 2) }}</span>
            </div>

            <div class="summary-row">
                <span>Envoltura de regalo:</span>
                <span id="summary-envoltura-cost">S/ {{ $envolturaRegalo ? '12.00' : '0.00' }}</span>
            </div>

            <div class="summary-row">
                <span>Estimado envío:</span>
                <span id="summary-shipping-cost">S/ 10.00 PEN (Cusco Urbano)</span>
            </div>

            <div class="summary-row total-row">
                <span>Total Estimado:</span>
                <span id="summary-total-amount">S/ {{ number_format($subtotal + ($envolturaRegalo ? 12 : 0) + 10, 2) }} PEN</span>
            </div>

            <!-- Ficha de Trazabilidad y Garantía HUB -->
            <div style="background-color: var(--color-cream-surface); border: 1px solid var(--color-cream-border); border-radius: var(--radius-sm); padding: 14px; margin-top: 20px; font-size: 0.82rem;">
                <h4 style="font-family: var(--font-serif); margin-bottom: 6px; color: var(--color-charcoal-dark);">
                    Certificación HUB Cusco Incluida
                </h4>
                <ul style="list-style: none; color: var(--color-charcoal-muted); line-height: 1.5;">
                    <li>✓ Código QR único de autenticidad en cada pieza</li>
                    <li>✓ Verificación directa en el taller artesanal</li>
                    <li>✓ Embalaje reforzado contra vibraciones y caídas</li>
                    <li>✓ Retribución justa y directa a las familias creadoras</li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<script>
const baseSubtotal = {{ (float) $subtotal }};
let currentEnvoltura = {{ $envolturaRegalo ? 12.00 : 0.00 }};
let currentEnvio = 10.00;

function cambiarModalidadEntrega(modo) {
    const secDelivery = document.getElementById('seccion_delivery_campos');
    const secRecojo = document.getElementById('seccion_recojo_tienda');
    const dirInput = document.getElementById('direccion_envio');
    const paisSelect = document.getElementById('pais_destino');
    const form = document.getElementById('checkout-form');

    if (modo === 'recojo') {
        if (secDelivery) secDelivery.style.display = 'none';
        if (secRecojo) secRecojo.style.display = 'block';

        if (dirInput) {
            if (!dirInput.dataset.original) {
                dirInput.dataset.original = dirInput.value;
            }
            dirInput.value = 'Recojo en Tienda: HUB Central Cusco (Calle Triunfo 392, Centro Histórico)';
        }

        if (paisSelect) {
            let opt = paisSelect.querySelector('option[value="Cusco (Recojo en Tienda)"]');
            if (!opt) {
                opt = new Option('Cusco (Recojo en Tienda / HUB Central)', 'Cusco (Recojo en Tienda)');
                paisSelect.add(opt);
            }
            paisSelect.value = 'Cusco (Recojo en Tienda)';
        }

        let radRecojo = document.getElementById('tipo_envio_recojo_hidden');
        if (!radRecojo && form) {
            radRecojo = document.createElement('input');
            radRecojo.type = 'radio';
            radRecojo.name = 'tipo_envio';
            radRecojo.id = 'tipo_envio_recojo_hidden';
            radRecojo.value = 'Recojo en Tienda';
            radRecojo.style.display = 'none';
            form.appendChild(radRecojo);
        }
        if (radRecojo) {
            radRecojo.checked = true;
        }

        const radUrbano = document.getElementById('tipo_envio_urbano');
        const radProvincias = document.getElementById('tipo_envio_provincias');
        if (radUrbano) radUrbano.checked = false;
        if (radProvincias) radProvincias.checked = false;

        actualizarCostoEnvio(0, 'Recojo en Tienda');
    } else {
        if (secDelivery) secDelivery.style.display = 'block';
        if (secRecojo) secRecojo.style.display = 'none';

        if (dirInput) {
            if (dirInput.dataset.original !== undefined && !dirInput.dataset.original.includes('Recojo en Tienda')) {
                dirInput.value = dirInput.dataset.original;
            } else {
                dirInput.value = '';
            }
        }

        const radRecojo = document.getElementById('tipo_envio_recojo_hidden');
        if (radRecojo) {
            radRecojo.checked = false;
        }

        const radUrbano = document.getElementById('tipo_envio_urbano');
        const radProvincias = document.getElementById('tipo_envio_provincias');
        if (radProvincias && radProvincias.checked) {
            actualizarCostoEnvio(20, 'Delivery Valle Sagrado');
        } else {
            if (radUrbano) radUrbano.checked = true;
            actualizarCostoEnvio(10, 'Delivery Cusco Urbano');
        }

        if (paisSelect && paisSelect.value === 'Cusco (Recojo en Tienda)') {
            paisSelect.value = 'Cusco (Ciudad / Urbano)';
        }
    }
}

function actualizarCostoEnvio(costo, label) {
    currentEnvio = parseFloat(costo);
    const shippingEl = document.getElementById('summary-shipping-cost');
    if (shippingEl) {
        shippingEl.textContent = `S/ ${currentEnvio.toFixed(2)} PEN (${label})`;
    }
    recalcularGranTotal();
}

function recalcularGranTotal() {
    const total = baseSubtotal + currentEnvoltura + currentEnvio;
    const totalEl = document.getElementById('summary-total-amount');
    if (totalEl) {
        totalEl.textContent = `S/ ${total.toFixed(2)} PEN`;
    }
}

function toggleDedicatoriaPreview(isChecked) {
    const container = document.getElementById('dedicatoria_fields_container');
    if (container) {
        container.style.display = isChecked ? 'block' : 'none';
    }
    currentEnvoltura = isChecked ? 12.00 : 0.00;
    const envEl = document.getElementById('summary-envoltura-cost');
    if (envEl) {
        envEl.textContent = `S/ ${currentEnvoltura.toFixed(2)}`;
    }
    recalcularGranTotal();
}

function actualizarCostoSegunDestino(val) {
    if (val.includes('Valle Sagrado') || val.includes('Provincias')) {
        const rad = document.getElementById('tipo_envio_provincias');
        if (rad) {
            rad.checked = true;
            actualizarCostoEnvio(20, 'Valle Sagrado y Provincias');
        }
    } else {
        const rad = document.getElementById('tipo_envio_urbano');
        if (rad) {
            rad.checked = true;
            actualizarCostoEnvio(10, 'Cusco Urbano');
        }
    }
}

function actualizarPreviewDedicatoria(val) {
    const preview = document.getElementById('preview_mensaje_texto');
    if (preview) {
        preview.textContent = val.trim() ? `«${val}»` : '«Escribe tu mensaje arriba para previsualizarlo aquí...»';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('mensaje_regalo_input');
    if (input && input.value) {
        actualizarPreviewDedicatoria(input.value);
    }
});
</script>
@endsection
