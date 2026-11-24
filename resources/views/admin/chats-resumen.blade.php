@extends('layouts.app')

@section('title', 'Supervisión de Chats y Resúmenes Ejecutivos HUB | Portal del Artesano')

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px;">
    <!-- Encabezado con Botón de Retorno -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <span class="section-tag" style="margin-bottom: 4px; display: inline-block;">
                🛡️ Control de Calidad & Trazabilidad HUB
            </span>
            <h1 style="font-family: var(--font-serif); font-size: 1.85rem; color: var(--color-charcoal-dark); margin: 0;">
                Supervisión & Resúmenes de Chats Cliente - Artesano
            </h1>
            <p style="color: var(--color-charcoal-muted); font-size: 0.95rem; margin-top: 4px;">
                Monitorea los acuerdos de personalización, dudas de compradores y coordina el acopio logístico a tiempo.
            </p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
            ← Volver al Panel HUB
        </a>
    </div>

    <!-- Tarjetas de Métricas de Mensajería -->
    <div class="dashboard-metrics-grid" style="margin-bottom: 30px;">
        <div class="metric-card">
            <span class="metric-label">Conversaciones Totales</span>
            <span class="metric-value">{{ $totalChats }}</span>
            <span class="metric-foot">Clientes y talleres conectados</span>
        </div>
        <div class="metric-card" style="{{ $chatsAlerta > 0 ? 'border: 2px solid var(--color-danger); background: #FFF5F5;' : '' }}">
            <span class="metric-label">Requieren Asistencia HUB</span>
            <span class="metric-value" style="{{ $chatsAlerta > 0 ? 'color: var(--color-danger);' : '' }}">{{ $chatsAlerta }}</span>
            <span class="metric-foot">Alertas de demoras o aduanas</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Acuerdos Resueltos</span>
            <span class="metric-value" style="color: var(--color-fairtrade-green);">{{ $chatsResueltos }}</span>
            <span class="metric-foot">Consultas atendidas exitosamente</span>
        </div>
    </div>

    <!-- Tabla Maestra de Resúmenes de Chats -->
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 70px;">Chat</th>
                    <th style="width: 170px;">Participantes</th>
                    <th style="width: 160px;">Obra / Pedido</th>
                    <th>Resumen Ejecutivo del Chat (Para el HUB)</th>
                    <th style="width: 120px;">Estado & Alerta</th>
                    <th style="width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conversaciones as $c)
                    <tr style="{{ $c->necesita_intervencion_hub ? 'background-color: #FFF9F9;' : '' }}">
                        <td>
                            <strong>#{{ $c->id }}</strong>
                            <div style="font-size: 0.72rem; color: var(--color-charcoal-muted);">
                                {{ $c->ultimo_mensaje_at ? $c->ultimo_mensaje_at->format('d/m H:i') : $c->created_at->format('d/m H:i') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.88rem;">
                                👤 <strong>{{ $c->cliente->nombre_completo ?? 'Cliente' }}</strong>
                                <span style="font-size: 0.75rem; color: var(--color-charcoal-muted); display: block;">
                                    Destino: {{ $c->pedido->pais_destino ?? 'Turista en Cusco' }}
                                </span>
                            </div>
                            <div style="font-size: 0.85rem; margin-top: 6px; padding-top: 4px; border-top: 1px dashed #E0D5C5;">
                                🎨 <strong>{{ $c->artesano->nombre_completo ?? 'Taller' }}</strong>
                                <span style="font-size: 0.75rem; color: #8C531B; display: block;">
                                    📍 {{ $c->artesano->direccion_arte ?? 'Cusco' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($c->producto)
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <img src="{{ $c->producto->imagen_url }}" alt="{{ $c->producto->nombre_prod }}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 4px;">
                                    <div>
                                        <div style="font-weight: 600; font-size: 0.82rem;">{{ \Illuminate\Support\Str::limit($c->producto->nombre_prod, 22) }}</div>
                                        <div style="font-size: 0.72rem; color: var(--color-charcoal-muted);">S/ {{ number_format($c->producto->precio, 2) }}</div>
                                    </div>
                                </div>
                            @elseif($c->pedido)
                                <div>
                                    <strong style="font-size: 0.82rem;">Pedido #{{ $c->pedido->id }}</strong>
                                    <div style="font-size: 0.72rem; font-family: monospace; color: var(--color-earth-primary);">
                                        {{ $c->pedido->codigo_seguimiento }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--color-charcoal-muted);">
                                        S/ {{ number_format($c->pedido->total, 2) }} PEN
                                    </div>
                                </div>
                            @else
                                <span style="font-size: 0.8rem; color: var(--color-charcoal-muted);">Consulta General</span>
                            @endif
                        </td>
                        <td>
                            <!-- RESUMEN EJECUTIVO INTELIGENTE DEL CHAT -->
                            <div style="background: #FFFDF8; border-left: 3px solid #C88A2C; padding: 10px 14px; border-radius: 4px; font-size: 0.85rem; line-height: 1.45; color: #2C1E11;">
                                {{ $c->resumen_admin ?: 'Conversación recién iniciada. Aún no se registran mensajes de síntesis.' }}
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center; margin-top: 6px; font-size: 0.75rem; color: var(--color-charcoal-muted);">
                                <span>Asunto: <em>{{ $c->asunto }}</em></span>
                                <span>•</span>
                                <span>Categoría: <strong>{{ $c->categoria_consulta }}</strong></span>
                            </div>
                        </td>
                        <td>
                            <div style="margin-bottom: 6px;">
                                @if($c->estado === 'resuelto')
                                    <span class="badge badge-success">✓ Resuelto</span>
                                @elseif($c->estado === 'en_espera')
                                    <span class="badge badge-warning">⏳ En Espera</span>
                                @else
                                    <span class="badge badge-info">💬 Abierto</span>
                                @endif
                            </div>

                            @if($c->necesita_intervencion_hub)
                                <span class="badge badge-danger" style="font-size: 0.7rem; display: block; text-align: center;">
                                    ⚠️ Asistencia HUB
                                </span>
                            @else
                                <span style="font-size: 0.72rem; color: var(--color-fairtrade-green); display: block;">
                                    ✓ Trato directo OK
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <a href="{{ route('chat.show', $c->id) }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.78rem; padding: 4px 8px; text-align: center;">
                                    👁️ Ver Transcripción
                                </a>

                                <!-- Formulario Rápido de Estado para el HUB -->
                                <form action="{{ route('admin.chats.estado', $c->id) }}" method="POST" style="display: flex; gap: 4px;">
                                    @csrf
                                    <select name="estado" style="font-size: 0.75rem; padding: 2px 4px; border-radius: 4px; border: 1px solid var(--color-cream-border);">
                                        <option value="abierto" {{ $c->estado === 'abierto' ? 'selected' : '' }}>Abierto</option>
                                        <option value="en_espera" {{ $c->estado === 'en_espera' ? 'selected' : '' }}>En espera</option>
                                        <option value="resuelto" {{ $c->estado === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline-earth btn-sm" style="font-size: 0.72rem; padding: 2px 6px;" title="Actualizar estado">
                                        ✓
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px;">
                            No hay conversaciones registradas entre clientes y talleres en este momento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $conversaciones->links() }}
    </div>
</div>
@endsection
