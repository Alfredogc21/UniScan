@extends('layouts.profesor')

@section('title', 'Alumnos - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/profesor/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/profesor/alumnos.css') }}">
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
                    <a href="{{ route('profesor.dashboard') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-home"></i></span>
                        <span class="nav__link-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('profesor.alumnos') }}" class="nav__link nav__link--active">
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
                    <a href="{{ route('profesor.asistencias') }}" class="nav__link">
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

            <h1 class="header__title">Alumnos</h1>

            <div class="header__search">
                <span class="search__icon"><i class="fas fa-search"></i></span>
                <input type="text" id="userSearchInput" class="search__input" placeholder="Buscar alumno...">
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

            <!-- Tabla de alumnos -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Listado de Alumnos</h2>
                    <div class="section__actions">
                        <button id="btnAddUser" class="btn-action">
                            <i class="fas fa-plus"></i> Agregar Alumno
                        </button>
                    </div>
                </div>
                <div class="section__content">                    <table class="data-table" id="usersTable">
                        <thead class="data-table__head">
                            <tr>
                                <th class="data-table__header">ID</th>
                                <th class="data-table__header">Nombre</th>
                                <th class="data-table__header">Email</th>
                                <th class="data-table__header">Fecha Registro</th>
                                <th class="data-table__header">Último Acceso</th>
                                <th class="data-table__header">% Asistencia</th>
                                <th class="data-table__header">Materias</th>
                                <th class="data-table__header">Estado</th>
                                <th class="data-table__header">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="data-table__body">
                            @forelse($alumnos ?? [] as $alumno)
                            <tr>
                                <td class="data-table__cell">{{ $alumno->id }}</td>
                                <td class="data-table__cell">{{ $alumno->name }}</td>
                                <td class="data-table__cell">{{ $alumno->email }}</td>
                                <td class="data-table__cell">
                                    {{ $alumno->created_at ? $alumno->created_at->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="data-table__cell">
                                    {{ $alumno->ultimo_acceso ? \Carbon\Carbon::parse($alumno->ultimo_acceso)->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="data-table__cell">
                                    <span class="attendance-percentage {{ $alumno->porcentaje_asistencia >= 80 ? 'high' : ($alumno->porcentaje_asistencia >= 60 ? 'medium' : 'low') }}">
                                        {{ $alumno->porcentaje_asistencia }}%
                                    </span>
                                </td>
                                <td class="data-table__cell" title="{{ $alumno->materias_compartidas }}">
                                    {{ Str::limit($alumno->materias_compartidas, 30) }}
                                </td>
                                <td class="data-table__cell">
                                    <span class="status-badge status-{{ $alumno->estado_id == 1 ? 'active' : 'inactive' }}">
                                        {{ $alumno->estado_id == 1 ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="data-table__cell">
                                    <div class="data-table__actions">
                                        <button class="data-table__action btn-edit-user" title="Editar alumno" data-user-id="{{ $alumno->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('profesor.alumnos.delete', $alumno->id) }}" method="POST" class="delete-form d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="data-table__action btn-delete" title="Eliminar alumno">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        <button class="data-table__action btn-view-attendance" title="Ver detalles de asistencia" data-user-id="{{ $alumno->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="data-table__cell text-center">No hay alumnos matriculados en sus materias</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contenedor para notificaciones -->
            <div class="notifications-container"></div>
        </div>
    </main>
</div>

<!-- Modal para editar alumno -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Alumno</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="editUserForm" class="user-form">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="editUserId" name="user_id">

            <div class="form-group">
                <label for="editName">Nombre</label>
                <input type="text" id="editName" name="name" required>
            </div>

            <div class="form-group">
                <label for="editEmail">Email</label>
                <input type="email" id="editEmail" name="email" required>
            </div>            <div class="form-group">
                <label for="editEstado">Estado</label>
                <select id="editEstado" name="estado_id" required>
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="editPassword">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" id="editPassword" name="password">
            </div>

            <div class="btn-container">
                <button type="button" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para agregar alumno -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Agregar Nuevo Alumno</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addUserForm" action="{{ route('profesor.alumnos.store') }}" method="POST" class="user-form">
            @csrf

            <div class="form-group">
                <label for="addName">Nombre</label>
                <input type="text" id="addName" name="name" required>
            </div>            <div class="form-group">
                <label for="addEmail">Email</label>
                <input type="email" id="addEmail" name="email" required>
            </div>

            <!-- Alumno siempre tendrá role_id = 3 -->
            <input type="hidden" name="role_id" value="3">

            <div class="form-group">
                <label for="addMateria">Materia a Asociar</label>
                <select id="addMateria" name="materia_id" required>
                    <option value="">Seleccionar materia...</option>
                    @foreach($materias ?? [] as $materia)
                        <option value="{{ $materia->id }}">{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </div>            <div class="form-group">
                <label for="addEstado">Estado</label>
                <select id="addEstado" name="estado_id" required>
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="addPassword">Contraseña</label>
                <input type="password" id="addPassword" name="password" required>
            </div>

            <div class="form-group">
                <label for="addPasswordConfirmation">Confirmar Contraseña</label>
                <input type="password" id="addPasswordConfirmation" name="password_confirmation" required>
            </div>

            <div class="btn-container">
                <button type="button" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-save">Agregar Alumno</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para ver detalles de asistencia -->
<div class="modal-overlay" id="attendanceDetailsModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3>Detalles de Asistencia - <span id="attendanceStudentName"></span></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Filtros -->
            <div class="attendance-filters">
                <div class="filter-group">
                    <label for="dateFrom">Desde:</label>
                    <input type="date" id="dateFrom" class="form-control">
                </div>
                <div class="filter-group">
                    <label for="dateTo">Hasta:</label>
                    <input type="date" id="dateTo" class="form-control">
                </div>
                <div class="filter-group">
                    <label for="attendanceType">Tipo:</label>
                    <select id="attendanceType" class="form-control">
                        <option value="">Todos</option>
                        <option value="1">Presente</option>
                        <option value="2">Ausente</option>
                        <option value="3">Justificado</option>
                    </select>
                </div>
                <button id="applyFilters" class="btn-action">Aplicar Filtros</button>
            </div>

            <!-- Estadísticas generales -->
            <div class="attendance-stats">
                <div class="stat-card">
                    <div class="stat-number" id="totalAttendances">0</div>
                    <div class="stat-label">Total Asistencias</div>
                </div>
                <div class="stat-card stat-present">
                    <div class="stat-number" id="totalPresent">0</div>
                    <div class="stat-label">Presente</div>
                </div>
                <div class="stat-card stat-absent">
                    <div class="stat-number" id="totalAbsent">0</div>
                    <div class="stat-label">Ausente</div>
                </div>
                <div class="stat-card stat-justified">
                    <div class="stat-number" id="totalJustified">0</div>
                    <div class="stat-label">Justificado</div>
                </div>
                <div class="stat-card stat-percentage">
                    <div class="stat-number" id="attendancePercentage">0%</div>
                    <div class="stat-label">% Asistencia</div>
                </div>
            </div>

            <!-- Gráfico de tendencia -->
            <div class="chart-container">
                <h4>Tendencia de Asistencia (Últimos 30 días)</h4>
                <canvas id="attendanceTrendChart" width="400" height="200"></canvas>
            </div>

            <!-- Estadísticas por materia -->
            <div class="subject-stats">
                <h4>Estadísticas por Materia</h4>
                <div id="subjectStatsContainer">
                    <!-- Se llenará dinámicamente -->
                </div>
            </div>

            <!-- Historial de asistencias -->
            <div class="attendance-history">
                <h4>Historial de Asistencias</h4>
                <div class="attendance-table-container">
                    <table class="data-table" id="attendanceHistoryTable">                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Materia</th>
                                <th>Tipo</th>
                                <th>Justificación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llenará dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para justificar asistencia -->
<div class="modal-overlay" id="justifyModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Justificar Asistencia</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="justifyForm">
            @csrf
            <input type="hidden" id="justifyAttendanceId" name="attendance_id">
            
            <div class="form-group">
                <label for="justification">Justificación:</label>
                <textarea id="justification" name="justificacion" rows="4" required placeholder="Ingrese la justificación para esta asistencia..."></textarea>
            </div>
            
            <div class="btn-container">
                <button type="button" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-save">Justificar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/profesor/dashboard.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // La gestión del sidebar ahora está en dashboard.js        // Búsqueda de alumnos
        const searchInput = document.getElementById('userSearchInput');
        const tableRows = document.querySelectorAll('#usersTable tbody tr');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                tableRows.forEach(row => {
                    const name = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const email = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                    const materias = row.querySelector('td:nth-child(7)')?.textContent.toLowerCase() || '';
                    const estado = row.querySelector('td:nth-child(8)')?.textContent.toLowerCase() || '';

                    if (name.includes(searchTerm) || email.includes(searchTerm) || materias.includes(searchTerm) || estado.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Mostrar/Ocultar modales
        const btnAddUser = document.getElementById('btnAddUser');
        const editButtons = document.querySelectorAll('.btn-edit-user');
        const modalCloseButtons = document.querySelectorAll('.modal-close');
        const cancelButtons = document.querySelectorAll('.btn-cancel');
        const addUserModal = document.getElementById('addUserModal');
        const editUserModal = document.getElementById('editUserModal');
        const attendanceDetailsModal = document.getElementById('attendanceDetailsModal');

        // Abrir modal para agregar alumno
        if (btnAddUser && addUserModal) {
            btnAddUser.addEventListener('click', function() {
                addUserModal.style.display = 'flex';
            });
        }

        // Abrir modal para editar alumno
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                // Hacemos petición AJAX para obtener los datos del alumno
                fetch(`{{ url('/profesor/alumnos/') }}/${userId}/edit`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        document.getElementById('editUserId').value = data.id;
                        document.getElementById('editName').value = data.name;
                        document.getElementById('editEmail').value = data.email;
                        document.getElementById('editEstado').value = data.estado_id;

                        editUserModal.style.display = 'flex';
                    })
                    .catch(error => {
                        console.error('Error al cargar datos del alumno:', error);
                        alert('Error al cargar datos del alumno: ' + error.message);
                    });
            });
        });

        // Abrir modal para ver detalles de asistencia
        const viewAttendanceButtons = document.querySelectorAll('.btn-view-attendance');
        const attendanceModal = document.getElementById('attendanceDetailsModal');
        const justifyModal = document.getElementById('justifyModal');
        let attendanceTrendChart = null;
        let currentStudentId = null;
        let attendanceData = [];
        let filteredData = [];

        viewAttendanceButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                currentStudentId = userId;
                loadAttendanceDetails(userId);
            });
        });

        // Función para cargar detalles de asistencia
        function loadAttendanceDetails(userId) {
            fetch(`{{ url('/profesor/alumnos/') }}/${userId}/asistencias`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    attendanceData = data.asistencias;
                    filteredData = [...attendanceData];
                    
                    // Llenar datos del modal
                    document.getElementById('attendanceStudentName').textContent = data.alumno.name;
                    
                    // Estadísticas generales
                    document.getElementById('totalAttendances').textContent = data.estadisticas.total;
                    document.getElementById('totalPresent').textContent = data.estadisticas.presente;
                    document.getElementById('totalAbsent').textContent = data.estadisticas.ausente;
                    document.getElementById('totalJustified').textContent = data.estadisticas.justificado;
                    document.getElementById('attendancePercentage').textContent = data.estadisticas.porcentaje + '%';
                    
                    // Estadísticas por materia
                    renderSubjectStats(data.estadisticasPorMateria);
                    
                    // Gráfico de tendencia
                    renderTrendChart(data.tendencia);
                    
                    // Historial de asistencias
                    renderAttendanceHistory(filteredData);
                    
                    // Mostrar modal
                    attendanceModal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error al cargar detalles de asistencia:', error);
                    alert('Error al cargar detalles de asistencia: ' + error.message);
                });
        }

        // Función para renderizar estadísticas por materia
        function renderSubjectStats(stats) {
            const container = document.getElementById('subjectStatsContainer');
            container.innerHTML = '';
            
            stats.forEach(stat => {
                const card = document.createElement('div');
                card.className = 'subject-stat-card';
                card.innerHTML = `
                    <h5>${stat.materia}</h5>
                    <div class="subject-stat-row">
                        <span>Total:</span>
                        <span>${stat.total}</span>
                    </div>
                    <div class="subject-stat-row">
                        <span>Presente:</span>
                        <span style="color: #28a745;">${stat.presente}</span>
                    </div>
                    <div class="subject-stat-row">
                        <span>Ausente:</span>
                        <span style="color: #dc3545;">${stat.ausente}</span>
                    </div>
                    <div class="subject-stat-row">
                        <span>Justificado:</span>
                        <span style="color: #ffc107;">${stat.justificado}</span>
                    </div>
                    <div class="subject-stat-row">
                        <span>% Asistencia:</span>
                        <span style="color: #007bff; font-size: 1.1rem;">${stat.porcentaje}%</span>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // Función para renderizar gráfico de tendencia
        function renderTrendChart(tendenciaData) {
            const ctx = document.getElementById('attendanceTrendChart').getContext('2d');
            
            // Destruir gráfico anterior si existe
            if (attendanceTrendChart) {
                attendanceTrendChart.destroy();
            }
            
            const labels = tendenciaData.map(item => {
                const date = new Date(item.fecha);
                return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
            });
            
            attendanceTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Presente',
                            data: tendenciaData.map(item => item.presente),
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Ausente',
                            data: tendenciaData.map(item => item.ausente),
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Justificado',
                            data: tendenciaData.map(item => item.justificado),
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Función para renderizar historial de asistencias
        function renderAttendanceHistory(asistencias) {
            const tbody = document.querySelector('#attendanceHistoryTable tbody');
            tbody.innerHTML = '';
            
            asistencias.forEach(asistencia => {
                const row = document.createElement('tr');
                const fecha = new Date(asistencia.fecha_hora);
                const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                }) + ' ' + fecha.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                  let tipoClass = '';
                
                switch(asistencia.tipo) {
                    case 'Presente':
                        tipoClass = 'style="color: #28a745; font-weight: bold;"';
                        break;
                    case 'Ausente':
                        tipoClass = 'style="color: #dc3545; font-weight: bold;"';
                        break;
                    case 'Justificado':
                        tipoClass = 'style="color: #ffc107; font-weight: bold;"';
                        break;
                }
                  row.innerHTML = `
                    <td>${fechaFormateada}</td>
                    <td>${asistencia.materia}</td>
                    <td ${tipoClass}>${asistencia.tipo}</td>
                    <td>${asistencia.justificacion || '-'}</td>
                `;
                
                tbody.appendChild(row);
            });
        }

        // Función para abrir modal de justificación
        function openJustifyModal(attendanceId) {
            document.getElementById('justifyAttendanceId').value = attendanceId;
            document.getElementById('justification').value = '';
            justifyModal.style.display = 'flex';
        }

        // Manejar formulario de justificación
        const justifyForm = document.getElementById('justifyForm');
        if (justifyForm) {
            justifyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const attendanceId = document.getElementById('justifyAttendanceId').value;
                const justification = document.getElementById('justification').value;
                
                fetch(`{{ url('/profesor/asistencias/') }}/${attendanceId}/justificar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        justificacion: justification
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        justifyModal.style.display = 'none';
                        // Recargar detalles de asistencia
                        loadAttendanceDetails(currentStudentId);
                        alert('Asistencia justificada correctamente');
                    } else {
                        alert('Error al justificar asistencia: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al justificar asistencia');
                });
            });
        }

        // Manejar filtros de asistencia
        const applyFiltersBtn = document.getElementById('applyFilters');
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', function() {
                const dateFrom = document.getElementById('dateFrom').value;
                const dateTo = document.getElementById('dateTo').value;
                const attendanceType = document.getElementById('attendanceType').value;
                
                filteredData = attendanceData.filter(asistencia => {
                    let passes = true;
                    
                    // Filtro por fecha
                    if (dateFrom) {
                        const asistenciaDate = new Date(asistencia.fecha_hora);
                        const fromDate = new Date(dateFrom);
                        if (asistenciaDate < fromDate) passes = false;
                    }
                    
                    if (dateTo) {
                        const asistenciaDate = new Date(asistencia.fecha_hora);
                        const toDate = new Date(dateTo + 'T23:59:59');
                        if (asistenciaDate > toDate) passes = false;
                    }
                    
                    // Filtro por tipo
                    if (attendanceType) {
                        const tipoMap = {
                            '1': 'Presente',
                            '2': 'Ausente',
                            '3': 'Justificado'
                        };
                        if (asistencia.tipo !== tipoMap[attendanceType]) passes = false;
                    }
                    
                    return passes;
                });
                
                renderAttendanceHistory(filteredData);
            });
        }

        // Cerrar modales de detalles de asistencia
        const attendanceModalCloses = attendanceModal.querySelectorAll('.modal-close, .btn-cancel');
        attendanceModalCloses.forEach(button => {
            button.addEventListener('click', function() {
                attendanceModal.style.display = 'none';
                if (attendanceTrendChart) {
                    attendanceTrendChart.destroy();
                    attendanceTrendChart = null;
                }
            });
        });

        const justifyModalCloses = justifyModal.querySelectorAll('.modal-close, .btn-cancel');
        justifyModalCloses.forEach(button => {
            button.addEventListener('click', function() {
                justifyModal.style.display = 'none';
            });
        });

        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
            if (event.target === attendanceModal) {
                attendanceModal.style.display = 'none';
                if (attendanceTrendChart) {
                    attendanceTrendChart.destroy();
                    attendanceTrendChart = null;
                }
            }
            
            if (event.target === justifyModal) {
                justifyModal.style.display = 'none';
            }
        });

        // Hacer disponible globalmente para los botones inline
        window.openJustifyModal = openJustifyModal;
    });
</script>
@endsection