@extends('layouts.app')

@section('title', 'Detalle de Pedido #' . $pedido->id . ' | Portal del Artesano')

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <span class="badge {{ $pedido->estado_badge }}">{{ $pedido->estado_legible }}</span>
            <h1 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-charcoal-dark); margin-top: 6px;">
                Orden de Despacho #{{ $pedido->id }}
            </h1>
            <p style="font-size: 0.9rem; color: var(--color-charcoal-muted);">
                Código de Seguimiento HUB: <strong style="font-family: monospace; color: var(--color-charcoal-dark);">{{ $pedido->codigo_seguimiento }}</strong>
            </p>
        </div>

        <div>
            <a href="{{ route('catalogo.index') }}" class="btn btn-secondary btn-sm">← Ir al Catálogo</a>
            @if(Auth::user()->isCliente())
                <a href="{{ route('pedidos.index') }}" class="btn btn-primary btn-sm">Ver Mis Pedidos</a>
            @endif
        </div>
    </div>

    <!-- Trazabilidad Logística HUB: Pasos visuales -->
    <div style="background-color: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 30px; box-shadow: var(--shadow-sm);">
        <h3 style="font-family: var(--font-serif); font-size: 1.15rem; margin-bottom: 16px; color: var(--color-charcoal-dark);">
            Línea de Trazabilidad Logística & Certificación Andina
        </h3>

        @php
            $etapas = [
                'pendiente' => '1. Solicitud Recibida',
                'confirmado_artesano' => '2. En Taller Artesano',
                'en_acopio_hub' => '3. Acopio & Certificación HUB',
                'enviado_internacional' => '4. En Despacho / Vuelo',
                'entregado' => '5. Entregado en Destino',
            ];
            $estadosClaves = array_keys($etapas);
            $indiceActual = array_search($pedido->estado_pedido, $estadosClaves);
            if ($indiceActual === false) $indiceActual = 0;
        @endphp

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px;">
            @foreach($etapas as $clave => $etiqueta)
                @php
                    $indice = array_search($clave, $estadosClaves);
                    $alcanzado = $indice <= $indiceActual;
                @endphp
                <div style="padding: 12px; border-radius: var(--radius-sm); border: 1px solid {{ $alcanzado ? 'var(--color-fairtrade-green)' : 'var(--color-cream-border)' }}; background-color: {{ $alcanzado ? 'var(--color-fairtrade-light)' : 'var(--color-cream-surface)' }};">
                    <div style="font-size: 0.8rem; font-weight: 700; color: {{ $alcanzado ? 'var(--color-fairtrade-green)' : 'var(--color-charcoal-muted)' }};">
                        {{ $alcanzado ? '✓ Completado / En curso' : '○ Pendiente' }}
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--color-charcoal-dark); margin-top: 4px;">
                        {{ $etiqueta }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="order-detail-layout">
        <!-- Piezas Incluidas -->
        <div class="cart-items-card">
            <h3 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: 16px;">
                Piezas Artesanales en este Pedido
            </h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($pedido->productos as $prod)
                    <div style="display: flex; gap: 16px; align-items: center; border-bottom: 1px solid var(--color-cream-border); padding-bottom: 16px;">
                        <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre_prod }}" style="width: 70px; height: 70px; object-fit: cover; border-radius: var(--radius-sm);">
                        <div style="flex: 1;">
                            <h4 style="font-family: var(--font-serif); font-size: 1.05rem;">
                                <a href="{{ route('catalogo.show', $prod->id) }}">{{ $prod->nombre_prod }}</a>
                            </h4>
                            <div style="font-size: 0.8rem; color: var(--color-charcoal-muted); margin-top: 2px;">
                                Taller: <strong>{{ $prod->artesano->nombre_completo }}</strong> ({{ $prod->artesano->direccion_arte }})
                            </div>
                            <div style="font-size: 0.78rem; font-family: monospace; color: var(--color-earth-primary); margin-top: 4px;">
                                Certificado: {{ $prod->certificado_autenticidad_codigo }}
                            </div>
                            <form action="{{ route('chat.iniciar') }}" method="POST" style="margin-top: 6px;">
                                @csrf
                                <input type="hidden" name="artesano_id" value="{{ $prod->artesano_id }}">
                                <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                                <input type="hidden" name="producto_id" value="{{ $prod->id }}">
                                <button type="submit" class="btn btn-outline-earth btn-sm" style="font-size: 0.74rem; padding: 3px 8px;">
                                    💬 Chatear con el Maestro {{ $prod->artesano->nombre_completo }}
                                </button>
                            </form>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.85rem; color: var(--color-charcoal-muted);">
                                {{ $prod->pivot->cantidad }} x S/ {{ number_format($prod->pivot->precio_unitario_historico, 2) }}
                            </div>
                            <div style="font-weight: 700; font-size: 1.1rem; color: var(--color-charcoal-dark);">
                                S/ {{ number_format($prod->pivot->cantidad * $prod->pivot->precio_unitario_historico, 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($pedido->envoltura_regalo)
                <!-- Tarjeta de Dedicatoria & Código QR Digital -->
                <div style="background: linear-gradient(135deg, #FFFDF8 0%, #FFF8E7 100%); border: 2px solid #D4A359; border-radius: var(--radius-md); padding: 22px; margin-top: 24px; box-shadow: 0 4px 16px rgba(184, 134, 11, 0.08);">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ECDAB9; padding-bottom: 12px; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.3rem;">🎁</span>
                            <strong style="font-family: var(--font-serif); font-size: 1.15rem; color: #3B2613;">
                                Tarjeta de Dedicatoria & Código QR Digital
                            </strong>
                        </div>
                        <span class="badge" style="background: #2E6B38; color: #FFFFFF; font-size: 0.75rem;">
                            ✓ QR Generado y Activo
                        </span>
                    </div>

                    <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                        <!-- QR Code Thumbnail -->
                        <div style="text-align: center; background: #FFFFFF; border: 1.5px dashed #D4A359; border-radius: 8px; padding: 10px;">
                            <a href="{{ $pedido->url_dedicatoria_digital }}" target="_blank" title="Clic para abrir dedicatoria digital">
                                <img src="{{ $pedido->qr_dedicatoria_url }}" 
                                     alt="QR Dedicatoria Digital" 
                                     style="width: 110px; height: 110px; display: block; border-radius: 4px;">
                            </a>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #8C531B; margin-top: 4px; display: block;">
                                ESCANEAR O CLIC
                            </span>
                        </div>

                        <!-- Dedication Message & Actions -->
                        <div style="flex: 1; min-width: 250px;">
                            <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: #8A5A1B; letter-spacing: 0.5px;">
                                Mensaje que acompaña el empaque:
                            </div>
                            <div style="font-family: var(--font-serif); font-size: 1.1rem; font-style: italic; color: #2C1E11; background: #FAF5E8; border-left: 3px solid #C88A2C; padding: 10px 14px; border-radius: 4px; margin-top: 6px; line-height: 1.5;">
                                «{{ $pedido->mensaje_regalo ?: 'Que este pedazo de historia cusqueña ilumine tu hogar.' }}»
                            </div>
                            <p style="font-size: 0.8rem; color: #7A624E; margin-top: 8px; line-height: 1.4;">
                                Este código QR viaja impreso en la tarjeta física dentro del paquete. Al escanearlo, el destinatario verá la dedicatoria en pantalla y la trazabilidad de los talleres de Cusco.
                            </p>
                            <div style="display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap;">
                                <a href="{{ $pedido->url_dedicatoria_digital }}" target="_blank" class="btn btn-primary btn-sm">
                                    📱 Ver Dedicatoria Digital
                                </a>
                                <a href="{{ $pedido->url_dedicatoria_digital }}" target="_blank" onclick="window.open(this.href).print(); return false;" class="btn btn-secondary btn-sm">
                                    🖨️ Imprimir Tarjeta
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Ficha de Envío y Desglose Financiero -->
        <aside class="cart-summary-card">
            <h3 class="summary-title">Detalles de Entrega</h3>

            <div style="font-size: 0.88rem; line-height: 1.6; margin-bottom: 20px;">
                <div><strong>Destinatario:</strong> {{ $pedido->cliente->nombre_completo }}</div>
                <div><strong>País / Destino:</strong> {{ $pedido->pais_destino }}</div>
                <div><strong>Dirección:</strong> {{ $pedido->direccion_envio }}</div>
                <div><strong>Modalidad:</strong> {{ $pedido->tipo_envio }}</div>
            </div>

            <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 10px; border-top: 1px solid var(--color-cream-border); padding-top: 14px;">
                Liquidación de Orden
            </h4>

            <div class="summary-row">
                <span>Subtotal Piezas:</span>
                <span>S/ {{ number_format($pedido->subtotal, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Costo de Despacho:</span>
                <span>S/ {{ number_format($pedido->costo_envio, 2) }}</span>
            </div>
            @if($pedido->envoltura_regalo)
                <div class="summary-row">
                    <span>Envoltura Artesanal:</span>
                    <span>S/ 12.00</span>
                </div>
            @endif
            <div class="summary-row total-row">
                <span>Total Abonado:</span>
                <span>S/ {{ number_format($pedido->total, 2) }} PEN</span>
            </div>

            <div style="background-color: var(--color-fairtrade-light); padding: 12px; border-radius: var(--radius-sm); margin-top: 16px; font-size: 0.8rem;">
                <div style="color: var(--color-fairtrade-green); font-weight: 700; margin-bottom: 4px;">
                    🤝 Impacto Social Garantizado:
                </div>
                <div>Retribución artesano: <strong>S/ {{ number_format($pedido->subtotal - $pedido->comision_curaduria_hub, 2) }}</strong></div>
                <div>Curaduría & HUB: <strong>S/ {{ number_format($pedido->comision_curaduria_hub, 2) }}</strong></div>
            </div>
        </aside>
    </div>
</div>
@endsection
