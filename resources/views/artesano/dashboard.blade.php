@extends('layouts.app')

@section('title', 'Taller Digital | ' . $artesano->nombre_completo)

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px;">
    <!-- Encabezado del Taller -->
    <div style="background-color: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; gap: 18px; align-items: center;">
            <img src="{{ $artesano->foto_perfil ?? 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=400&q=80' }}" alt="Foto de {{ $artesano->nombre_completo }}" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-earth-primary);">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 1.7rem; color: var(--color-charcoal-dark);">
                    Taller de {{ $artesano->nombre_completo }}
                </h1>
                <div style="font-size: 0.9rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                    📍 {{ $artesano->direccion_arte }} | Tel: {{ $artesano->telefono }}
                </div>
            </div>
        </div>

        <div>
            @if($artesano->estaValidado())
                <span class="badge badge-success" style="font-size: 0.85rem; padding: 6px 12px;">
                    ✓ Taller Validado por el HUB
                </span>
            @else
                <span class="badge badge-warning" style="font-size: 0.85rem; padding: 6px 12px;">
                    ⏳ En Proceso de Validación por el HUB
                </span>
            @endif
        </div>
    </div>

    <!-- Barra de Alertas en Tiempo Real con Sonido -->
    <div class="artisan-live-alerts-bar" style="background: linear-gradient(135deg, #FFFDF6 0%, #FFF7E8 100%); border: 1.5px solid #D4A359; border-radius: var(--radius-md); padding: 14px 20px; margin-bottom: 26px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; box-shadow: 0 4px 14px rgba(184, 134, 11, 0.08);">
        <div style="display: flex; align-items: center; gap: 14px;">
            <span style="font-size: 1.8rem; line-height: 1; display: inline-block;">🔔</span>
            <div>
                <div style="font-weight: 700; font-size: 0.98rem; color: #3B2613; display: flex; align-items: center; gap: 8px;">
                    <span>Monitor de Nuevos Pedidos en Taller</span>
                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.72rem; color: #2E6B38; background: #E8F5E9; padding: 2px 8px; border-radius: 12px; font-weight: 700;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #2E6B38; display: inline-block;"></span> EN VIVO
                    </span>
                </div>
                <div style="font-size: 0.82rem; color: #7A624E; margin-top: 2px;">
                    Cuando un cliente compre una de tus obras, sonará una campana y aparecerá la orden aquí al instante.
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="btn btn-outline-earth btn-sm" onclick="if(window.probarSonidoAlerta){ window.probarSonidoAlerta(); }" style="font-size: 0.82rem;">
                🔊 Probar Sonido de Alerta
            </button>
            <span id="contador-pedidos-pendientes" class="badge {{ $pedidos->where('estado_pedido', 'pendiente')->count() > 0 ? 'badge-warning' : 'badge-secondary' }}" style="font-size: 0.82rem; padding: 6px 12px;">
                {{ $pedidos->where('estado_pedido', 'pendiente')->count() }} pendientes de preparación
            </span>
        </div>
    </div>

    <!-- Tarjetas de Métricas -->
    <div class="dashboard-metrics-grid">
        <div class="metric-card">
            <span class="metric-label">Piezas Registradas</span>
            <span class="metric-value">{{ $productos->count() }}</span>
            <span class="metric-foot">{{ $productos->where('aprobado_hub', true)->count() }} en vitrina activa</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Pedidos Recibidos</span>
            <span class="metric-value">{{ $pedidos->count() }}</span>
            <span class="metric-foot">Órdenes de clientes</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Ingresos Netos Acumulados (85%)</span>
            <span class="metric-value">S/ {{ number_format($totalRecibido, 2) }}</span>
            <span class="metric-foot">Comercio justo garantizado</span>
        </div>
        <div class="metric-card" style="border-left: 4px solid var(--color-earth-primary);">
            <span class="metric-label">Mensajería en Vivo</span>
            <span class="metric-value">💬 Chats</span>
            <span class="metric-foot">
                <a href="{{ route('chat.index') }}" style="color: var(--color-earth-primary); font-weight: 700; text-decoration: underline;">
                    Bandeja con Clientes →
                </a>
            </span>
        </div>
    </div>

    <!-- Gestión de Piezas del Taller -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.4rem;">
            Mis Piezas y Obras de Arte
        </h2>
        <a href="{{ route('artesano.productos.create') }}" class="btn btn-accent btn-sm">
            + Registrar Nueva Pieza
        </a>
    </div>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Pieza</th>
                    <th>Categoría</th>
                    <th>Precio Base</th>
                    <th>Tu Retribución (85%)</th>
                    <th>Stock</th>
                    <th>Estado HUB</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $prod)
                    <tr>
                        <td>
                            <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre_prod }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-sm);">
                        </td>
                        <td>
                            <strong>{{ $prod->nombre_prod }}</strong>
                            <div style="font-size: 0.75rem; font-family: monospace; color: var(--color-charcoal-muted);">
                                {{ $prod->certificado_autenticidad_codigo }}
                            </div>
                        </td>
                        <td>{{ $prod->categoria }}</td>
                        <td>S/ {{ number_format($prod->precio, 2) }}</td>
                        <td style="color: var(--color-fairtrade-green); font-weight: 700;">
                            S/ {{ number_format($prod->precio_artesano, 2) }}
                        </td>
                        <td>
                            <span style="font-weight: 600; {{ $prod->stock < 3 ? 'color: var(--color-danger);' : '' }}">
                                {{ $prod->stock }} unids.
                            </span>
                        </td>
                        <td>
                            @if($prod->aprobado_hub)
                                <span class="badge badge-success">Aprobado / Publicado</span>
                            @else
                                <span class="badge badge-warning">En revisión HUB</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('catalogo.show', $prod->id) }}" class="btn btn-secondary btn-sm" title="Vista previa">
                                    👁️
                                </a>
                                <a href="{{ route('artesano.productos.edit', $prod->id) }}" class="btn btn-outline-earth btn-sm" title="Editar precio/stock">
                                    ✏️
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px;">
                            Aún no has registrado piezas. ¡Comienza subiendo tus obras al catálogo!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pedidos para Despacho en Taller -->
    <h2 style="font-family: var(--font-serif); font-size: 1.4rem; margin-top: 40px; margin-bottom: 16px;">
        Órdenes Solicitadas a tu Taller
    </h2>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>N° Pedido</th>
                    <th>Fecha</th>
                    <th>Comprador</th>
                    <th>Piezas solicitadas</th>
                    <th>Estado de Despacho</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tabla-pedidos-artesano-body">
                @forelse($pedidos as $ped)
                    <tr>
                        <td><strong>#{{ $ped->id }}</strong></td>
                        <td>
                            <div>{{ $ped->created_at->format('d/m/Y') }}</div>
                            <div style="font-size: 0.75rem; color: var(--color-charcoal-muted);">{{ $ped->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <strong>{{ $ped->cliente->nombre_completo }}</strong>
                            <div style="font-size: 0.8rem; color: var(--color-charcoal-muted);">
                                📍 {{ $ped->pais_destino }}
                            </div>
                            <form action="{{ route('chat.iniciar') }}" method="POST" style="margin-top: 4px;">
                                @csrf
                                <input type="hidden" name="artesano_id" value="{{ $artesano->id }}">
                                <input type="hidden" name="pedido_id" value="{{ $ped->id }}">
                                <input type="hidden" name="asunto" value="Coordinación Pedido #{{ $ped->id }} con {{ $ped->cliente->nombre_completo }}">
                                <button type="submit" class="btn btn-outline-earth btn-sm" style="font-size: 0.72rem; padding: 2px 8px;">
                                    💬 Chatear con Comprador
                                </button>
                            </form>
                        </td>
                        <td>
                            @foreach($ped->productos as $pItem)
                                <div>• {{ $pItem->pivot->cantidad }}x {{ $pItem->nombre_prod }}</div>
                            @endforeach

                            @if($ped->envoltura_regalo)
                                <div style="margin-top: 10px; background: #FFF9E6; border: 1.5px dashed #D4A359; border-radius: 8px; padding: 10px 12px; font-size: 0.82rem;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                        <strong style="color: #8C531B; display: flex; align-items: center; gap: 4px;">
                                            🎁 Empaque de Regalo + QR Digital
                                        </strong>
                                        <span class="badge" style="background: #E8F5E9; color: #2E6B38; font-size: 0.7rem;">
                                            + S/ 12.00
                                        </span>
                                    </div>

                                    <div style="display: flex; gap: 12px; align-items: center; margin-top: 8px;">
                                        <a href="{{ $ped->url_dedicatoria_digital }}" target="_blank" title="Ver dedicatoria digital">
                                            <img src="{{ $ped->qr_dedicatoria_url }}" 
                                                 alt="QR Dedicatoria" 
                                                 style="width: 55px; height: 55px; border-radius: 4px; border: 1px solid #D4A359; background: #FFF; padding: 2px;">
                                        </a>
                                        <div style="flex: 1;">
                                            <div style="font-family: var(--font-serif); font-style: italic; color: #3B2613; line-height: 1.3;">
                                                «{{ $ped->mensaje_regalo ?: 'Sin dedicatoria manuscrita.' }}»
                                            </div>
                                            <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                                                <a href="{{ $ped->url_dedicatoria_digital }}" target="_blank" class="btn btn-outline-earth btn-sm" style="font-size: 0.72rem; padding: 2px 8px;">
                                                    📱 Ver Tarjeta QR
                                                </a>
                                                <a href="{{ $ped->url_dedicatoria_digital }}" target="_blank" onclick="window.open(this.href).print(); return false;" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 2px 8px;">
                                                    🖨️ Imprimir Tarjeta
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #7A624E; margin-top: 6px;">
                                        💡 <em>Imprime o adjunta este código QR en la envoltura artesanal de la pieza antes de enviarla al HUB.</em>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $ped->estado_badge }}">{{ $ped->estado_legible }}</span>
                        </td>
                        <td>
                            @if($ped->estado_pedido === 'pendiente')
                                <form action="{{ route('artesano.pedidos.confirmar', $ped->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Confirmar Preparación en Taller
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 0.85rem; color: var(--color-fairtrade-green); font-weight: 600;">
                                    ✓ Confirmado
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 24px;">
                            No tienes órdenes pendientes en este momento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    window.ARTESANO_INITIAL_ORDER_ID = {{ $pedidos->max('id') ?? 0 }};
</script>
@endsection
