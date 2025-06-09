@extends('layouts.estudiante')

@section('title', 'Dashboard - UniScan')

@section('page-title', 'Dashboard Estudiante')

@section('content')
<!-- Tarjetas de resumen -->
<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-card__header">
            <h3 class="summary-card__title">Materias Matriculadas</h3>
            <div class="summary-card__icon">
                <i class="fas fa-book"></i>
            </div>
        </div>
        <div class="summary-card__content">
            <div class="summary-card__value">{{ number_format($materiasMatriculadas) }}</div>
            <div class="summary-card__stats">
                <i class="fas fa-graduation-cap"></i> Materias activas
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
                <i class="fas fa-calendar-day"></i> {{ Carbon\Carbon::now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-card__header">
            <h3 class="summary-card__title">Total Asistencias</h3>
            <div class="summary-card__icon">
                <i class="fas fa-list-check"></i>
            </div>
        </div>
        <div class="summary-card__content">
            <div class="summary-card__value">{{ number_format($totalAsistencias) }}</div>
            <div class="summary-card__stats">
                <i class="fas fa-history"></i> Historial completo
            </div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-card__header">
            <h3 class="summary-card__title">% Mi Asistencia</h3>
            <div class="summary-card__icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        <div class="summary-card__content">
            <div class="summary-card__value">{{ $porcentajeAsistencia }}%</div>
            <div class="summary-card__stats {{ $porcentajeAsistencia >= 80 ? 'summary-card__stats--up' : 'summary-card__stats--down' }}">
                <i class="fas fa-{{ $porcentajeAsistencia >= 80 ? 'arrow-up' : 'arrow-down' }}"></i> Mi promedio
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="charts">
    <div class="content-section chart-container">
        <div class="section__header">
            <h2 class="section__title">Actividad Semanal</h2>
        </div>
        <div class="section__content">
            <canvas id="weeklyActivityChart" data-chart-data="{{ json_encode($datosGraficoSemanal) }}"></canvas>
        </div>
    </div>
</div> <!-- Tabla de datos recientes -->
<div class="content-section">
    <div class="section__header">
        <h2 class="section__title">Mis Asistencias Recientes</h2>
    </div>    <div class="section__content">
        <div class="table-responsive">
            <table class="data-table">
                <thead class="data-table__head">
                    <tr>
                        <th class="data-table__header">Materia</th>
                        <th class="data-table__header">Profesor</th>
                        <th class="data-table__header">Fecha</th>
                        <th class="data-table__header">Hora</th>
                        <th class="data-table__header">Estado</th>
                    </tr>
                </thead>
                <tbody class="data-table__body">
                @forelse($asistenciasRecientes as $asistencia)
                <tr>
                    <td class="data-table__cell">{{ $asistencia->materia->nombre }}</td>
                    <td class="data-table__cell">{{ $asistencia->materia->profesor->name ?? 'N/A' }}</td>
                    <td class="data-table__cell">{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y') }}</td>
                    <td class="data-table__cell">{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('H:i') }}</td>
                    <td class="data-table__cell">
                        @php
                        $estadoClass = [
                        1 => 'data-table__status--active', // Presente
                        2 => 'data-table__status--inactive', // Ausente
                        3 => 'data-table__status--pending' // Justificado
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
                    <td colspan="5" class="data-table__cell text-center">No hay asistencias registradas aún</td>
                </tr>                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div> <!-- Contenedor para notificaciones -->
<div class="notifications-container"></div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/estudiante/dashboard.js') }}"></script>
@endsection