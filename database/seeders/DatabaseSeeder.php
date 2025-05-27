<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        Role::create(['id' => 1, 'name' => 'Admin']);
        Role::create(['id' => 2, 'name' => 'Profesor']);
        Role::create(['id' => 3, 'name' => 'Estudiante']);

        

    // Crear usuario administrador por defecto
    User::factory()->create([
        'name' => 'Administrador',
        'email' => 'admin@uniscan.com',
        'password' => Hash::make('admin12345'),
        'role_id' => 1, // Administrador
        'estado_id' => 1, // Activo
    ]);
    
    // Crear usuario de prueba con rol profesor
    User::factory()->create([
        'name' => 'Profesor Prueba',
        'email' => 'profesor@uniscan.com',
        'password' => Hash::make('profesor12345'),
        'role_id' => 2, // Profesor
        'estado_id' => 1, // Activo
    ]);
}
}