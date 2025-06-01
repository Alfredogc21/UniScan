@extends('layouts.admin')

@section('title', 'Gestión de Asistencias - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/asistenciasAdmin.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                <li class="nav__item"> <a href="{{ route('admin.asistencias') }}" class="nav__link nav__link--active">
                        <span class="nav__link-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="nav__link-text">Asistencias</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar__footer"> <a href="{{ route('admin.profile') }}" class="user-info">
                <div class="user-info__avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info__details">
                    <div class="user-info__name">{{ Auth::user()->name }}</div>
                    <div class="user-info__role">Administrador</div>
                </div>
            </a>
        </div>
    </aside>

    <!-- Contenido principal -->
    <main class="dashboard__content">
        <header class="content__header">
            <button class="actions__button mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="header__title">Gestión de Asistencias</h1>

            <div class="header__search">
                <span class="search__icon"><i class="fas fa-search"></i></span>
                <input type="text" id="asistenciaSearchInput" class="search__input" placeholder="Buscar estudiante...">
            </div>

            <div class="header__actions">
                @include('partials.logout_button')
            </div>
        </header>

        <div class="content__main">
            <!-- Mensajes de notificación -->
            @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
            @endif

            <!-- Filtros -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Filtros de Búsqueda</h2>
                </div>
                <div class="section__content" id="filtersContainer">
                    <form id="attendanceFilterForm" method="GET" action="{{ route('admin.asistencias') }}">
                        <div class="filters-container">
                            <div class="filter-group">
                                <label for="materia">Materia:</label>
                                <select name="materia" id="materia">
                                    <option value="">Todas las materias</option>
                                    @foreach($materias ?? [] as $materia)
                                    <option value="{{ $materia->id }}" {{ request('materia') == $materia->id ? 'selected' : '' }}>{{ $materia->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="profesor">Profesor:</label>
                                <select name="profesor" id="profesor">
                                    <option value="">Todos los profesores</option>
                                    @foreach($profesores ?? [] as $profesor)
                                    <option value="{{ $profesor->id }}" {{ request('profesor') == $profesor->id ? 'selected' : '' }}>{{ $profesor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="alumno">Alumno:</label>
                                <select name="alumno" id="alumno">
                                    <option value="">Todos los alumnos</option>
                                    @foreach($alumnos ?? [] as $alumno)
                                    <option value="{{ $alumno->id }}" {{ request('alumno') == $alumno->id ? 'selected' : '' }}>{{ $alumno->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="estado">Estado:</label>
                                <select name="estado" id="estado">
                                    <option value="">Todos los estados</option>
                                    @foreach($tiposAsistencia ?? [] as $tipoAsistencia)
                                    <option value="{{ $tipoAsistencia->id }}" {{ request('estado') == $tipoAsistencia->id ? 'selected' : '' }}>{{ $tipoAsistencia->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-group">
                                <label>Rango de fechas:</label>
                                <div class="date-range-container">
                                    <div class="date-field">
                                        <input type="text" name="fecha_inicio" id="fecha_inicio" placeholder="Fecha inicio" class="date-picker">
                                    </div>
                                    <div class="date-field">
                                        <input type="text" name="fecha_fin" id="fecha_fin" placeholder="Fecha fin" class="date-picker">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-buttons">
                            <button type="button" id="resetFilters" class="filter-button filter-reset">Limpiar filtros</button>
                            <button type="submit" class="filter-button filter-apply">Aplicar filtros</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de asistencias -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Registro de Asistencias</h2>
                    <div class="section__actions">
                        <a href="{{ route('admin.asistencias.previsualizar-pdf', request()->query()) }}" target="_blank" class="btn btn-secondary" title="Ver PDF en nueva ventana">
                            <i class="fas fa-eye"></i> Previsualizar PDF
                        </a>
                        <a href="{{ route('admin.asistencias.exportar-pdf', request()->query()) }}" class="btn btn-primary" title="Descargar PDF">
                            <i class="fas fa-download"></i> Exportar PDF
                        </a>
                    </div>
                </div>
                <div class="section__content">
                    <table class="data-table" id="asistenciasTable">
                        <thead class="data-table__head">
                            <tr>
                                <th class="data-table__header">ID</th>
                                <th class="data-table__header">Estudiante</th>
                                <th class="data-table__header">Materia</th>
                                <th class="data-table__header">Profesor</th>
                                <th class="data-table__header">Fecha</th>
                                <th class="data-table__header">Hora</th>
                                <th class="data-table__header">Estado</th>
                                <th class="data-table__header">Justificación</th>
                                <th class="data-table__header text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="data-table__body"> @forelse($asistencias ?? [] as $asistencia)
                            <tr>
                                <td class="data-table__cell">{{ $asistencia->id }}</td>
                                <td class="data-table__cell">{{ $asistencia->alumno->name ?? 'No disponible' }}</td>
                                <td class="data-table__cell">{{ $asistencia->materia->nombre ?? 'No disponible' }}</td>
                                <td class="data-table__cell">{{ $asistencia->nombre_profesor }}</td>
                                <td class="data-table__cell">{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y') }}</td>
                                <td class="data-table__cell">{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('H:i') }}</td>                                <td class="data-table__cell">
                                    <span class="attendance-status {{ $asistencia->estado_css_class }}">
                                        {{ $asistencia->estado_texto }}
                                    </span>
                                </td>
                                <td class="data-table__cell">{{ $asistencia->justificacion ?? 'Sin justificación' }}</td>
                                <td class="data-table__cell">
                                    <div class="data-table__actions">
                                        <button class="data-table__action btn-ver-asistencia"
                                            title="Ver detalles"
                                            data-id="{{ $asistencia->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($asistencia->tipo_asistencia_id != 1 && !$asistencia->justificacion)
                                        <button class="data-table__action btn-justify-asistencia"
                                            title="Justificar asistencia"
                                            data-id="{{ $asistencia->id }}">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="data-table__cell" style="text-align: center;">No hay registros de asistencia disponibles</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- Paginación -->
                    <div class="pagination"> {{ $asistencias->links() ?? '' }}
                    </div>
                </div>
            </div>

            <!-- Resumen de asistencias -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Resumen</h2>
                </div>
                <div class="section__content">
                    <div class="summary-cards">
                        <div class="summary-card">
                            <div class="summary-card__header">
                                <h3 class="summary-card__title">Total Asistencias</h3>
                                <div class="summary-card__icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                            </div>
                            <div class="summary-card__content">
                                <div class="summary-card__value">{{ $totalAsistencias ?? 0 }}</div>
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
                                <div class="summary-card__value">{{ $totalPresentes ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="summary-card">
                            <div class="summary-card__header">
                                <h3 class="summary-card__title">Ausentes</h3>
                                <div class="summary-card__icon">
                                    <i class="fas fa-user-times"></i>
                                </div>
                            </div>
                            <div class="summary-card__content">
                                <div class="summary-card__value">{{ $totalAusentes ?? 0 }}</div>
                            </div>
                        </div>                        <div class="summary-card">
                            <div class="summary-card__header">
                                <h3 class="summary-card__title">Justificadas</h3>
                                <div class="summary-card__icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                            <div class="summary-card__content">
                                <div class="summary-card__value">{{ $totalJustificados ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="summary-card">
                            <div class="summary-card__header">
                                <h3 class="summary-card__title">Índice</h3>
                                <div class="summary-card__icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                            </div>
                            <div class="summary-card__content">
                                <div class="summary-card__value">{{ $totalAsistencias > 0 ? round(($totalPresentes / $totalAsistencias) * 100) : 0 }}%</div>
                                <div class="summary-card__stats">
                                    <small>Asistencia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal para ver detalles de asistencia -->
<div class="modal-overlay" id="verAsistenciaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalles de Asistencia</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="user-form">
            <div class="attendance-details-card">
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-user"></i> Estudiante:</span>
                    <span class="detail-value" id="detailEstudiante"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-book"></i> Materia:</span>
                    <span class="detail-value" id="detailMateria"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-chalkboard-teacher"></i> Profesor:</span>
                    <span class="detail-value" id="detailProfesor"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-calendar-day"></i> Fecha:</span>
                    <span class="detail-value" id="detailFecha"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-clock"></i> Hora:</span>
                    <span class="detail-value" id="detailHora"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-info-circle"></i> Estado:</span>
                    <span class="detail-value" id="detailEstado"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-comment"></i> Justificación:</span>
                    <span class="detail-value" id="detailJustificacion"></span>
                </div>
                <div class="detail-row" id="qrVerificationContainer">
                    <span class="detail-label"><i class="fas fa-qrcode"></i> Verificación QR:</span>
                    <span class="detail-value" id="detailQrVerification"></span>
                </div>
                <div class="detail-row" id="ubicacionContainer">
                    <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Ubicación:</span>
                    <span class="detail-value" id="detailUbicacion"></span>
                </div>
                <div class="detail-row" id="dispositivoContainer">
                    <span class="detail-label"><i class="fas fa-mobile-alt"></i> Dispositivo:</span>
                    <span class="detail-value" id="detailDispositivo"></span>
                </div>
            </div>

            <div class="btn-container">
                <button type="button" class="btn-cancel"><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para justificar ausencia -->
<div class="modal-overlay" id="justificarAusenciaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Justificar Asistencia</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="justificarAusenciaForm" action="" method="POST" class="user-form">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="form-group">
                <label for="justificacionText">Justificación:</label>
                <textarea id="justificacionText" name="justificacion" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="evidenciaFile">Evidencia (opcional):</label>
                <input type="file" id="evidenciaFile" name="evidencia">
                <small>Formatos permitidos: PDF, JPG, PNG. Máximo 2MB.</small>
            </div>

            <div class="btn-container">
                <button type="button" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Justificación</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar selectores de fecha
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            locale: "es",
            allowInput: true
        });

        // Búsqueda de asistencias
        const searchInput = document.getElementById('asistenciaSearchInput');
        const tableRows = document.querySelectorAll('#asistenciasTable tbody tr');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                tableRows.forEach(row => {
                    const studentName = row.cells[1].textContent.toLowerCase();
                    const materia = row.cells[2].textContent.toLowerCase();

                    if (studentName.includes(searchTerm) || materia.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        } // Configuración para filtros en móviles
        const filtersContainer = document.getElementById('filtersContainer');
        if (filtersContainer && window.innerWidth < 768) {
            // En móviles, mostramos los filtros siempre visibles para mejor accesibilidad
            filtersContainer.classList.add('filters-mobile');
        }

        // Limpiar filtros
        const resetFiltersBtn = document.getElementById('resetFilters');
        const filterForm = document.getElementById('attendanceFilterForm');

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
            });
        }    // Ver detalles de asistencia
        const verAsistenciaBtns = document.querySelectorAll('.btn-ver-asistencia');
        const verAsistenciaModal = document.getElementById('verAsistenciaModal');

        if (verAsistenciaBtns.length && verAsistenciaModal) {
            verAsistenciaBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const asistenciaId = this.getAttribute('data-id');                    // Construir la URL correctamente con la ruta de Laravel
                    const detailsUrl = `{{ route('admin.asistencias.details', ['id' => '__ID__']) }}`.replace('__ID__', asistenciaId);
                      // Usar AJAX para obtener los datos detallados de la asistencia
                    console.log('Solicitando detalles de asistencia:', detailsUrl);
                    fetch(detailsUrl)
                        .then(response => {
                            if (!response.ok) {
                                console.error('Respuesta no exitosa:', response.status, response.statusText);
                                throw new Error(`Error ${response.status}: ${response.statusText}`);
                            }
                            console.log('Respuesta recibida correctamente');
                            return response.json();
                        })
                        .then(data => {
                            // Llenar los campos del modal con los datos
                            document.getElementById('detailEstudiante').textContent = data.alumno || 'No disponible';
                            document.getElementById('detailMateria').textContent = data.materia || 'No disponible';
                            document.getElementById('detailProfesor').textContent = data.profesor || 'No disponible';
                            document.getElementById('detailFecha').textContent = data.fecha || 'No disponible';
                            document.getElementById('detailHora').textContent = data.hora || 'No disponible';                            // Usar el estado directamente de la respuesta del servidor
                            const estadoElement = document.getElementById('detailEstado');

                            // Mostrar la descripción del estado que viene de la tabla tipo_asistencia
                            const estadoText = data.estado_descripcion || 'No definido';
                            estadoElement.textContent = estadoText;

                            // Usar las clases CSS que provienen directamente del backend a través de accesorios
                            let estadoClass = 'detail-value';
                            
                            // Si tenemos una clase CSS personalizada del backend, usarla
                            if (data.estado_css_class) {
                                // Convertir de 'attendance-xxx' a 'status-xxx' para mantener la consistencia visual
                                const statusClass = data.estado_css_class.replace('attendance-', 'status-');
                                estadoClass += ' ' + statusClass;
                            } else {
                                estadoClass += ' status-desconocido';
                            }
                            
                            estadoElement.className = estadoClass;

                            // Mostrar justificación
                            const justificacionElement = document.getElementById('detailJustificacion');
                            justificacionElement.textContent = data.justificacion || 'Sin justificación';

                            // Ocultar campos opcionales que no están implementados aún
                            document.getElementById('qrVerificationContainer').style.display = 'none';
                            document.getElementById('ubicacionContainer').style.display = 'none';
                            document.getElementById('dispositivoContainer').style.display = 'none';

                            // Mostrar el modal (asegurarse de usar display flex)
                            verAsistenciaModal.style.display = 'flex';
                            verAsistenciaModal.classList.add('active');
                        })                        .catch(error => {
                            console.error('Error al obtener detalles:', error);
                            
                            // Mostrar mensaje más descriptivo al usuario
                            const errorMessage = `Error al cargar los detalles: ${error.message}. URL: ${detailsUrl}`;
                            console.error(errorMessage);
                            
                            // Verificar si hay problemas con CORS
                            console.log('Verificando posibles problemas con CORS o autenticación...');
                            
                            // Informar al usuario
                            alert('Error al cargar los detalles de la asistencia. Por favor, intente nuevamente o contacte al administrador.');
                            
                            // Si hay un elemento de estado, podemos mostrar el error allí también
                            if (document.getElementById('detailEstado')) {
                                document.getElementById('detailEstado').textContent = 'Error al cargar datos';
                                document.getElementById('detailEstado').className = 'detail-value status-error';
                            }
                        });
                });
            });
        } // Justificar ausencia
        const justificarBtns = document.querySelectorAll('.btn-justify-asistencia');
        const justificarModal = document.getElementById('justificarAusenciaModal');
        const justificarForm = document.getElementById('justificarAusenciaForm');

        if (justificarBtns.length && justificarModal && justificarForm) {
            justificarBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const ausenciaId = this.getAttribute('data-id');

                    // Actualizar la acción del formulario con el ID correcto
                    justificarForm.action = `/admin/asistencias/${ausenciaId}/justificar`;

                    // Mostrar el modal
                    justificarModal.style.display = 'flex';
                    justificarModal.classList.add('active');
                });
            });
        } // Cerrar modales
        const modalCloseBtns = document.querySelectorAll('.modal-close, .btn-cancel');
        const modals = document.querySelectorAll('.modal-overlay');

        if (modalCloseBtns.length && modals.length) {
            modalCloseBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    modals.forEach(modal => {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    });
                });
            });
        }

        // Funcionalidad para botones de PDF ya está arriba
    });
</script>
@endsection