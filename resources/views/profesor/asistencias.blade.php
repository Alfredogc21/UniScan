@extends('layouts.profesor')

@section('title', 'Asistencias - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/profesor/dashboard.css') }}">
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
                    <a href="{{ route('profesor.dashboard') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-home"></i></span>
                        <span class="nav__link-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('profesor.alumnos') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-users"></i></span>
                        <span class="nav__link-text">Alumnos</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('profesor.materias') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-book"></i></span>
                        <span class="nav__link-text">Mis Materias</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('profesor.asistencias') }}" class="nav__link nav__link--active">
                        <span class="nav__link-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="nav__link-text">Asistencias</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar__footer">
            <a href="{{ route('profesor.profile') }}" class="user-info" style="color: white;">
                <div class="user-info__avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info__details">
                    <div class="user-info__name" style="color: white;">{{ Auth::user()->name }}</div>
                    <div class="user-info__role" style="color: rgba(255, 255, 255, 0.8);">Profesor</div>
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

            <h1 class="header__title">Asistencias</h1>

            <div class="header__search">
                <span class="search__icon"><i class="fas fa-search"></i></span>
                <input type="text" class="search__input" placeholder="Buscar asistencia...">
            </div>

            <div class="header__actions">
                @include('partials.logout_button')
            </div>
        </header>

        <div class="content__main">
            <!-- Filtros de asistencia -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Filtros</h2>
                </div>
                <div class="section__content">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="materia-filter">Materia:</label>
                            <select id="materia-filter" class="form-select">
                                <option value="">Todas</option>
                                @foreach($materias ?? [] as $materia)
                                    <option value="{{ $materia->id }}">{{ $materia->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="estado-filter">Estado:</label>
                            <select id="estado-filter" class="form-select">
                                <option value="">Todos</option>
                                @foreach($tiposAsistencia ?? [] as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="fecha-desde">Desde:</label>
                            <input type="date" id="fecha-desde" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="fecha-hasta">Hasta:</label>
                            <input type="date" id="fecha-hasta" class="form-control">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary" id="aplicar-filtros">Aplicar Filtros</button>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de asistencias -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Registro de Asistencias</h2>
                </div>
                <div class="section__content">
                    <table class="data-table">
                        <thead class="data-table__head">
                            <tr>
                                <th class="data-table__header">ID</th>
                                <th class="data-table__header">Alumno</th>
                                <th class="data-table__header">Materia</th>
                                <th class="data-table__header">Fecha</th>
                                <th class="data-table__header">Hora</th>
                                <th class="data-table__header">Estado</th>
                                <th class="data-table__header">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="data-table__body">
                            @forelse($asistencias ?? [] as $asistencia)
                            <tr>
                                <td class="data-table__cell">{{ $asistencia->id }}</td>
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
                                <td class="data-table__cell">
                                    <div class="data-table__actions">
                                        <button class="data-table__action" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="data-table__action" title="Justificar">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="data-table__cell text-center">No hay asistencias registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    @if(isset($asistencias) && method_exists($asistencias, 'links'))
                        <div class="mt-4">
                            {{ $asistencias->links() }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Contenedor para notificaciones -->
            <div class="notifications-container"></div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/profesor/dashboard.js') }}"></script>
@endsection
