<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\Admin\AdminMateriaController;



// Página de bienvenida
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registro
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// Recuperación de contraseña
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Ruta de diagnóstico para materias
Route::get('/debug-materia/{id}', function($id) {
    try {
        $materia = App\Models\Materia::with(['profesor', 'aula', 'curso'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $materia
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        
        // Rutas de perfil
        Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        
        // Rutas de gestión de usuarios usando el controlador
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users');
        Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        
        // Rutas de gestión de materias
        Route::get('/materias', [App\Http\Controllers\Admin\AdminMateriaController::class, 'index'])->name('materias');
        Route::post('/materias', [App\Http\Controllers\Admin\AdminMateriaController::class, 'store'])->name('materias.store');
        Route::get('/materias/{id}/edit', [App\Http\Controllers\Admin\AdminMateriaController::class, 'edit'])->name('materias.edit');
        Route::put('/materias/{id}', [App\Http\Controllers\Admin\AdminMateriaController::class, 'update'])->name('materias.update');
        Route::delete('/materias/{id}', [App\Http\Controllers\Admin\AdminMateriaController::class, 'destroy'])->name('materias.destroy');
        Route::post('/materias/{id}/generate-qr', [App\Http\Controllers\Admin\AdminMateriaController::class, 'generateQr'])->name('materias.generateQr');
        
        Route::get('/subjects', function () {
            return view('admin.subjects');
        })->name('subjects');
        
        Route::get('/attendance', function () {
            return view('admin.attendance');
        })->name('attendance');
        
        Route::get('/reports', function () {
            return view('admin.reports');
        })->name('reports');
        
        Route::get('/settings', function () {
            return view('admin.settings');
        })->name('settings');
    });

    Route::get('/profesor/dashboard', [ProfesorController::class, 'dashboard'])->name('profesor.dashboard');

    Route::get('/estudiante/dashboard', function () {
        return view('estudiante.dashboard');
    })->name('estudiante.dashboard');

    // Guardar materia y generar QR (profesor)
    Route::post('/materias', [MateriaController::class, 'store']);

    // Registrar asistencia desde QR (alumno)
    Route::post('/registrar-asistencia', [MateriaController::class, 'registrarAsistencia']);
    Route::resource('asistencias', AsistenciaController::class);
});
