<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // tu vista login.blade.php
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        // Verificar si el usuario está activo
        $user = User::where('email', $request->email)->first();
        if ($user && $user->estado_id == 0) {
            return back()->withErrors([
                'email' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ]);
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirigir según rol
            $user = Auth::user();
            if ($user->role_id == 1) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role_id == 2) {
                return redirect()->route('profesor.dashboard');
            } else {
                return redirect()->route('estudiante.dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
