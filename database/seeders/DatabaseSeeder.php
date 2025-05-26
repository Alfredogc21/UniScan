<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role; // ✅ ¡Esta línea es clave!
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        Role::create(['id' => 1, 'name' => 'Admin']);
        Role::create(['id' => 2, 'name' => 'Profesor']);
        Role::create(['id' => 3, 'name' => 'Estudiante']);

        

    // Crear usuario de prueba con rol
    User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role_id' => 2, // Por ejemplo, profesor
    ]);
}
}