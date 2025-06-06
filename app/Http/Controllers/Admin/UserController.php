<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{    /**
     * Constructor para asegurar que solo los administradores pueden acceder
     * 
     * Nota: No podemos usar middleware directamente en el constructor
     * La restricción de acceso se debe aplicar en las rutas
     */
    public function __construct()
    {
        // No necesitamos aplicar middleware aquí, lo haremos en routes/web.php
    }

    /**
     * Mostrar la lista de usuarios
     */
    public function index()
    {
        $users = User::with('role')->orderBy('id', 'asc')->get();
        return view('admin.users', compact('users'));
    }

    /**
     * Mostrar formulario para agregar nuevo usuario
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Guardar un nuevo usuario
     */
    public function store(Request $request)
    {        // Validación del formulario
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'estado_id' => 'required|in:0,1',
        ]);

        // Crear el usuario
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'estado_id' => $request->estado_id,
        ]);

        return redirect()->route('admin.users')->with('success', 'Usuario creado correctamente.');
    }    /**
     * Obtener los datos de un usuario para editar
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    }

    /**
     * Actualizar un usuario existente
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);        // Validación del formulario
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'role_id' => 'required|exists:roles,id',
            'estado_id' => 'required|in:0,1',
            'password' => 'nullable|string|min:8',
        ]);

        // Actualizar datos básicos
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role_id = $request->role_id;
        $user->estado_id = $request->estado_id;

        // Actualizar contraseña solo si se proporciona una nueva
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }        try {
            $user->save();
            return response()->json(['success' => true, 'message' => 'Usuario actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
        }
    }    /**
     * Eliminar un usuario
     */    public function destroy($id)
    {
        // No permitir eliminar al usuario propio
        if (Auth::check() && $id == Auth::user()->id) {
            return redirect()->route('admin.users')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Usuario eliminado correctamente.');
    }
}