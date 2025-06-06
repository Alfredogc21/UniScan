@extends('layouts.estudiante', ['hideSearch' => true])

@section('title', 'Perfil - UniScan')

@section('page-title', 'Mi Perfil')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/estudiante/profile.css') }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@endsection

@section('content')
<div class="profile-container">
    @if (session('success'))
    <div class="alert alert-success mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="profile-title">
            <h1>{{ $user->name }}</h1>
            <p>Estudiante</p>
        </div>
    </div>

    <form action="{{ route('estudiante.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h2>Información Personal</h2>
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-section">
            <h2>Cambiar Contraseña</h2>
            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn-purple">Guardar Cambios</button>
        </div>
    </form>
</div>
</div>
@endsection