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
        <button type="button" class="close-alert" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <div class="profile-header">
        <div class="profile-avatar" title="Foto de perfil">
            <i class="fas fa-user"></i>
        </div>
        <div class="profile-title">
            <h1>{{ $user->name }}</h1>
            <p><i class="fas fa-graduation-cap"></i> Estudiante</p>
        </div>
    </div>

    <form action="{{ route('estudiante.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h2><i class="fas fa-user-edit"></i> Información Personal</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Nombre</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" autocomplete="name">
                    @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" autocomplete="email">
                    @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2><i class="fas fa-key"></i> Cambiar Contraseña</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Nueva Contraseña</label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                        <button type="button" class="toggle-password" tabindex="-1" title="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                    @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmar Nueva Contraseña</label>
                    <div class="password-input-container">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                        <button type="button" class="toggle-password" tabindex="-1" title="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="reset" class="btn-secondary">Cancelar</button>
            <button type="submit" class="btn-purple">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

<script>
    // Mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });
    
    // Auto-ocultar mensajes de alerta después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
</script>
@endsection