@extends('layouts.estudiante')

@section('title', 'Mis Asistencias - UniScan')

@section('page-title', 'Mis Asistencias')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
            <!-- Tarjetas de resumen -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-card__header">
                        <h3 class="summary-card__title">Total Asistencias</h3>
                        <div class="summary-card__icon">
                            <i class="fas fa-list-check"></i>
                        </div>
                    </div>
                    <div class="summary-card__content">
                        <div class="summary-card__value">{{ $totalAsistencias }}</div>
                        <div class="summary-card__stats">
                            <i class="fas fa-history"></i> Historial completo
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-card__header">
                        <h3 class="summary-card__title">Presentes</h3>
                        <div class="summary-card__icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="summary-card__content">
                        <div class="summary-card__value">{{ $totalPresentes }}</div>
                        <div class="summary-card__stats summary-card__stats--up">
                            <i class="fas fa-arrow-up"></i> Asistencias confirmadas
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-card__header">
                        <h3 class="summary-card__title">Ausentes</h3>
                        <div class="summary-card__icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="summary-card__content">
                        <div class="summary-card__value">{{ $totalAusentes }}</div>
                        <div class="summary-card__stats summary-card__stats--down">
                            <i class="fas fa-exclamation-triangle"></i> Faltas registradas
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
                            <i class="fas fa-{{ $porcentajeAsistencia >= 80 ? 'arrow-up' : 'arrow-down' }}"></i> Mi promedio general
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Filtros de Búsqueda</h2>
                    <button id="resetFiltersBtn" class="section__action">
                        <i class="fas fa-undo"></i> Limpiar Filtros
                    </button>
                </div>
                <div class="section__content">
                    <form id="filterForm" class="filters-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="materiaFilter">Materia:</label>
                                <select id="materiaFilter" name="materia_id" class="form-control">
                                    <option value="">Todas las materias</option>
                                    @foreach($materias as $materia)
                                        <option value="{{ $materia->id }}" {{ request('materia_id') == $materia->id ? 'selected' : '' }}>
                                            {{ $materia->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tipoAsistenciaFilter">Estado:</label>
                                <select id="tipoAsistenciaFilter" name="tipo_asistencia_id" class="form-control">
                                    <option value="">Todos los estados</option>
                                    @foreach($tiposAsistencia as $tipo)
                                        <option value="{{ $tipo->id }}" {{ request('tipo_asistencia_id') == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="fechaDesde">Fecha desde:</label>
                                <input type="date" id="fechaDesde" name="fecha_desde" class="form-control date-picker" 
                                       value="{{ request('fecha_desde') }}">
                            </div>

                            <div class="form-group">
                                <label for="fechaHasta">Fecha hasta:</label>
                                <input type="date" id="fechaHasta" name="fecha_hasta" class="form-control date-picker" 
                                       value="{{ request('fecha_hasta') }}">
                            </div>
                        </div>

                        <div class="filters-actions">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-filter"></i> Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de asistencias -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Historial de Asistencias</h2>
                    <div class="section__actions">
                        <span class="badge">{{ $asistencias->total() }} registros</span>
                    </div>
                </div>
                <div class="section__content">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead class="data-table__head">
                                <tr>
                                    <th class="data-table__header">Fecha</th>
                                    <th class="data-table__header">Hora</th>
                                    <th class="data-table__header">Materia</th>
                                    <th class="data-table__header">Profesor</th>
                                    <th class="data-table__header">Estado</th>
                                    <th class="data-table__header">Justificación</th>
                                    <th class="data-table__header">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="data-table__body">
                                @forelse($asistencias as $asistencia)
                                <tr>
                                    <td class="data-table__cell">
                                        {{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y') }}
                                    </td>
                                    <td class="data-table__cell">
                                        {{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('H:i') }}
                                    </td>
                                    <td class="data-table__cell">{{ $asistencia->materia->nombre }}</td>
                                    <td class="data-table__cell">{{ $asistencia->materia->profesor->name ?? 'N/A' }}</td>
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
                                        {{ $asistencia->justificacion ?? 'Sin justificación' }}
                                    </td>
                                    <td class="data-table__cell">
                                        <div class="data-table__actions">
                                            <button class="data-table__action btn-ver-detalle" 
                                                    title="Ver detalles" 
                                                    data-id="{{ $asistencia->id }}"
                                                    data-materia="{{ $asistencia->materia->nombre }}"
                                                    data-profesor="{{ $asistencia->materia->profesor->name ?? 'N/A' }}"
                                                    data-fecha="{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y H:i') }}"
                                                    data-estado="{{ $asistencia->tipoAsistencia->descripcion }}"
                                                    data-justificacion="{{ $asistencia->justificacion ?? 'Sin justificación' }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="data-table__cell text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-times" style="font-size: 2rem; color: #dee2e6; margin-bottom: 1rem;"></i>
                                            <p>No se encontraron registros de asistencia</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($asistencias->hasPages())
                    <div class="pagination-container">
                        {{ $asistencias->links() }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Contenedor para notificaciones -->
            <div class="notifications-container"></div>

<!-- Modal para ver detalles de asistencia -->
<div class="modal-overlay" id="detalleAsistenciaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalles de Asistencia</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="attendance-details-card">
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-book"></i> Materia:</span>
                    <span class="detail-value" id="detailMateria"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-chalkboard-teacher"></i> Profesor:</span>
                    <span class="detail-value" id="detailProfesor"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-calendar-day"></i> Fecha y Hora:</span>
                    <span class="detail-value" id="detailFecha"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-info-circle"></i> Estado:</span>
                    <span class="detail-value" id="detailEstado"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-comment"></i> Justificación:</span>
                    <span class="detail-value" id="detailJustificacion"></span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Cerrar</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="{{ asset('js/estudiante/dashboard.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar selectores de fecha
    flatpickr(".date-picker", {
        dateFormat: "Y-m-d",
        locale: "es",
        allowInput: true
    });

    // Búsqueda en tiempo real
    const searchInput = document.getElementById('asistenciaSearchInput');
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            tableRows.forEach(row => {
                const materia = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                const profesor = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
                const estado = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';
                
                if (materia.includes(searchTerm) || profesor.includes(searchTerm) || estado.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Limpiar filtros
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const filterForm = document.getElementById('filterForm');
    
    if (resetFiltersBtn && filterForm) {
        resetFiltersBtn.addEventListener('click', function() {
            const selects = filterForm.querySelectorAll('select');
            const inputs = filterForm.querySelectorAll('input');
            
            selects.forEach(select => select.selectedIndex = 0);
            inputs.forEach(input => {
                if (input.type === 'text' || input.type === 'date') {
                    input.value = '';
                }
            });
            
            // Reenviar formulario para limpiar filtros en el servidor
            filterForm.submit();
        });
    }

    // Modal de detalles de asistencia
    const verDetalleButtons = document.querySelectorAll('.btn-ver-detalle');
    const detalleModal = document.getElementById('detalleAsistenciaModal');
    const modalCloseButtons = document.querySelectorAll('.modal-close');

    verDetalleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const materia = this.getAttribute('data-materia');
            const profesor = this.getAttribute('data-profesor');
            const fecha = this.getAttribute('data-fecha');
            const estado = this.getAttribute('data-estado');
            const justificacion = this.getAttribute('data-justificacion');

            // Llenar los campos del modal
            document.getElementById('detailMateria').textContent = materia;
            document.getElementById('detailProfesor').textContent = profesor;
            document.getElementById('detailFecha').textContent = fecha;
            document.getElementById('detailEstado').textContent = estado;
            document.getElementById('detailJustificacion').textContent = justificacion;

            // Mostrar modal
            detalleModal.style.display = 'flex';
        });
    });

    // Cerrar modal
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            detalleModal.style.display = 'none';
        });
    });

    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target === detalleModal) {
            detalleModal.style.display = 'none';
        }
    });
});
</script>

<style>
.filters-form {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-control {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.filters-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn-filter {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-filter:hover {
    background: #0056b3;
}

.table-responsive {
    overflow-x: auto;
}

.pagination-container {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #495057;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
}

.modal-close:hover {
    color: #495057;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
}

.attendance-details-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
}

.detail-row {
    display: flex;
    margin-bottom: 1rem;
    align-items: center;
}

.detail-row:last-child {
    margin-bottom: 0;
}

.detail-label {
    font-weight: 500;
    color: #495057;
    min-width: 150px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-value {
    color: #212529;
    flex: 1;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #6c757d;
}

.badge {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .filters-actions {
        justify-content: center;
    }
    
    .modal-content {
        width: 95%;
        margin: 1rem;
    }
    
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .detail-label {
        min-width: auto;
    }
}
</style>
@endsection
