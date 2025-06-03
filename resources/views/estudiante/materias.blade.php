@extends('layouts.estudiante')

@section('title', 'Mis Materias - UniScan')

@section('page-title', 'Mis Materias')

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
                </div>        </div>

            <!-- Contenedor para notificaciones -->
            <div class="notifications-container"></div>
@endsection

@section('scripts')
<script src="{{ asset('js/estudiante/dashboard.js') }}"></script>
<style>
.materias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.materia-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid #e1e5e9;
    transition: all 0.3s ease;
}

.materia-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.materia-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e1e5e9;
}

.materia-card__title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.materia-card__percentage {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
}

.percentage--good {
    background: #d4edda;
    color: #155724;
}

.percentage--warning {
    background: #fff3cd;
    color: #856404;
}

.percentage--danger {
    background: #f8d7da;
    color: #721c24;
}

.materia-card__info {
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.info-item i {
    width: 20px;
    color: #6c757d;
    margin-right: 0.75rem;
}

.info-item span {
    color: #495057;
    font-size: 0.875rem;
}

.materia-card__stats {
    display: flex;
    justify-content: space-around;
    padding-top: 1rem;
    border-top: 1px solid #e1e5e9;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state__icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state__title {
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.empty-state__description {
    color: #868e96;
    max-width: 400px;
    margin: 0 auto;
}

.badge {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}
</style>
@endsection
