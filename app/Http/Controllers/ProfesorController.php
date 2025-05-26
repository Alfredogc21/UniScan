<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfesorController extends Controller
{
    public function dashboard(Request $request)
{
    $user = auth()->user();

    if (!$user || $user->role_id != 2) {
        abort(403, 'Acceso no autorizado');
    }

    return view('profesor.dashboard', ['user' => $user]);
}


}
