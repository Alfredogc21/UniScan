<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RolMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $usuario = Auth::user();

        // Verifica si el rol del usuario está en la lista permitida
        if (!in_array($usuario->rol, $roles)) {
            abort(403, 'Acceso no autorizado');
        }

        return $next($request);
    }
}
