@extends('layouts.app')

@section('title', 'Iniciar Sesión | Portal del Artesano Cusco')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 480px;">
    <div style="background-color: var(--color-white); border: 1px solid var(--color-cream-border); border-radius: var(--radius-md); padding: 32px; box-shadow: var(--shadow-sm);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 2.5rem; margin-bottom: 8px;" aria-hidden="true">❖</div>
            <h1 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-charcoal-dark);">
                Acceso al Portal
            </h1>
            <p style="font-size: 0.88rem; color: var(--color-charcoal-muted); margin-top: 4px;">
                Ingresa con tu correo registrado para continuar.
            </p>
        </div>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="correo" class="form-label">Correo Electrónico:</label>
                <input type="email" 
                       name="correo" 
                       id="correo" 
                       class="form-control" 
                       value="{{ old('correo') }}" 
                       required 
                       autofocus 
                       placeholder="ejemplo@portalartesano.pe">
                @error('correo')
                    <span style="font-size: 0.8rem; color: var(--color-danger); display: block; margin-top: 4px;">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña:</label>
                <div class="password-input-wrapper">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control" 
                           required 
                           placeholder="••••••••"
                           autocomplete="current-password">
                    <button type="button" 
                            class="btn-toggle-password" 
                            id="toggle-password" 
                            aria-label="Mostrar u ocultar contraseña" 
                            title="Mostrar u ocultar contraseña">
                        <!-- Icono Ojito Abierto (por defecto visible) -->
                        <svg class="eye-icon eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <!-- Icono Ojito Tachado / Oculto -->
                        <svg class="eye-icon eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <span style="font-size: 0.8rem; color: var(--color-danger); display: block; margin-top: 4px;">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 0.85rem;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    <input type="checkbox" name="remember" value="1">
                    Recordarme en este dispositivo
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 12px;">
                Entrar al Sistema
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-cream-border); font-size: 0.88rem;">
            ¿Aún no tienes cuenta? 
            <a href="{{ route('register') }}" style="color: var(--color-terracotta); font-weight: 600;">
                Regístrate aquí
            </a>
        </div>

        <div style="margin-top: 16px; background-color: var(--color-cream-surface); padding: 12px; border-radius: var(--radius-sm); font-size: 0.78rem; color: var(--color-charcoal-muted); line-height: 1.4;">
            <strong>Cuentas de prueba sembradas:</strong><br>
            • Admin HUB: <code>admin@portalartesano.pe</code> / <code>secret123</code><br>
            • Artesano San Blas: <code>marcopolo@portalartesano.pe</code> / <code>secret123</code><br>
            • Cliente Turista: <code>turista@gmail.com</code> / <code>secret123</code>
        </div>
    </div>
</div>
@endsection
