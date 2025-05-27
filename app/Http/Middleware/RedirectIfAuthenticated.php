<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirigir según el rol
                $user = Auth::user();
                
                if ($user->role_id == 1) {  // Admin
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role_id == 2) { // Profesor
                    return redirect()->route('profesor.dashboard');
                } else { // Estudiante u otros
                    return redirect()->route('estudiante.dashboard');
                }
            }
        }

        return $next($request);
    }
}
