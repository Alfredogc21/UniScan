<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'UniScan - Panel de Estudiante')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('img/uniscan_logo.png') }}" type="image/png" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/estudiante/dashboard.css') }}">

    <!-- Estilos adicionales -->
    @yield('styles')
</head>

<body>
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
                        <a href="{{ route('estudiante.dashboard') }}" class="nav__link {{ request()->routeIs('estudiante.dashboard') ? 'nav__link--active' : '' }}">
                            <span class="nav__link-icon"><i class="fas fa-home"></i></span>
                            <span class="nav__link-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="{{ route('estudiante.materias') }}" class="nav__link {{ request()->routeIs('estudiante.materias') ? 'nav__link--active' : '' }}">
                            <span class="nav__link-icon"><i class="fas fa-book"></i></span>
                            <span class="nav__link-text">Mis Materias</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="{{ route('estudiante.asistencias') }}" class="nav__link {{ request()->routeIs('estudiante.asistencias') ? 'nav__link--active' : '' }}">
                            <span class="nav__link-icon"><i class="fas fa-clipboard-check"></i></span>
                            <span class="nav__link-text">Mis Asistencias</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="{{ route('estudiante.asistencia-qr') }}" class="nav__link {{ request()->routeIs('estudiante.asistencia-qr') ? 'nav__link--active' : '' }}">
                            <span class="nav__link-icon"><i class="fas fa-qrcode"></i></span>
                            <span class="nav__link-text">Asistencia QR</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar__footer">
                <a href="{{ route('estudiante.profile') }}" class="user-info" style="color: white;">
                    <div class="user-info__avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info__details">
                        <div class="user-info__name" style="color: white;">{{ Auth::user()->name }}</div>
                        <div class="user-info__role" style="color: rgba(255, 255, 255, 0.8);">Estudiante</div>
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
                <h1 class="header__title">@yield('page-title', 'Panel Estudiante')</h1>

                @if(!isset($hideSearch) || !$hideSearch)
                <div class="header__search">
                    <span class="search__icon"><i class="fas fa-search"></i></span>
                    <input type="text" class="search__input" placeholder="Buscar...">
                </div>
                @endif

                <div class="header__actions">
                    @include('partials.logout_button')
                </div>
            </header>

            <div class="content__main">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts adicionales -->
    @yield('scripts')
</body>

</html>