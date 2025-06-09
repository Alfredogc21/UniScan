@extends('layouts.estudiante')

@section('title', 'Mis Materias - UniScan')

@section('page-title', 'Mis Materias')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/estudiante/materias.css') }}">
@endsection

@section('content')
<!-- Contenido de materias -->
<div class="content-section">
    <div class="section__header">
        <h2 class="section__title">Materias Matriculadas</h2>
        <div class="section__actions">
            <span class="badge">{{ count($materias) }} materias</span>
        </div>
    </div>

    <div class="section__content">
        @if(count($materias) > 0)
        <div class="materias-grid">
            @foreach($materias as $materia)
            <div class="materia-card">
                <div class="materia-card__header">
                    <h3 class="materia-card__title">{{ $materia->nombre }}</h3>
                    <div class="materia-card__percentage {{ $materia->porcentaje_asistencia >= 80 ? 'percentage--good' : ($materia->porcentaje_asistencia >= 60 ? 'percentage--warning' : 'percentage--danger') }}">
                        {{ $materia->porcentaje_asistencia }}%
                    </div>
                </div>

                <div class="materia-card__info">
                    <div class="info-item">
                        <i class="fas fa-user-tie"></i>
                        <span>{{ $materia->profesor->name ?? 'Sin asignar' }}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-door-open"></i>
                        <span>{{ $materia->aula->nombre ?? 'Sin aula' }}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span>{{ $materia->curso->nombre ?? 'Sin curso' }}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>{{ $materia->horario_ingreso }} - {{ $materia->horario_salida }}</span>
                    </div>
                </div>

                <div class="materia-card__stats">
                    <div class="stat-item">
                        <div class="stat-value">{{ $materia->total_asistencias }}</div>
                        <div class="stat-label">Asistencias</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $materia->porcentaje_asistencia }}%</div>
                        <div class="stat-label">Asistencia</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state__icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="empty-state__title">No hay materias matriculadas</h3>
            <p class="empty-state__description">
                Aún no tienes materias asignadas. Contacta con tu profesor o administrador.
            </p>
        </div>
        @endif
    </div>
</div>

<!-- Contenedor para notificaciones -->
<div class="notifications-container"></div>
@endsection

@section('scripts')
<script src="{{ asset('js/estudiante/dashboard.js') }}"></script>
@endsection