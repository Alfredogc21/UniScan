@extends('layouts.admin')

@section('title', 'Dashboard - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
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
                    <a href="{{ route('admin.dashboard') }}" class="nav__link nav__link--active">
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
                <li class="nav__item">
                    <a href="{{ route('admin.menu') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-question-circle"></i></span>
                        <span class="nav__link-text">Ayuda</span>
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

            <h1 class="header__title">Dashboard</h1>

            <div class="header__search">
                <span class="search__icon"><i class="fas fa-search"></i></span>
                <input type="text" class="search__input" placeholder="Buscar...">
            </div>

            <div class="header__actions">
                @include('partials.logout_button')
            </div>
        </header>

        <div class="content__main">
            <!-- Tarjetas de resumen -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-card__header">
                        <h3 class="summary-card__title">Usuarios Totales</h3>
                        <div class="summary-card__icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="summary-card__content">
                        <div class="summary-card__value">{{ number_format($totalUsuarios) }}</div>
                        <div class="summary-card__stats summary-card__stats--up">
                            <i class="fas fa-arrow-up"></i> Actualizado en tiempo real
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-card__header">
                        <h3 class="summary-card__title">Asistencias Hoy</h3>
                        <div class="summary-card__icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="summary-card__content">
                        <div class="summary-card__value">{{ number_format($asistenciasHoy) }}</div>
                        <div class="summary-card__stats summary-card__stats--up">
                            <i class="fas fa-arrow-up"></i> {{ Carbon\Carbon::now()->format('d/m/Y') }}
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-card__header">
                        <h3 class="summary-card__title">Materias Activas</h3>
                        <div class="summary-card__icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="summary-card__content">
                        <div class="summary-card__value">{{ number_format($materiasActivas) }}</div>
                        <div class="summary-card__stats">
                            <i class="fas fa-circle"></i> Total de materias
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-card__header">
                        <h3 class="summary-card__title">% Asistencia</h3>
                        <div class="summary-card__icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="summary-card__content">
                        <div class="summary-card__value">{{ $porcentajeAsistencia }}%</div>
                        <div class="summary-card__stats {{ $porcentajeAsistencia >= 80 ? 'summary-card__stats--up' : 'summary-card__stats--down' }}">
                            <i class="fas fa-{{ $porcentajeAsistencia >= 80 ? 'arrow-up' : 'arrow-down' }}"></i> Promedio general
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="charts">
                <div class="content-section chart-container">                <div class="section__header">
                    <h2 class="section__title">Actividad Semanal</h2>
                </div>
                    <div class="section__content">
                        <canvas id="weeklyActivityChart" data-chart-data="{{ json_encode($datosGraficoSemanal) }}"></canvas>
                    </div>
                </div>

                <div class="content-section chart-container">
                    <div class="section__header">
                        <h2 class="section__title">Distribución de Asistencias</h2>
                    </div>
                    <div class="section__content">
                        <canvas id="attendanceDistChart" data-chart-data="{{ json_encode($datosGraficoDistribucion) }}"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabla de datos recientes -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Asistencias Recientes</h2>
                </div>
                <div class="section__content">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead class="data-table__head">
                                <tr>
                                <th class="data-table__header">Usuario</th>
                                <th class="data-table__header">Materia</th>
                                <th class="data-table__header">Fecha</th>
                                <th class="data-table__header">Hora</th>
                                <th class="data-table__header">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="data-table__body">
                            @forelse($asistenciasRecientes as $asistencia)
                            <tr>
                                <td class="data-table__cell">{{ $asistencia->alumno->name }}</td>
                                <td class="data-table__cell">{{ $asistencia->materia->nombre }}</td>
                                <td class="data-table__cell">{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y') }}</td>
                                <td class="data-table__cell">{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('H:i') }}</td>
                                <td class="data-table__cell">
                                    @php
                                    $estadoClass = [
                                        1 => 'data-table__status--active',   // Presente
                                        2 => 'data-table__status--inactive', // Ausente
                                        3 => 'data-table__status--pending'   // Justificado
                                    ];
                                    $class = $estadoClass[$asistencia->tipo_asistencia_id] ?? '';
                                    @endphp
                                    <span class="data-table__status {{ $class }}">
                                        {{ $asistencia->tipoAsistencia->descripcion }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="data-table__cell text-center">No hay asistencias recientes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            <!-- Contenedor para notificaciones -->
            <div class="notifications-container"></div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endsection