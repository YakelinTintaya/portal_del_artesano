@extends('layouts.app')

@section('title', 'Registro de Cuenta | Portal del Artesano Cusco')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 540px;">
    <div style="background-color: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 32px; box-shadow: var(--shadow-sm);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 2.5rem; margin-bottom: 8px;" aria-hidden="true">❖</div>
            <h1 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-charcoal-dark);">
                Crear una Cuenta
            </h1>
            <p style="font-size: 0.88rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                Compra con certificación digital o comercializa tus obras de arte andino.
            </p>
        </div>

        <form action="{{ route('register.post') }}" method="POST">
            @csrf

            <div class="form-row-2col">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre(s) *:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <span style="font-size: 0.78rem; color: var(--color-danger);">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="apellido" class="form-label">Apellido(s) *:</label>
                    <input type="text" name="apellido" id="apellido" class="form-control" value="{{ old('apellido') }}" required>
                    @error('apellido')
                        <span style="font-size: 0.78rem; color: var(--color-danger);">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="correo" class="form-label">Correo Electrónico *:</label>
                <input type="email" name="correo" id="correo" class="form-control" value="{{ old('correo') }}" required placeholder="tu-correo@ejemplo.com">
                @error('correo')
                    <span style="font-size: 0.78rem; color: var(--color-danger);">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="+51 984 000 000">
                </div>

                <div class="form-group">
                    <label for="rol" class="form-label">Tipo de Usuario *:</label>
                    <select name="rol" id="rol" class="form-control" required>
                        <option value="cliente" {{ old('rol') === 'cliente' ? 'selected' : '' }}>Cliente Comprador</option>
                        <option value="artesano" {{ old('rol') === 'artesano' ? 'selected' : '' }}>Maestro Artesano (Taller)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="direccion" class="form-label">Dirección habitual / Ciudad:</label>
                <input type="text" name="direccion" id="direccion" class="form-control" value="{{ old('direccion') }}" placeholder="Ej. San Blas, Cusco o París, Francia">
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña *:</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="password" id="password" class="form-control" required minlength="6" autocomplete="new-password">
                        <button type="button" class="btn-toggle-password" aria-label="Mostrar u ocultar contraseña" title="Mostrar u ocultar contraseña">
                            <svg class="eye-icon eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-icon eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span style="font-size: 0.78rem; color: var(--color-danger);">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña *:</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="6" autocomplete="new-password">
                        <button type="button" class="btn-toggle-password" aria-label="Mostrar u ocultar confirmación de contraseña" title="Mostrar u ocultar confirmación de contraseña">
                            <svg class="eye-icon eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-icon eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cláusula de Consentimiento Ley N.º 29733 -->
            <div style="margin-top: 14px; margin-bottom: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.8rem; color: var(--color-charcoal-light); cursor: pointer; line-height: 1.4;">
                    <input type="checkbox" name="consentimiento_datos" id="consentimiento_datos" required style="margin-top: 3px; width: 16px; height: 16px;">
                    <span>
                        Declaro haber leído y acepto la política de privacidad y tratamiento de datos personales conforme a la <strong>Ley N.º 29733 de la República del Perú</strong>.
                    </span>
                </label>
                @error('consentimiento_datos')
                    <span style="font-size: 0.78rem; color: var(--color-danger); display: block; margin-top: 4px;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-accent btn-block" style="padding: 13px;">
                Registrar Cuenta Segura
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-cream-border); font-size: 0.88rem;">
            ¿Ya tienes una cuenta? 
            <a href="{{ route('login') }}" style="color: var(--color-earth-primary); font-weight: 600;">
                Inicia sesión aquí
            </a>
        </div>
    </div>
</div>
@endsection
