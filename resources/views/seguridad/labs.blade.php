@extends('layouts.app')

@section('title', 'Laboratorio de Vulnerabilidades Web (PortSwigger) | Portal del Artesano')

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 70px;">
    <!-- Breadcrumb -->
    <nav aria-label="Ruta de navegación" style="margin-bottom: 20px; font-size: 0.85rem; color: var(--color-charcoal-muted);">
        <ol style="display: flex; gap: 8px; list-style: none;">
            <li><a href="{{ route('home') }}" style="color: var(--color-earth-primary);">Inicio</a></li>
            <li>/</li>
            <li aria-current="page" style="color: var(--color-charcoal-dark); font-weight: 600;">Laboratorio de Seguridad (PortSwigger Web Security)</li>
        </ol>
    </nav>

    <!-- Encabezado del Módulo de Seguridad -->
    <div style="background: linear-gradient(135deg, #1C2421 0%, #2A3832 100%); color: #FFFFFF; border-radius: var(--radius-md); padding: 32px; margin-bottom: 36px; box-shadow: var(--shadow-md); border-left: 5px solid var(--color-gold);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <span class="badge" style="background-color: var(--color-gold); color: #1C2421; font-weight: 700; margin-bottom: 12px; display: inline-block;">
                    🛡️ Desarrollo de Aplicaciones Seguras (DAS)
                </span>
                <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; margin-bottom: 10px;">
                    Laboratorio de Vulnerabilidades Web
                </h1>
                <p style="font-size: 0.95rem; color: rgba(255, 255, 255, 0.85); max-width: 800px; line-height: 1.6;">
                    Demostración práctica, análisis de riesgos y mitigación técnica de los <strong>10 laboratorios clave de PortSwigger Web Security Academy</strong> implementados y neutralizados en la arquitectura de <strong>Laravel 11 y Eloquent ORM</strong>.
                </p>
            </div>
            <div>
                <a href="https://portswigger.net/web-security/all-labs" target="_blank" rel="noopener noreferrer" class="btn btn-outline-earth" style="color: #FFFFFF; border-color: var(--color-gold); display: inline-flex; align-items: center; gap: 8px;">
                    <span>🌐</span> Ver Catálogo Oficial PortSwigger →
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros de Categorías de Seguridad -->
    <div style="display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary btn-sm lab-tab-btn active" data-cat="all" onclick="filtrarLabs('all')">
            Todos los Labs (10)
        </button>
        <button type="button" class="btn btn-secondary btn-sm lab-tab-btn" data-cat="sql" onclick="filtrarLabs('sql')">
            1. SQL Injection (2)
        </button>
        <button type="button" class="btn btn-secondary btn-sm lab-tab-btn" data-cat="xss" onclick="filtrarLabs('xss')">
            2. XSS (3)
        </button>
        <button type="button" class="btn btn-secondary btn-sm lab-tab-btn" data-cat="access" onclick="filtrarLabs('access')">
            3. Broken Access Control (3)
        </button>
        <button type="button" class="btn btn-secondary btn-sm lab-tab-btn" data-cat="llm" onclick="filtrarLabs('llm')">
            4. Web LLM Attacks (1)
        </button>
        <button type="button" class="btn btn-secondary btn-sm lab-tab-btn" data-cat="auth" onclick="filtrarLabs('auth')">
            5. Authentication (1)
        </button>
    </div>

    <!-- Lista de los 10 Laboratorios -->
    <div style="display: flex; flex-direction: column; gap: 28px;">
        @foreach($labs as $id => $lab)
            @php
                $catClase = match(true) {
                    str_contains($lab['categoria'], 'SQL') => 'sql',
                    str_contains($lab['categoria'], 'XSS') => 'xss',
                    str_contains($lab['categoria'], 'Access') => 'access',
                    str_contains($lab['categoria'], 'LLM') => 'llm',
                    default => 'auth'
                };
            @endphp
            <div class="cart-items-card lab-card lab-cat-{{ $catClase }}" id="lab-card-{{ $id }}" style="border-left: 4px solid var(--color-earth-primary);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; border-bottom: 1px solid var(--color-cream-border); padding-bottom: 12px;">
                    <div>
                        <span class="badge badge-info" style="font-size: 0.72rem; margin-bottom: 6px;">
                            {{ $lab['categoria'] }}
                        </span>
                        <h2 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-charcoal-dark); margin: 4px 0;">
                            {{ $lab['titulo'] }}
                        </h2>
                        <a href="{{ $lab['url_portswigger'] }}" target="_blank" rel="noopener noreferrer" style="font-size: 0.78rem; color: var(--color-earth-primary); text-decoration: underline;">
                            🔗 Ver especificación en PortSwigger Academy
                        </a>
                    </div>
                    <span class="badge badge-success" style="font-size: 0.78rem; padding: 6px 12px;">
                        ✓ Mitigado en Laravel 11
                    </span>
                </div>

                <div class="form-row-2col" style="gap: 20px; margin-bottom: 16px;">
                    <!-- Columna 1: Vector y Escenario Vulnerable -->
                    <div style="background: #FFF5F5; border: 1px solid #FFD0D0; border-radius: var(--radius-sm); padding: 14px;">
                        <strong style="color: #B8332A; display: flex; align-items: center; gap: 6px; font-size: 0.88rem; margin-bottom: 6px;">
                            ⚠️ Escenario Vulnerable (PortSwigger)
                        </strong>
                        <p style="font-size: 0.84rem; color: var(--color-charcoal-dark); margin-bottom: 8px; line-height: 1.4;">
                            {{ $lab['explicacion_vulnerable'] }}
                        </p>
                        <div style="font-size: 0.78rem; color: #555;">
                            <strong>Vector de Entrada:</strong> <code>{{ $lab['vector'] }}</code>
                        </div>
                    </div>

                    <!-- Columna 2: Mitigación en Laravel y Código Seguro -->
                    <div style="background: #F4FAF5; border: 1px solid #C8E6C9; border-radius: var(--radius-sm); padding: 14px;">
                        <strong style="color: #2E6B38; display: flex; align-items: center; gap: 6px; font-size: 0.88rem; margin-bottom: 6px;">
                            🛡️ Defensa en Portal del Artesano
                        </strong>
                        <p style="font-size: 0.84rem; color: var(--color-charcoal-dark); margin-bottom: 8px; line-height: 1.4;">
                            {{ $lab['defensa_laravel'] }}
                        </p>
                        <div style="font-size: 0.78rem; color: #444;">
                            <strong>Ubicación:</strong> <code>{{ $lab['archivo_codigo'] }}</code>
                        </div>
                    </div>
                </div>

                <!-- Bloque de Código Seguro -->
                <div style="background: #202428; color: #E6EDF3; border-radius: var(--radius-sm); padding: 10px 14px; font-family: monospace; font-size: 0.82rem; margin-bottom: 16px; overflow-x: auto;">
                    <span style="color: #7EE787;">// Implementación Segura en Código:</span><br>
                    {{ $lab['codigo_laravel'] }}
                </div>

                <!-- Panel de Simulación en Vivo -->
                <div style="background: var(--color-cream-surface); border: 1px dashed var(--color-cream-border); border-radius: var(--radius-sm); padding: 14px;">
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 240px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--color-charcoal-muted); margin-bottom: 3px;">
                                Payload de Ataque para Probar:
                            </label>
                            <input type="text" 
                                   id="payload-input-{{ $id }}" 
                                   class="form-control" 
                                   value="{{ $lab['payload_ejemplo'] }}" 
                                   style="font-family: monospace; font-size: 0.85rem; padding: 6px 10px;">
                        </div>
                        <div style="align-self: flex-end;">
                            <button type="button" 
                                    class="btn btn-primary btn-sm" 
                                    id="btn-test-{{ $id }}" 
                                    onclick="ejecutarTestLab({{ $id }})">
                                ⚡ Ejecutar Prueba en Vivo
                            </button>
                        </div>
                    </div>

                    <!-- Contenedor del Resultado AJAX -->
                    <div id="resultado-lab-{{ $id }}" style="display: none; margin-top: 12px; padding: 10px 14px; border-radius: var(--radius-sm); font-size: 0.85rem; line-height: 1.4;">
                        <!-- Se llena dinámicamente vía JavaScript -->
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function filtrarLabs(cat) {
    document.querySelectorAll('.lab-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-cat') === cat);
    });

    document.querySelectorAll('.lab-card').forEach(card => {
        if (cat === 'all' || card.classList.contains('lab-cat-' + cat)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function ejecutarTestLab(id) {
    const input = document.getElementById('payload-input-' + id);
    const btn = document.getElementById('btn-test-' + id);
    const resultBox = document.getElementById('resultado-lab-' + id);
    const payload = input ? input.value : '';

    if (!btn || !resultBox) return;

    btn.disabled = true;
    btn.innerText = 'Probando...';
    resultBox.style.display = 'none';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('{{ url("/seguridad/labs/test") }}/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ payload: payload })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = '⚡ Ejecutar Prueba en Vivo';

        resultBox.style.display = 'block';
        if (data.resultado && data.resultado.bloqueado) {
            resultBox.style.background = '#E8F5E9';
            resultBox.style.border = '1px solid #A5D6A7';
            resultBox.style.color = '#1B5E20';
            resultBox.innerHTML = `
                <div style="font-weight: 700; margin-bottom: 4px;">
                    🛡️ ${data.resultado.estado_seguridad}
                </div>
                <div style="font-size: 0.82rem; margin-bottom: 4px;">
                    <strong>Comportamiento PortSwigger Vulnerable:</strong> ${data.resultado.comportamiento_vulnerable_portswigger}
                </div>
                <div style="font-size: 0.82rem;">
                    <strong>Respuesta Real de Laravel:</strong> ${data.resultado.resultado_laravel}
                </div>
            `;
        } else {
            resultBox.style.background = '#FFEBEE';
            resultBox.style.border = '1px solid #FFCDD2';
            resultBox.style.color = '#B71C1C';
            resultBox.innerHTML = `
                <div style="font-weight: 700;">⚠️ Advertencia en Simulación</div>
                <div style="font-size: 0.82rem;">${data.resultado ? data.resultado.resultado_laravel : 'Verificar configuración'}</div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = '⚡ Ejecutar Prueba en Vivo';
        resultBox.style.display = 'block';
        resultBox.style.background = '#FFF3E0';
        resultBox.style.color = '#E65100';
        resultBox.innerText = 'Error al ejecutar simulación: ' + err.message;
    });
}
</script>
@endpush
@endsection
