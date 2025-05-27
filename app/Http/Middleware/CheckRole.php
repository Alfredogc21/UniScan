<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $role  El ID del rol requerido
     * @return mixed
     */    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }
        
        if (Auth::user()->role_id != $role) {
            return redirect('/')->with('error', 'No tienes permisos para acceder a esta sección. Se requiere rol ID: ' . $role . ', tu rol actual es: ' . Auth::user()->role_id);
        }

        return $next($request);
    }
}
