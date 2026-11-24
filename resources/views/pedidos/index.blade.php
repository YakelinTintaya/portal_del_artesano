@extends('layouts.app')

@section('title', 'Mis Pedidos | Portal del Artesano')

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px;">
    <h1 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-charcoal-dark); margin-bottom: 24px;">
        Historial de Pedidos y Certificaciones
    </h1>

    @if($pedidos->count() > 0)
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>N° Pedido</th>
                        <th>Fecha</th>
                        <th>Destino</th>
                        <th>Piezas</th>
                        <th>Total</th>
                        <th>Estado Logístico</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedidos as $p)
                        <tr>
                            <td>
                                <strong>#{{ $p->id }}</strong>
                                <div style="font-size: 0.75rem; font-family: monospace; color: var(--color-charcoal-muted);">
                                    {{ $p->codigo_seguimiento }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $p->created_at->format('d/m/Y') }}</div>
                                <div style="font-size: 0.75rem; color: var(--color-charcoal-muted);">{{ $p->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                {{ $p->pais_destino }} ({{ $p->tipo_envio }})
                                @if($p->envoltura_regalo)
                                    <div>
                                        <a href="{{ $p->url_dedicatoria_digital }}" target="_blank" class="badge" style="background: #FDF4E1; color: #8C531B; border: 1px solid #D4A359; font-size: 0.72rem; text-decoration: none; margin-top: 4px; display: inline-block;">
                                            🎁 Dedicatoria QR
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $p->cant_producto }} pieza(s)</td>
                            <td><strong>S/ {{ number_format($p->total, 2) }}</strong></td>
                            <td>
                                <span class="badge {{ $p->estado_badge }}">{{ $p->estado_legible }}</span>
                            </td>
                            <td>
                                <a href="{{ route('pedidos.show', $p->id) }}" class="btn btn-secondary btn-sm">
                                    Ver Detalle & Tracking
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $pedidos->links() }}
        </div>
    @else
        <div style="background-color: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 50px 20px; text-align: center;">
            <p style="font-size: 1.1rem; color: var(--color-charcoal-muted); margin-bottom: 16px;">
                Aún no has realizado pedidos en el Portal del Artesano.
            </p>
            <a href="{{ route('catalogo.index') }}" class="btn btn-primary">
                Ir al Catálogo de Obras
            </a>
        </div>
    @endif
</div>
@endsection
