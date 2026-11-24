@extends('layouts.app')

@section('title', 'Bandeja de Mensajes y Consultas en Vivo | Portal del Artesano')

@section('content')
<div class="container" style="padding-top: 36px; padding-bottom: 60px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 26px;">
        <div>
            <h1 style="font-family: var(--font-serif); font-size: 1.85rem; color: var(--color-charcoal-dark); margin: 0;">
                💬 Mensajería & Consultas en Vivo
            </h1>
            <p style="color: var(--color-charcoal-muted); font-size: 0.95rem; margin-top: 4px;">
                @if(Auth::user()->isArtesano())
                    Comunícate directamente con los compradores internacionales interesados en tus obras.
                @else
                    Conversa directamente con los maestros artesanos de Cusco sobre medidas, personalización y elaboración.
                @endif
            </p>
        </div>

        <div>
            @if(Auth::user()->isArtesano())
                <a href="{{ route('artesano.dashboard') }}" class="btn btn-secondary btn-sm">
                    ← Volver a mi Taller
                </a>
            @elseif(Auth::user()->isAdminHub())
                <a href="{{ route('admin.chats.resumen') }}" class="btn btn-primary btn-sm">
                    🔍 Ver Resúmenes del HUB
                </a>
            @else
                <a href="{{ route('catalogo.index') }}" class="btn btn-secondary btn-sm">
                    ← Explorar Catálogo
                </a>
            @endif
        </div>
    </div>

    @if($conversaciones->isEmpty())
        <div style="background: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 50px 20px; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 12px;">💬</div>
            <h3 style="font-family: var(--font-serif); font-size: 1.3rem; color: var(--color-charcoal-dark);">
                Aún no tienes conversaciones activas
            </h3>
            <p style="color: var(--color-charcoal-muted); font-size: 0.95rem; max-width: 500px; margin: 8px auto 20px;">
                @if(Auth::user()->isArtesano())
                    Cuando los turistas o compradores inicien una consulta sobre tus piezas en catálogo, aparecerán aquí para que respondas en vivo.
                @else
                    Puedes iniciar un chat con cualquier maestro artesano desde la ficha de cualquier pieza en nuestro catálogo.
                @endif
            </p>
            @if(!Auth::user()->isArtesano())
                <a href="{{ route('catalogo.index') }}" class="btn btn-primary">
                    Ir al Catálogo de Obras
                </a>
            @endif
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 14px;">
            @foreach($conversaciones as $conv)
                <a href="{{ route('chat.show', $conv->id) }}" style="text-decoration: none; color: inherit; display: block;">
                    <div class="chat-inbox-item" style="background: #FFFFFF; border: 1.5px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 18px 22px; transition: all 0.2s ease; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: var(--shadow-sm);">
                        <div style="display: flex; gap: 16px; align-items: center;">
                            @if(Auth::user()->isArtesano())
                                <div style="width: 52px; height: 52px; border-radius: 50%; background: #FAF3E3; border: 2px solid #D4A359; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                                    👤
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h3 style="font-family: var(--font-serif); font-size: 1.15rem; margin: 0; color: #2C1E11;">
                                            {{ $conv->cliente->nombre_completo ?? 'Comprador' }}
                                        </h3>
                                        <span class="badge {{ $conv->estado === 'resuelto' ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.7rem;">
                                            {{ ucfirst($conv->estado) }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--color-charcoal-muted); margin-top: 2px;">
                                        {{ $conv->asunto }}
                                    </div>
                                    @if($conv->ultimoMensaje)
                                        <div style="font-size: 0.85rem; color: #6E533A; margin-top: 4px; font-style: italic;">
                                            «{{ \Illuminate\Support\Str::limit($conv->ultimoMensaje->mensaje, 85) }}»
                                        </div>
                                    @endif
                                </div>
                            @else
                                <img src="{{ $conv->artesano->foto_perfil ?? 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=400&q=80' }}" 
                                     alt="{{ $conv->artesano->nombre_completo ?? 'Artesano' }}" 
                                     style="width: 54px; height: 54px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-earth-primary);">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h3 style="font-family: var(--font-serif); font-size: 1.15rem; margin: 0; color: #2C1E11;">
                                            Taller: {{ $conv->artesano->nombre_completo ?? 'Maestro Artesano' }}
                                        </h3>
                                        <span class="badge badge-info" style="font-size: 0.7rem;">
                                            📍 {{ $conv->artesano->direccion_arte ?? 'Cusco' }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--color-charcoal-muted); margin-top: 2px;">
                                        {{ $conv->asunto }}
                                    </div>
                                    @if($conv->ultimoMensaje)
                                        <div style="font-size: 0.85rem; color: #6E533A; margin-top: 4px; font-style: italic;">
                                            «{{ \Illuminate\Support\Str::limit($conv->ultimoMensaje->mensaje, 85) }}»
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                            <span style="font-size: 0.78rem; color: var(--color-charcoal-muted);">
                                {{ $conv->ultimo_mensaje_at ? $conv->ultimo_mensaje_at->diffForHumans() : $conv->updated_at->diffForHumans() }}
                            </span>
                            @if($conv->producto)
                                <span style="font-size: 0.75rem; background: #FAF5EB; border: 1px solid #E4D5C0; padding: 2px 8px; border-radius: 4px; color: #8A5A1B;">
                                    🎨 {{ \Illuminate\Support\Str::limit($conv->producto->nombre_prod, 28) }}
                                </span>
                            @elseif($conv->pedido)
                                <span style="font-size: 0.75rem; background: #FAF5EB; border: 1px solid #E4D5C0; padding: 2px 8px; border-radius: 4px; color: #8A5A1B;">
                                    📦 Pedido #{{ $conv->pedido->id }}
                                </span>
                            @endif
                            <span class="btn btn-outline-earth btn-sm" style="font-size: 0.75rem; padding: 3px 10px; margin-top: 4px;">
                                Abrir Chat →
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<style>
.chat-inbox-item:hover {
    border-color: var(--color-earth-primary) !important;
    background-color: #FFFDF9 !important;
    transform: translateY(-2px);
}
</style>
@endsection
