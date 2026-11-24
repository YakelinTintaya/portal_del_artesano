@extends('layouts.app')

@section('title', 'HUB Logístico Regional Cusco | Panel de Administración')

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px;">
    <!-- Encabezado del HUB -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 28px;">
        <div style="max-width: 750px;">
            <span class="badge badge-hub" style="font-size: 0.85rem; padding: 6px 12px;">HUB Central de Acopio y Certificación Cusco</span>
            <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: var(--color-charcoal-dark); margin-top: 6px;">
                Panel de Operaciones Logísticas y Curaduría Digital
            </h1>
            <p style="color: var(--color-charcoal-muted); font-size: 0.95rem;">
                Supervisión integral de acreditación de artesanos, certificación de autenticidad, monitoreo de ventas y trazabilidad aduanera de exportación.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.chats.resumen') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <span>💬</span> <strong>Supervisión de Chats & Resúmenes HUB</strong>
            </a>
        </div>
    </div>

    <!-- 1. Métricas Globales del HUB -->
    <div class="dashboard-metrics-grid">
        <div class="metric-card">
            <span class="metric-label">Ventas Brutas Totales</span>
            <span class="metric-value">S/ {{ number_format($metricas['total_ventas'], 2) }}</span>
            <span class="metric-foot">Comercio electrónico global</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Comisiones HUB (15%)</span>
            <span class="metric-value">S/ {{ number_format($metricas['comisiones_hub'], 2) }}</span>
            <span class="metric-foot">Curaduría y logística</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Retribución a Artesanos (85%)</span>
            <span class="metric-value" style="color: var(--color-fairtrade-green);">S/ {{ number_format($metricas['pagos_artesanos'], 2) }}</span>
            <span class="metric-foot">Impacto social directo</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Talleres Validados</span>
            <span class="metric-value">{{ $metricas['artesanos_validados'] }}</span>
            <span class="metric-foot">{{ $metricas['artesanos_pendientes'] }} pendientes de validación</span>
        </div>
    </div>

    <!-- 2. Sección: Gestión Logística de Pedidos Internacionales -->
    <section style="margin-top: 40px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.4rem; margin-bottom: 16px;">
            Monitoreo y Ciclo de Vida de Pedidos
        </h2>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>N° Pedido</th>
                        <th>Fecha</th>
                        <th>Cliente / Destino</th>
                        <th>Total</th>
                        <th>Estado Actual</th>
                        <th>Actualizar Estado Logístico</th>
                        <th>Ver</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedidosRecientes as $ped)
                        <tr>
                            <td>
                                <strong>#{{ $ped->id }}</strong>
                                <div style="font-size: 0.75rem; font-family: monospace; color: var(--color-charcoal-muted);">
                                    {{ $ped->codigo_seguimiento }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $ped->created_at->format('d/m/Y') }}</div>
                                <div style="font-size: 0.75rem; color: var(--color-charcoal-muted);">{{ $ped->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <strong>{{ $ped->cliente->nombre_completo }}</strong>
                                <div style="font-size: 0.8rem; color: var(--color-charcoal-muted);">
                                    {{ $ped->pais_destino }} ({{ $ped->tipo_envio }})
                                </div>
                                @if($ped->envoltura_regalo)
                                    <div style="margin-top: 6px;">
                                        <div style="display: inline-flex; align-items: center; gap: 6px; background: #FFF9E6; border: 1px solid #D4A359; border-radius: 6px; padding: 4px 8px; font-size: 0.75rem;">
                                            <a href="{{ $ped->url_dedicatoria_digital }}" target="_blank" title="Verificar QR y dedicatoria digital">
                                                <img src="{{ $ped->qr_dedicatoria_url }}" alt="QR" style="width: 24px; height: 24px; border-radius: 2px; vertical-align: middle;">
                                            </a>
                                            <div>
                                                <strong style="color: #8C531B;">🎁 Regalo + QR</strong>
                                                <a href="{{ $ped->url_dedicatoria_digital }}" target="_blank" style="color: var(--color-earth-primary); text-decoration: underline; margin-left: 4px;">
                                                    Verificar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>S/ {{ number_format($ped->total, 2) }}</strong>
                                <div style="font-size: 0.75rem; color: var(--color-fairtrade-green);">
                                    HUB: S/ {{ number_format($ped->comision_curaduria_hub, 2) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $ped->estado_badge }}">{{ $ped->estado_legible }}</span>
                            </td>
                            <td>
                                @if(!$ped->esCerrado())
                                    <form action="{{ route('admin.pedidos.estado', $ped->id) }}" method="POST" style="display: flex; gap: 6px; align-items: center;">
                                        @csrf
                                        <select name="estado_pedido" class="form-control" style="font-size: 0.82rem; padding: 4px 8px; width: auto;">
                                            <option value="pendiente" {{ $ped->estado_pedido === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="confirmado_artesano" {{ $ped->estado_pedido === 'confirmado_artesano' ? 'selected' : '' }}>En preparación artesano</option>
                                            <option value="en_acopio_hub" {{ $ped->estado_pedido === 'en_acopio_hub' ? 'selected' : '' }}>En acopio HUB</option>
                                            <option value="enviado_internacional" {{ $ped->estado_pedido === 'enviado_internacional' ? 'selected' : '' }}>Enviado internacional</option>
                                            <option value="entregado" {{ $ped->estado_pedido === 'entregado' ? 'selected' : '' }}>Entregado en destino</option>
                                            <option value="cancelado" {{ $ped->estado_pedido === 'cancelado' ? 'selected' : '' }}>Cancelar</option>
                                        </select>
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">
                                            Guardar
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 0.8rem; color: var(--color-charcoal-muted); font-style: italic;">
                                        Orden cerrada (Inmutable)
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <a href="{{ route('pedidos.show', $ped->id) }}" class="btn btn-secondary btn-sm" title="Ver Detalle y Trazabilidad">
                                        👁️
                                    </a>
                                    @if($ped->envoltura_regalo)
                                        <a href="{{ $ped->url_dedicatoria_digital }}" target="_blank" class="btn btn-outline-earth btn-sm" title="Ver Dedicatoria Digital con QR" style="font-size: 0.75rem; padding: 4px 6px;">
                                            📱 QR
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- 3. Sección: Acreditación de Nuevos Artesanos y Curaduría de Obras -->
    <div class="dashboard-split-grid" style="grid-template-columns: 1fr 1fr; margin-top: 40px;">
        <!-- Validación de Talleres -->
        <div>
            <h2 style="font-family: var(--font-serif); font-size: 1.3rem; margin-bottom: 14px;">
                Talleres Pendientes de Validación ({{ $artesanosPendientesList->count() }})
            </h2>

            <div class="cart-items-card">
                @forelse($artesanosPendientesList as $art)
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-cream-border); padding: 12px 0;">
                        <div>
                            <strong>{{ $art->nombre_completo }}</strong>
                            <div style="font-size: 0.8rem; color: var(--color-charcoal-muted);">
                                📍 {{ $art->direccion_arte }} | Tel: {{ $art->telefono }}
                            </div>
                        </div>
                        <form action="{{ route('admin.artesanos.toggle', $art->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                ✓ Validar Taller
                            </button>
                        </form>
                    </div>
                @empty
                    <p style="font-size: 0.9rem; color: var(--color-charcoal-muted); padding: 16px 0;">
                        Todos los talleres artesanales registrados se encuentran validados.
                    </p>
                @endforelse
            </div>
        </div>

        <!-- Curaduría de Piezas y Obras -->
        <div>
            <h2 style="font-family: var(--font-serif); font-size: 1.3rem; margin-bottom: 14px;">
                Piezas Pendientes de Curaduría ({{ $productosPendientesList->count() }})
            </h2>

            <div class="cart-items-card">
                @forelse($productosPendientesList as $prod)
                    <div style="display: flex; gap: 12px; align-items: center; border-bottom: 1px solid var(--color-cream-border); padding: 12px 0;">
                        <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre_prod }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: var(--radius-sm);">
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.88rem;">{{ $prod->nombre_prod }}</div>
                            <div style="font-size: 0.78rem; color: var(--color-charcoal-muted);">
                                {{ $prod->artesano->nombre_completo }} | S/ {{ number_format($prod->precio, 2) }}
                            </div>
                        </div>
                        <form action="{{ route('admin.productos.toggle', $prod->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-accent btn-sm">
                                ✓ Aprobar Vitrina
                            </button>
                        </form>
                    </div>
                @empty
                    <p style="font-size: 0.9rem; color: var(--color-charcoal-muted); padding: 16px 0;">
                        No hay piezas pendientes de curaduría actualmente.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
