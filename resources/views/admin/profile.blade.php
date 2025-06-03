@extends('layouts.admin')

@section('title', 'Perfil - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<style>
    .profile-container {
        max-width: 800px;
        margin: 0 auto;
        background-color: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e1ff;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: #7f6fcb;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-right: 1.5rem;
    }

    .profile-title h1 {
        margin: 0;
        color: #4a3c85;
        font-size: 1.8rem;
    }

    .profile-title p {
        margin: 0.5rem 0 0;
        color: #8a7eb1;
        font-size: 1rem;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section h2 {
        color: #4a3c85;
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e1ff;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #574b9b;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1.5px solid #c7b9f7;
        font-size: 1rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #7f6fcb;
        box-shadow: 0 0 0 3px rgba(127, 111, 203, 0.25);
    }

    .btn-purple {
        background: linear-gradient(90deg, #7f6fcb, #ab94ff);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }

    .btn-purple:hover {
        opacity: 0.9;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background-color: #d1fae5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .invalid-feedback {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc2626;
    }
</style>
@endsection

@section('content')
<div class="dashboard">
    <!-- Sidebar -->
    <aside class="dashboard__sidebar">
        <div class="sidebar__header">
            <div class="sidebar__logo">
                <img src="{{ asset('img/uniscan_logo.png') }}" alt="UniScan Logo" class="sidebar__logo-img">
                <span class="sidebar__logo-text">UniScan</span>
            </div>
            <button class="sidebar__toggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <nav class="sidebar__nav">
            <ul class="nav__list">
                <li class="nav__item">
                    <a href="{{ route('admin.dashboard') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-home"></i></span>
                        <span class="nav__link-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.users') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-users"></i></span>
                        <span class="nav__link-text">Usuarios</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.materias') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-book"></i></span>
                        <span class="nav__link-text">Materias</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.asistencias') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="nav__link-text">Asistencias</span>
                    </a>
                </li>

            </ul>
        </nav>

        <div class="sidebar__footer">
            <a href="{{ route('admin.profile') }}" class="user-info" style="color: white;">
                <div class="user-info__avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info__details">
                    <div class="user-info__name" style="color: white;">{{ Auth::user()->name }}</div>
                    <div class="user-info__role" style="color: rgba(255, 255, 255, 0.8);">Administrador</div>
                </div>
            </a>
        </div>
    </aside>

    <!-- Contenido principal -->
    <main class="dashboard__content">
        <header class="content__header">
            <button class="actions__button mobile-menu-btn d-md-none">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="header__title">Mi Perfil</h1>

            <div class="header__search">
                <span class="search__icon"><i class="fas fa-search"></i></span>
                <input type="text" class="search__input" placeholder="Buscar...">
            </div>

            <div class="header__actions">
                @include('partials.logout_button')
            </div>
        </header>

        <div class="content__main">
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
                        <p>Administrador</p>
                    </div>
                </div>

                <form action="{{ route('admin.profile.update') }}" method="POST">
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
    </main>
</div>
@endsection