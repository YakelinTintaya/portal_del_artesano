<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('warning', 'Debe iniciar sesión para acceder a esta sección.');
        }

        $user = Auth::user();

        if (! in_array($user->rol, $roles, true)) {
            abort(403, 'Acceso denegado: no cuenta con los privilegios requeridos para este módulo.');
        }

        return $next($request);
    }
}
