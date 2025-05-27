<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si el usuario está autenticado y tiene role_id = 1 (Administrador)
        if (!Auth::check() || Auth::user()->role_id != 1) {
            return redirect('/')->with('error', 'Acceso denegado. No tienes permisos de administrador.');
        }

        return $next($request);
    }
}
