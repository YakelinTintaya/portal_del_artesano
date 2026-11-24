@extends('layouts.app')

@section('title', ($modo === 'crear' ? 'Registrar Nueva Pieza' : 'Editar Pieza') . ' | Portal del Artesano')

@php
    $dimLargo = old('dim_largo', $dimensiones['largo'] ?? '');
    $dimAncho = old('dim_ancho', $dimensiones['ancho'] ?? '');
    $dimAlto = old('dim_alto', $dimensiones['alto'] ?? '');
    $dimUnidad = old('dim_unidad', $dimensiones['unidad'] ?? 'cm');
    $dimTextoLibre = old('dim_texto_libre', $dimensiones['texto_libre'] ?? '');
    $modoLibre = old('modo_tamano', ($dimensiones['es_personalizado'] ?? false) ? 'libre' : 'recuadros') === 'libre';
    $tamanoActual = old('tamano', $producto->tamano ?? '');
@endphp

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px; max-width: 800px;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('artesano.dashboard') }}" class="btn btn-secondary btn-sm">← Volver a Mi Taller</a>
    </div>

    <div style="background-color: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
        <h1 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-charcoal-dark); margin-bottom: 10px;">
            {{ $modo === 'crear' ? 'Registrar Nueva Pieza de Arte' : 'Actualizar Pieza: ' . $producto->nombre_prod }}
        </h1>
        <p style="font-size: 0.9rem; color: var(--color-charcoal-muted); margin-bottom: 24px;">
            Todas las piezas nuevas o con cambio de precio pasan por el proceso de curaduría y validación del HUB Logístico de Cusco antes de publicarse en la vitrina internacional.
        </p>

        <form action="{{ $modo === 'crear' ? route('artesano.productos.store') : route('artesano.productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($modo === 'editar')
                @method('PUT')
            @endif

            @if ($errors->any())
                <div style="background-color: #FDEDEC; border-left: 4px solid var(--color-danger); padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px;">
                    <p style="font-weight: 600; color: var(--color-danger); margin-bottom: 6px;">Por favor verifica los siguientes campos:</p>
                    <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem; color: #78281F;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="nombre_prod" class="form-label">Nombre de la Pieza Tradicional *:</label>
                <input type="text" 
                       name="nombre_prod" 
                       id="nombre_prod" 
                       class="form-control" 
                       value="{{ old('nombre_prod', $producto->nombre_prod ?? '') }}" 
                       required 
                       placeholder="Ej. Poncho Ceremonial en Baby Alpaca con Pallay">
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label for="categoria" class="form-label">Categoría *:</label>
                    <select name="categoria" id="categoria" class="form-control" required>
                        @foreach(['Textiles', 'Platería', 'Cerámica', 'Imaginería'] as $cat)
                            <option value="{{ $cat }}" {{ old('categoria', $producto->categoria ?? '') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="subtipo" class="form-label">Subtipo (Ropa, Decoración, Accesorios):</label>
                    <input type="text" 
                           name="subtipo" 
                           id="subtipo" 
                           class="form-control" 
                           value="{{ old('subtipo', $producto->subtipo ?? '') }}" 
                           placeholder="Ej. Decoración, Ropa, Joyería">
                </div>
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label for="precio" class="form-label">Precio Base Justo (PEN S/) *:</label>
                    <input type="number" 
                           step="0.50" 
                           min="5" 
                           name="precio" 
                           id="precio" 
                           class="form-control" 
                           value="{{ old('precio', $producto->precio ?? '') }}" 
                           required 
                           placeholder="Ej. 180.00">
                    <span style="font-size: 0.78rem; color: var(--color-fairtrade-green);">
                        Recibirás directamente el 85% del valor de venta.
                    </span>
                </div>

                <div class="form-group">
                    <label for="stock" class="form-label">Stock Disponible en Taller *:</label>
                    <input type="number" 
                           min="0" 
                           name="stock" 
                           id="stock" 
                           class="form-control" 
                           value="{{ old('stock', $producto->stock ?? 1) }}" 
                           required>
                </div>
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label class="form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span>Dimensiones / Tamaño:</span>
                        <button type="button" 
                                id="btn-toggle-modo-libre" 
                                style="background: none; border: none; font-size: 0.76rem; color: var(--color-terracotta); cursor: pointer; text-decoration: underline; padding: 0;">
                            {{ $modoLibre ? '📐 Usar recuadros numéricos' : '✏️ ¿Talla única o texto?' }}
                        </button>
                    </label>

                    <!-- CONTENEDOR 1: Recuadros Separados (Largo × Ancho × Alto + Unidad) -->
                    <div id="contenedor-recuadros-tamano" style="display: {{ $modoLibre ? 'none' : 'block' }};">
                        <div style="background: var(--color-white); border: 1.5px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 8px 10px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                                <!-- Recuadro 1: Largo -->
                                <div style="flex: 1; min-width: 0;">
                                    <label for="dim_largo" style="display: block; font-size: 0.68rem; font-weight: 700; color: var(--color-charcoal-muted); text-transform: uppercase; margin-bottom: 3px; text-align: center; letter-spacing: 0.5px;">
                                        Largo
                                    </label>
                                    <input type="number" 
                                           step="any" 
                                           min="0" 
                                           id="dim_largo" 
                                           name="dim_largo" 
                                           class="form-control" 
                                           placeholder="180" 
                                           value="{{ $dimLargo }}" 
                                           style="text-align: center; font-weight: 600; padding: 7px 4px; font-size: 0.92rem; background: var(--color-cream-surface);">
                                </div>

                                <span style="font-weight: 700; color: var(--color-charcoal-muted); font-size: 0.95rem; margin-top: 14px; user-select: none;">×</span>

                                <!-- Recuadro 2: Ancho -->
                                <div style="flex: 1; min-width: 0;">
                                    <label for="dim_ancho" style="display: block; font-size: 0.68rem; font-weight: 700; color: var(--color-charcoal-muted); text-transform: uppercase; margin-bottom: 3px; text-align: center; letter-spacing: 0.5px;">
                                        Ancho
                                    </label>
                                    <input type="number" 
                                           step="any" 
                                           min="0" 
                                           id="dim_ancho" 
                                           name="dim_ancho" 
                                           class="form-control" 
                                           placeholder="30" 
                                           value="{{ $dimAncho }}" 
                                           style="text-align: center; font-weight: 600; padding: 7px 4px; font-size: 0.92rem; background: var(--color-cream-surface);">
                                </div>

                                <span style="font-weight: 700; color: var(--color-charcoal-muted); font-size: 0.95rem; margin-top: 14px; user-select: none;">×</span>

                                <!-- Recuadro 3: Alto (Opcional) -->
                                <div style="flex: 1; min-width: 0;">
                                    <label for="dim_alto" style="display: block; font-size: 0.68rem; font-weight: 700; color: var(--color-charcoal-muted); text-transform: uppercase; margin-bottom: 3px; text-align: center; letter-spacing: 0.5px;">
                                        Alto
                                    </label>
                                    <input type="number" 
                                           step="any" 
                                           min="0" 
                                           id="dim_alto" 
                                           name="dim_alto" 
                                           class="form-control" 
                                           placeholder="Opc." 
                                           value="{{ $dimAlto }}" 
                                           style="text-align: center; font-weight: 600; padding: 7px 4px; font-size: 0.92rem; background: var(--color-cream-surface);">
                                </div>

                                <!-- Recuadro 4: Unidad -->
                                <div style="width: 66px; flex-shrink: 0;">
                                    <label for="dim_unidad" style="display: block; font-size: 0.68rem; font-weight: 700; color: var(--color-charcoal-muted); text-transform: uppercase; margin-bottom: 3px; text-align: center; letter-spacing: 0.5px;">
                                        Unidad
                                    </label>
                                    <select id="dim_unidad" 
                                            name="dim_unidad" 
                                            class="form-control" 
                                            style="padding: 7px 2px; font-weight: 700; text-align: center; font-size: 0.86rem; background: var(--color-cream-surface);">
                                        <option value="cm" {{ $dimUnidad === 'cm' ? 'selected' : '' }}>cm</option>
                                        <option value="m" {{ $dimUnidad === 'm' ? 'selected' : '' }}>m</option>
                                        <option value="mm" {{ $dimUnidad === 'mm' ? 'selected' : '' }}>mm</option>
                                        <option value="pulg" {{ $dimUnidad === 'pulg' ? 'selected' : '' }}>pulg</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENEDOR 2: Texto libre alternativo -->
                    <div id="contenedor-texto-tamano" style="display: {{ $modoLibre ? 'block' : 'none' }};">
                        <input type="text" 
                               id="dim_texto_libre" 
                               name="dim_texto_libre" 
                               class="form-control" 
                               placeholder="Ej. Talla Única, Regulable, o 250 gramos" 
                               value="{{ $dimTextoLibre ?: ($modoLibre ? $tamanoActual : '') }}">
                    </div>

                    <!-- Input oculto para 'tamano' y 'modo_tamano' -->
                    <input type="hidden" name="tamano" id="tamano" value="{{ $tamanoActual }}">
                    <input type="hidden" name="modo_tamano" id="modo_tamano" value="{{ $modoLibre ? 'libre' : 'recuadros' }}">

                    <!-- Vista previa en tiempo real -->
                    <div style="font-size: 0.76rem; color: var(--color-charcoal-muted); margin-top: 5px; display: flex; align-items: center; justify-content: space-between;">
                        <span>Formato en vitrina:</span>
                        <strong id="preview-tamano" style="color: var(--color-earth-primary);">
                            {{ $tamanoActual ?: 'Ej. 180 x 30 cm' }}
                        </strong>
                    </div>
                </div>

                <div class="form-group">
                    <label for="colores" class="form-label">Pigmentación / Colores:</label>
                    <input type="text" 
                           name="colores" 
                           id="colores" 
                           class="form-control" 
                           value="{{ old('colores', $producto->colores ?? '') }}" 
                           placeholder="Ej. Cochinilla, Vicuña natural">
                </div>
            </div>

            <div class="form-group">
                <label for="materiales_origen" class="form-label">Materiales Empleados y Origen Andino *:</label>
                <input type="text" 
                       name="materiales_origen" 
                       id="materiales_origen" 
                       class="form-control" 
                       value="{{ old('materiales_origen', $producto->materiales_origen ?? '') }}" 
                       required 
                       placeholder="Ej. Lana 100% Baby Alpaca virgen, tintes de chilca y qolle">
            </div>

            <div class="form-group">
                <label for="descripcion_prod" class="form-label">Descripción Cultural y Técnica Artesanal *:</label>
                <textarea name="descripcion_prod" 
                          id="descripcion_prod" 
                          rows="4" 
                          class="form-control" 
                          required 
                          placeholder="Describe el significado de la iconografía, el tiempo de elaboración y las técnicas empleadas...">{{ old('descripcion_prod', $producto->descripcion_prod ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <span>Fotografía de la Obra Artesanal *:</span>
                    <span style="font-size: 0.78rem; color: var(--color-terracotta); font-weight: 600;">Formatos: JPG, PNG, WEBP (Máx. 5MB)</span>
                </label>

                <!-- Caja de Subida de Archivo con Previsualización -->
                <div class="upload-dropzone" id="upload-dropzone" style="border: 2px dashed var(--color-cream-border); border-radius: var(--radius-md); padding: 24px 16px; text-align: center; background-color: var(--color-cream-surface); cursor: pointer; transition: var(--transition); position: relative;">
                    <input type="file" 
                           name="imagen_archivo" 
                           id="imagen_archivo" 
                           accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" 
                           style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 5;"
                           {{ $modo === 'crear' && !old('imagen_url') ? 'required' : '' }}>
                    
                    <div id="upload-placeholder" style="{{ isset($producto) && $producto->imagen_url ? 'display: none;' : '' }}">
                        <div style="font-size: 2.2rem; margin-bottom: 8px;">📷</div>
                        <p style="font-weight: 600; color: var(--color-charcoal-dark); margin-bottom: 4px;">
                            Haz clic o arrastra aquí la fotografía de tu pieza
                        </p>
                        <p style="font-size: 0.8rem; color: var(--color-charcoal-muted); margin-bottom: 12px;">
                            Se recomienda fondo neutro y buena iluminación natural para la curaduría del HUB.
                        </p>
                        <span class="btn btn-secondary btn-sm" style="pointer-events: none;">
                            Seleccionar Archivo de tu Dispositivo
                        </span>
                    </div>

                    <!-- Contenedor de previsualización inmediata -->
                    <div id="upload-preview-container" style="{{ isset($producto) && $producto->imagen_url ? 'display: block;' : 'display: none;' }}">
                        <img id="image-preview" 
                             src="{{ old('imagen_url', $producto->imagen_url ?? '') }}" 
                             alt="Vista previa de la obra" 
                             style="max-height: 220px; max-width: 100%; border-radius: var(--radius-sm); object-fit: contain; box-shadow: var(--shadow-sm); border: 1px solid var(--color-cream-border); background-color: var(--color-white); padding: 4px;">
                        <div style="margin-top: 10px;">
                            <span id="preview-file-name" style="font-size: 0.85rem; font-weight: 600; color: var(--color-earth-primary);">
                                {{ isset($producto) && $producto->imagen_url ? 'Fotografía actual registrada' : '' }}
                            </span>
                            <p style="font-size: 0.78rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                                (Haz clic sobre el recuadro para seleccionar una foto diferente)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Opción complementaria: URL web externa -->
                <details style="margin-top: 12px; font-size: 0.82rem; color: var(--color-charcoal-muted);">
                    <summary style="cursor: pointer; color: var(--color-earth-primary); font-weight: 600;">
                        🔗 ¿Prefieres ingresar una URL externa de internet?
                    </summary>
                    <div style="margin-top: 8px;">
                        <input type="url" 
                               name="imagen_url" 
                               id="imagen_url" 
                               class="form-control" 
                               value="{{ old('imagen_url', $producto->imagen_url ?? '') }}" 
                               placeholder="https://images.unsplash.com/..."
                               style="font-size: 0.85rem;">
                        <span style="font-size: 0.75rem; color: var(--color-charcoal-muted);">
                            Nota: Si subes un archivo en el recuadro de arriba, este tendrá prioridad.
                        </span>
                    </div>
                </details>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-accent btn-block" style="padding: 14px;">
                    {{ $modo === 'crear' ? 'Enviar Pieza a Curaduría del HUB' : 'Guardar Cambios en la Pieza' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('imagen_archivo');
        const urlInput = document.getElementById('imagen_url');
        const placeholder = document.getElementById('upload-placeholder');
        const previewContainer = document.getElementById('upload-preview-container');
        const previewImg = document.getElementById('image-preview');
        const fileNameSpan = document.getElementById('preview-file-name');
        const dropzone = document.getElementById('upload-dropzone');

        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    if (!file.type.match('image.*')) {
                        alert('Por favor selecciona un archivo de imagen válido (JPG, PNG, WEBP, GIF).');
                        fileInput.value = '';
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        alert('La imagen seleccionada supera el tamaño máximo permitido de 5MB.');
                        fileInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        previewImg.src = event.target.result;
                        fileNameSpan.textContent = 'Archivo seleccionado: ' + file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                        placeholder.style.display = 'none';
                        previewContainer.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Resaltar caja al arrastrar archivos
            ['dragenter', 'dragover'].forEach(function(evt) {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    dropzone.style.borderColor = 'var(--color-earth-primary)';
                    dropzone.style.backgroundColor = '#FFFFFF';
                });
            });

            ['dragleave', 'drop'].forEach(function(evt) {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    dropzone.style.borderColor = 'var(--color-cream-border)';
                    dropzone.style.backgroundColor = 'var(--color-cream-surface)';
                });
            });
        }

        if (urlInput) {
            urlInput.addEventListener('input', function () {
                if (urlInput.value.trim() && (!fileInput.files || fileInput.files.length === 0)) {
                    previewImg.src = urlInput.value.trim();
                    fileNameSpan.textContent = 'Fotografía desde URL externa';
                    placeholder.style.display = 'none';
                    previewContainer.style.display = 'block';
                    if (fileInput) {
                        fileInput.removeAttribute('required');
                    }
                }
            });
        }

        // Lógica interactiva de recuadros de dimensiones
        const dimLargo = document.getElementById('dim_largo');
        const dimAncho = document.getElementById('dim_ancho');
        const dimAlto = document.getElementById('dim_alto');
        const dimUnidad = document.getElementById('dim_unidad');
        const dimTextoLibre = document.getElementById('dim_texto_libre');
        const hiddenTamano = document.getElementById('tamano');
        const hiddenModo = document.getElementById('modo_tamano');
        const previewTamano = document.getElementById('preview-tamano');
        const btnToggleModo = document.getElementById('btn-toggle-modo-libre');
        const contRecuadros = document.getElementById('contenedor-recuadros-tamano');
        const contTexto = document.getElementById('contenedor-texto-tamano');

        function actualizarDimensiones() {
            if (hiddenModo && hiddenModo.value === 'libre') {
                const txt = dimTextoLibre ? dimTextoLibre.value.trim() : '';
                if (hiddenTamano) hiddenTamano.value = txt;
                if (previewTamano) previewTamano.textContent = txt || 'Sin medidas especificadas';
            } else {
                const largo = dimLargo ? dimLargo.value.trim() : '';
                const ancho = dimAncho ? dimAncho.value.trim() : '';
                const alto = dimAlto ? dimAlto.value.trim() : '';
                const unidad = dimUnidad ? dimUnidad.value : 'cm';

                const partes = [];
                if (largo) partes.push(largo);
                if (ancho) partes.push(ancho);
                if (alto) partes.push(alto);

                if (partes.length > 0) {
                    const str = partes.join(' x ') + ' ' + unidad;
                    if (hiddenTamano) hiddenTamano.value = str;
                    if (previewTamano) previewTamano.textContent = str;
                } else {
                    if (hiddenTamano) hiddenTamano.value = '';
                    if (previewTamano) previewTamano.textContent = 'Sin medidas';
                }
            }
        }

        if (btnToggleModo && hiddenModo && contRecuadros && contTexto) {
            btnToggleModo.addEventListener('click', function () {
                if (hiddenModo.value === 'recuadros') {
                    hiddenModo.value = 'libre';
                    contRecuadros.style.display = 'none';
                    contTexto.style.display = 'block';
                    btnToggleModo.textContent = '📐 Usar recuadros numéricos';
                    if (dimTextoLibre && !dimTextoLibre.value.trim()) {
                        dimTextoLibre.value = hiddenTamano.value || 'Único';
                    }
                    if (dimTextoLibre) dimTextoLibre.focus();
                } else {
                    hiddenModo.value = 'recuadros';
                    contTexto.style.display = 'none';
                    contRecuadros.style.display = 'block';
                    btnToggleModo.textContent = '✏️ ¿Talla única o texto?';
                }
                actualizarDimensiones();
            });
        }

        [dimLargo, dimAncho, dimAlto, dimUnidad].forEach(function (el) {
            if (el) {
                el.addEventListener('input', actualizarDimensiones);
                el.addEventListener('change', actualizarDimensiones);
            }
        });

        if (dimTextoLibre) {
            dimTextoLibre.addEventListener('input', actualizarDimensiones);
        }

        // Ejecutar cálculo inicial
        actualizarDimensiones();
    });
</script>

<style>
    @media (max-width: 640px) {
        .form-row-2col {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
