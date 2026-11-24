<?php

namespace App\Http\Controllers;

use App\Models\Artesano;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'correo' => 'required|email',
            'password' => 'required|string',
        ]);

        // Intentar autenticación por 'correo' o 'email'
        $user = User::where('correo', $credentials['correo'])
            ->orWhere('email', $credentials['correo'])
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($user->isAdminHub()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', "Bienvenido al Panel de Control del HUB Logístico, {$user->nombre}.");
            }

            if ($user->isArtesano()) {
                return redirect()->intended(route('artesano.dashboard'))
                    ->with('success', "Bienvenido a tu taller digital, {$user->nombre}.");
            }

            return redirect()->intended(route('catalogo.index'))
                ->with('success', "¡Bienvenido de vuelta, {$user->nombre}!");
        }

        return back()->withErrors([
            'correo' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
        ])->onlyInput('correo');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'correo' => 'required|email|max:150|unique:users,correo|unique:users,email',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:250',
            'rol' => 'required|in:cliente,artesano',
            'password' => 'required|string|min:6|confirmed',
            'consentimiento_datos' => 'accepted', // Cumplimiento estricto con Ley N.º 29733
        ], [
            'consentimiento_datos.accepted' => 'Debe autorizar el tratamiento de sus datos personales conforme a la Ley N.º 29733 de Protección de Datos Personales de la República del Perú.',
            'correo.unique' => 'El correo electrónico ya se encuentra registrado.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $user = User::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'name' => "{$validated['nombre']} {$validated['apellido']}",
            'correo' => $validated['correo'],
            'email' => $validated['correo'],
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'rol' => $validated['rol'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($user->rol === 'artesano') {
            Artesano::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'direccion_arte' => $user->direccion ?: 'Barrio de San Blas, Cusco',
                    'telefono' => $user->telefono ?: '+51 984 000 000',
                    'historia_taller' => 'Taller artesanal cusqueño dedicado a la preservación del arte tradicional andino.',
                    'foto_perfil' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=400&q=80',
                    'estado_validacion' => true,
                ]
            );
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->rol === 'artesano') {
            return redirect()->route('artesano.dashboard')
                ->with('success', '¡Bienvenido a tu Taller Artesanal! Tu cuenta ha sido activada.');
        }

        return redirect()->route('catalogo.index')
            ->with('success', '¡Registro exitoso! Bienvenido al Portal del Artesano.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Has cerrado tu sesión de forma segura.');
    }
}
