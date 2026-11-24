@extends('layouts.app')

@section('title', 'Chat en Vivo: ' . $conversacion->asunto . ' | Portal del Artesano')

@section('content')
<div class="container" style="padding-top: 24px; padding-bottom: 50px;">
    <!-- Breadcrumb de Navegación -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <nav aria-label="Navegación" style="font-size: 0.88rem; color: var(--color-charcoal-muted);">
            <a href="{{ route('home') }}">Inicio</a> &gt;
            <a href="{{ route('chat.index') }}">Bandeja de Chats</a> &gt;
            <span style="color: var(--color-earth-primary); font-weight: 600;">Conversación #{{ $conversacion->id }}</span>
        </nav>

        <div style="display: flex; gap: 8px; align-items: center;">
            <a href="{{ route('chat.index') }}" class="btn btn-secondary btn-sm">
                ← Volver a Bandeja
            </a>
            @if(Auth::user()->isAdminHub())
                <a href="{{ route('admin.chats.resumen') }}" class="btn btn-primary btn-sm">
                    🔍 Supervisión HUB
                </a>
            @endif
        </div>
    </div>

    <!-- TARJETA CONTENEDORA PRINCIPAL DEL CHAT -->
    <div class="chat-container-card">
        <!-- 1. Cabecera del Chat con Interlocutor -->
        <div class="chat-header-bar">
            <div style="display: flex; align-items: center; gap: 14px;">
                @if($esArtesano)
                    <div class="chat-avatar-placeholder">👤</div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h2 class="chat-participant-name">{{ $conversacion->cliente->nombre_completo ?? 'Cliente Comprador' }}</h2>
                            <span class="badge badge-success" style="font-size: 0.72rem;">Comprador</span>
                        </div>
                        <div style="font-size: 0.82rem; color: var(--color-charcoal-muted); margin-top: 2px;">
                            📍 Destino de entrega: {{ $conversacion->pedido->pais_destino ?? 'Internacional' }}
                        </div>
                    </div>
                @else
                    <img src="{{ $conversacion->artesano->foto_perfil ?? 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=400&q=80' }}" 
                         alt="{{ $conversacion->artesano->nombre_completo }}" 
                         class="chat-artisan-photo">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h2 class="chat-participant-name">{{ $conversacion->artesano->nombre_completo }}</h2>
                            <span class="badge badge-primary" style="font-size: 0.72rem;">Maestro Artesano</span>
                        </div>
                        <div style="font-size: 0.82rem; color: var(--color-charcoal-muted); margin-top: 2px;">
                            📍 {{ $conversacion->artesano->direccion_arte }} • <span style="color: var(--color-fairtrade-green); font-weight: 600;">✓ Taller Validado</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Botón de Sonido e Información -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="btn btn-outline-earth btn-sm" id="btn-toggle-sound" onclick="toggleChatSound()" title="Silenciar / Activar sonido de mensajes">
                    🔔 Sonido Activo
                </button>
            </div>
        </div>

        <!-- 2. Contexto de Obra o Pedido Vinculado -->
        @if($conversacion->producto)
            <div class="chat-context-strip">
                <img src="{{ $conversacion->producto->imagen_url }}" alt="{{ $conversacion->producto->nombre_prod }}" class="context-img">
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #8C531B; letter-spacing: 0.5px;">
                        Pieza en Consulta
                    </div>
                    <strong style="color: #2F1E0F; font-size: 0.95rem;">{{ $conversacion->producto->nombre_prod }}</strong>
                    <span style="font-size: 0.85rem; color: var(--color-charcoal-muted); margin-left: 8px;">
                        (S/ {{ number_format($conversacion->producto->precio, 2) }} PEN)
                    </span>
                    <div style="font-size: 0.75rem; font-family: monospace; color: var(--color-earth-primary);">
                        Certificado: {{ $conversacion->producto->certificado_autenticidad_codigo }}
                    </div>
                </div>
                <a href="{{ route('catalogo.show', $conversacion->producto->id) }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">
                    Ver Ficha
                </a>
            </div>
        @elseif($conversacion->pedido)
            <div class="chat-context-strip">
                <div style="font-size: 1.5rem;">📦</div>
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #8C531B;">
                        Pedido en Trazabilidad
                    </div>
                    <strong style="color: #2F1E0F; font-size: 0.95rem;">
                        Pedido #{{ $conversacion->pedido->id }} • {{ $conversacion->pedido->codigo_seguimiento }}
                    </strong>
                    <div style="font-size: 0.8rem; color: var(--color-charcoal-muted);">
                        Total: S/ {{ number_format($conversacion->pedido->total, 2) }} | Estado: <span class="badge {{ $conversacion->pedido->estado_badge }}">{{ $conversacion->pedido->estado_legible }}</span>
                    </div>
                </div>
                <a href="{{ route('pedidos.show', $conversacion->pedido->id) }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">
                    Ver Pedido
                </a>
            </div>
        @endif

        <!-- 3. Flujo de Mensajes en Vivo -->
        <div class="chat-messages-stream" id="chat-messages-stream">
            @forelse($conversacion->mensajes as $msg)
                @php
                    $esMio = $msg->remitente_id === Auth::id();
                @endphp
                <div class="chat-bubble-row {{ $esMio ? 'row-mio' : 'row-otro' }}" data-msg-id="{{ $msg->id }}">
                    <div class="chat-bubble {{ $esMio ? 'bubble-mio' : 'bubble-otro' }}">
                        <div class="chat-sender-name">
                            {{ $esMio ? 'Tú' : ($msg->remitente->nombre_completo ?? 'Usuario') }}
                        </div>
                        <div class="chat-text">
                            {!! nl2br(e($msg->mensaje)) !!}
                        </div>
                        <div class="chat-meta">
                            <span>{{ $msg->created_at->format('H:i') }}</span>
                            @if($esMio)
                                <span class="chat-check" title="Entregado">✓✓</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div id="no-messages-placeholder" style="text-align: center; color: var(--color-charcoal-muted); padding: 40px 20px;">
                    <div style="font-size: 2.2rem; margin-bottom: 8px;">🤝</div>
                    <p style="margin: 0; font-size: 0.95rem;">
                        Inicia la conversación saludando al maestro artesano. Puedes consultar sobre medidas personalizadas, tintes naturales o tiempos de elaboración.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- 4. Formulario de Envío de Mensaje -->
        <div class="chat-input-bar">
            <form id="chat-message-form" action="{{ route('chat.enviar', $conversacion->id) }}" method="POST" onsubmit="enviarMensajeChat(event)" style="display: flex; gap: 10px; width: 100%; align-items: flex-end;">
                @csrf
                <div style="flex: 1; position: relative;">
                    <textarea 
                        name="mensaje" 
                        id="chat-textarea-input" 
                        class="form-control" 
                        rows="1" 
                        placeholder="Escribe tu mensaje para el taller de Cusco (Presiona Enter para enviar)..." 
                        style="resize: none; min-height: 48px; max-height: 120px; padding: 12px 14px; font-size: 0.95rem; border-radius: 8px;"
                        required
                        onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); enviarMensajeChat(event); }"
                    ></textarea>
                </div>
                <button type="submit" class="btn btn-primary" id="btn-enviar-chat" style="height: 48px; padding: 0 20px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>Enviar</span> <span>➔</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.chat-container-card {
    background: #FFFFFF;
    border: 2px solid #EADBCC;
    border-radius: 16px;
    box-shadow: 0 10px 28px rgba(84, 51, 16, 0.08);
    display: flex;
    flex-direction: column;
    height: 680px;
    max-height: 85vh;
    overflow: hidden;
}

.chat-header-bar {
    background: linear-gradient(135deg, #FFFDF8 0%, #FFF7E8 100%);
    border-bottom: 1.5px solid #ECDAB9;
    padding: 16px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.chat-artisan-photo {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--color-earth-primary);
}

.chat-avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #FAF3E3;
    border: 2px solid #D4A359;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.chat-participant-name {
    font-family: var(--font-serif);
    font-size: 1.2rem;
    color: #2D1D0F;
    margin: 0;
}

.chat-context-strip {
    background: #FAF5EB;
    border-bottom: 1px solid #EBE0D0;
    padding: 10px 22px;
    display: flex;
    align-items: center;
    gap: 14px;
}

.context-img {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #D8C6B1;
}

.chat-messages-stream {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    background: #FCFAF6;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.chat-bubble-row {
    display: flex;
    width: 100%;
}

.row-mio {
    justify-content: flex-end;
}

.row-otro {
    justify-content: flex-start;
}

.chat-bubble {
    max-width: 72%;
    padding: 12px 16px;
    border-radius: 14px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.bubble-mio {
    background: #8A4614;
    color: #FFFFFF;
    border-bottom-right-radius: 2px;
}

.bubble-otro {
    background: #FFFFFF;
    color: #2B1D11;
    border: 1px solid #E6D9C8;
    border-bottom-left-radius: 2px;
}

.chat-sender-name {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    opacity: 0.85;
}

.bubble-mio .chat-sender-name {
    color: #F8EED9;
}

.bubble-otro .chat-sender-name {
    color: #8C531B;
}

.chat-text {
    font-size: 0.95rem;
    line-height: 1.5;
    word-break: break-word;
}

.chat-meta {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    margin-top: 4px;
    opacity: 0.8;
}

.chat-input-bar {
    background: #FFFFFF;
    border-top: 1.5px solid #EBE0D0;
    padding: 16px 20px;
}
</style>

<script>
let soundActive = true;
let lastKnownMessageId = {{ $conversacion->mensajes->max('id') ?? 0 }};
const conversacionId = {{ $conversacion->id }};

function resolveChatUrl(rawUrl) {
    if (typeof window.resolvePortalUrl === 'function') {
        return window.resolvePortalUrl(rawUrl);
    }
    let url = rawUrl || '';
    const subfolderMatch = window.location.pathname.match(/^(\/[^\/]+\/public)/);
    const basePath = subfolderMatch ? subfolderMatch[1] : '';

    if (url.startsWith('/') && !url.startsWith('//')) {
        if (basePath && !url.startsWith(basePath)) {
            url = `${basePath}${url}`;
        }
        return url;
    }

    try {
        const parsed = new URL(url, window.location.origin);
        parsed.protocol = window.location.protocol;
        parsed.host = window.location.host;
        if (basePath && !parsed.pathname.startsWith(basePath)) {
            parsed.pathname = `${basePath}${parsed.pathname}`;
        }
        return parsed.toString();
    } catch (e) {
        return url;
    }
}

const rawUrlNuevos = "{{ route('chat.nuevos', $conversacion->id) }}";
const rawUrlEnviar = "{{ route('chat.enviar', $conversacion->id) }}";
const urlNuevos = resolveChatUrl(rawUrlNuevos);
const urlEnviar = resolveChatUrl(rawUrlEnviar);
const currentUserId = {{ Auth::id() }};

// Auto scroll al final en carga
window.addEventListener('DOMContentLoaded', () => {
    scrollToBottom();
    // Iniciar live polling cada 3.5 segundos
    setInterval(pollNuevosMensajes, 3500);
});

function scrollToBottom() {
    const stream = document.getElementById('chat-messages-stream');
    if (stream) {
        stream.scrollTop = stream.scrollHeight;
    }
}

function toggleChatSound() {
    soundActive = !soundActive;
    const btn = document.getElementById('btn-toggle-sound');
    if (btn) {
        btn.textContent = soundActive ? '🔔 Sonido Activo' : '🔕 Sonido Silenciado';
    }
}

function playIncomingMessageChime() {
    if (!soundActive) return;
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        if (ctx.state === 'suspended') ctx.resume();

        const now = ctx.currentTime;
        // Dos notas rápidas y suaves tipo pop (Sol5 -> Do6)
        const notes = [
            { f: 783.99, t: 0, d: 0.18, g: 0.3 },
            { f: 1046.50, t: 0.1, d: 0.35, g: 0.35 }
        ];

        notes.forEach(n => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(n.f, now + n.t);
            gain.gain.setValueAtTime(n.g, now + n.t);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + n.t + n.d);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now + n.t);
            osc.stop(now + n.t + n.d);
        });
    } catch (e) {
        console.warn('Audio error:', e);
    }
}

async function enviarMensajeChat(e) {
    if (e && typeof e.preventDefault === 'function') {
        e.preventDefault();
    }

    const form = document.getElementById('chat-message-form');
    const textarea = document.getElementById('chat-textarea-input');
    const texto = textarea ? textarea.value.trim() : '';
    if (!texto) return;

    const btn = document.getElementById('btn-enviar-chat');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
               || document.querySelector('#chat-message-form input[name="_token"]')?.value
               || '';

    try {
        const res = await fetch(urlEnviar, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                _token: token,
                mensaje: texto
            })
        });

        if (!res.ok) {
            console.warn('Respuesta HTTP no 200 (' + res.status + '). Aplicando fallback de envío por formulario.');
            if (form) {
                form.submit();
                return;
            }
            throw new Error(`Error del servidor (HTTP ${res.status})`);
        }

        const data = await res.json();
        if (data.status === 'success' && data.mensaje) {
            textarea.value = '';
            textarea.style.height = 'auto';
            if (data.mensaje.id > lastKnownMessageId) {
                lastKnownMessageId = data.mensaje.id;
            }
            appendMessageBubble(data.mensaje, true);
            scrollToBottom();
        } else {
            throw new Error(data.error || 'No se pudo enviar el mensaje.');
        }
    } catch (err) {
        console.error('Error en fetch chat:', err);
        // Fallback final infalible: envío de formulario nativo
        if (form) {
            form.submit();
            return;
        }
        alert('No se pudo enviar el mensaje: ' + err.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    }
}

function pollNuevosMensajes() {
    fetch(`${urlNuevos}?ultimo_id=${lastKnownMessageId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.nuevos && data.nuevos.length > 0) {
            let huboMensajeDeOtro = false;
            data.nuevos.forEach(msg => {
                if (msg.id > lastKnownMessageId) {
                    lastKnownMessageId = msg.id;
                }
                const esMio = (msg.remitente_id === currentUserId);
                appendMessageBubble(msg, esMio);
                if (!esMio) {
                    huboMensajeDeOtro = true;
                }
            });

            if (huboMensajeDeOtro) {
                playIncomingMessageChime();
            }

            scrollToBottom();
        }
    })
    .catch(err => console.debug('Polling chat error:', err));
}

function appendMessageBubble(msg, esMio) {
    const stream = document.getElementById('chat-messages-stream');
    if (!stream) return;

    // Remover placeholder si existía
    const placeholder = document.getElementById('no-messages-placeholder');
    if (placeholder) placeholder.remove();

    // Evitar duplicados por ID
    if (document.querySelector(`[data-msg-id="${msg.id}"]`)) return;

    const row = document.createElement('div');
    row.className = `chat-bubble-row ${esMio ? 'row-mio' : 'row-otro'}`;
    row.setAttribute('data-msg-id', msg.id);

    row.innerHTML = `
        <div class="chat-bubble ${esMio ? 'bubble-mio' : 'bubble-otro'}">
            <div class="chat-sender-name">${esMio ? 'Tú' : msg.remitente_nombre}</div>
            <div class="chat-text">${msg.mensaje.replace(/\n/g, '<br>')}</div>
            <div class="chat-meta">
                <span>${msg.hora}</span>
                ${esMio ? '<span class="chat-check" title="Entregado">✓✓</span>' : ''}
            </div>
        </div>
    `;

    stream.appendChild(row);
}
</script>
@endsection
