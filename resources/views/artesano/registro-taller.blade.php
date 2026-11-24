@extends('layouts.app')

@section('title', 'Registro de Taller Artesanal | Portal del Artesano')

@section('content')
<div class="container" style="padding-top: 30px; padding-bottom: 60px; max-width: 700px;">
    <div style="background-color: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
        <h1 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-charcoal-dark); margin-bottom: 8px;">
            Acredita tu Taller Artesanal Cusqueño
        </h1>
        <p style="font-size: 0.9rem; color: var(--color-charcoal-muted); margin-bottom: 24px;">
            Únete a la red de artesanos de Cusco. El HUB logístico validará tu acreditación y habilitará la publicación de tus obras para compradores internacionales.
        </p>

        <form action="{{ route('artesano.taller.guardar') }}" method="POST">
            @csrf

            <div class="form-row-2col">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre del Maestro(a) *:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', Auth::user()->nombre) }}" required>
                </div>

                <div class="form-group">
                    <label for="apellido" class="form-label">Apellidos *:</label>
                    <input type="text" name="apellido" id="apellido" class="form-control" value="{{ old('apellido', Auth::user()->apellido) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="direccion_arte" class="form-label">Comunidad o Barrio Tradicional en Cusco *:</label>
                <input type="text" 
                       name="direccion_arte" 
                       id="direccion_arte" 
                       class="form-control" 
                       placeholder="Ej. Barrio de San Blas, Chinchero, Pisac, San Pedro..." 
                       value="{{ old('direccion_arte') }}" 
                       required>
            </div>

            <div class="form-group">
                <label for="telefono" class="form-label">Teléfono de Coordinación con el HUB *:</label>
                <input type="tel" name="telefono" id="telefono" class="form-control" value="{{ old('telefono', Auth::user()->telefono) }}" required placeholder="+51 984 000 000">
            </div>

            <div class="form-group">
                <label for="historia_taller" class="form-label">Historia y Trayectoria del Taller / Asociación *:</label>
                <textarea name="historia_taller" 
                          id="historia_taller" 
                          rows="4" 
                          class="form-control" 
                          required 
                          placeholder="Cuéntanos sobre tu técnica, tradición familiar, años de experiencia y los materiales que utilizas...">{{ old('historia_taller') }}</textarea>
            </div>

            <div class="form-group">
                <label for="foto_perfil" class="form-label">Foto de Perfil del Maestro / Taller (URL):</label>
                <input type="url" name="foto_perfil" id="foto_perfil" class="form-control" placeholder="https://images.unsplash.com/..." value="{{ old('foto_perfil') }}">
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-accent btn-block" style="padding: 14px;">
                    Enviar Solicitud de Acreditación al HUB
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
