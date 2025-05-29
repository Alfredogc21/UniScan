@extends('layouts.admin')

@section('title', 'Gestión de Materias - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    /* Estilos mejorados para botones */
    .btn-edit-materia:hover, .btn-generate-qr:hover, .btn-delete-materia:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .btn-edit-materia:active, .btn-generate-qr:active, .btn-delete-materia:active {
        transform: translateY(1px);
    }
    
    #btnAddMateria:hover {
        box-shadow: 0 6px 15px rgba(37, 117, 252, 0.3);
        transform: translateY(-2px);
    }
    
    #btnAddMateria:active {
        transform: translateY(1px);
        box-shadow: 0 3px 8px rgba(37, 117, 252, 0.2);
    }
    
    .data-table__status--active {
        background-color: rgba(46, 204, 113, 0.2);
        color: #27ae60;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
        display: inline-block;
    }
    
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .modal-show {
        display: flex !important;
    }
    
    .data-table__status--inactive {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
        display: inline-block;
    }
    
    /* Mejora en el estilo de la tabla */
    .data-table {
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-radius: 10px;
        overflow: hidden;
    }
    
    .data-table__head {
        background: linear-gradient(to right, #f8f9fa, #e9ecef);
    }
    
    .data-table tr:hover {
        background-color: #f8f9fa;
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
                    <a href="{{ route('admin.materias') }}" class="nav__link nav__link--active">
                        <span class="nav__link-icon"><i class="fas fa-book"></i></span>
                        <span class="nav__link-text">Materias</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.attendance') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="nav__link-text">Asistencias</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.reports') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-chart-pie"></i></span>
                        <span class="nav__link-text">Reportes</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.settings') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-cog"></i></span>
                        <span class="nav__link-text">Configuración</span>
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
            <button class="actions__button mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="header__title">Gestión de Materias</h1>

            <div class="header__search">
                <span class="search__icon"><i class="fas fa-search"></i></span>
                <input type="text" id="materiaSearchInput" class="search__input" placeholder="Buscar materia...">
            </div>

            <div class="header__actions">
                <button class="actions__button">
                    <i class="fas fa-bell"></i>
                    <span class="actions__notification">3</span>
                </button>
                <button class="actions__button">
                    <i class="fas fa-envelope"></i>
                    <span class="actions__notification">5</span>
                </button>
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

            <!-- Tabla de materias -->
            <div class="content-section">
                <div class="section__header">
                    <h2 class="section__title">Materias del Sistema</h2>
                    <button id="btnAddMateria" class="section__action" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 10px rgba(37, 117, 252, 0.2); transition: all 0.3s ease;">
                        <i class="fas fa-plus" style="margin-right: 8px;"></i> Añadir nueva materia
                    </button>
                </div>
                <div class="section__content">
                    <table class="data-table" id="materiasTable">
                        <thead class="data-table__head">
                            <tr>
                                <th class="data-table__header">ID</th>
                                <th class="data-table__header">Nombre</th>
                                <th class="data-table__header">Profesor</th>
                                <th class="data-table__header">Aula</th>
                                <th class="data-table__header">Horario</th>
                                <th class="data-table__header">Curso</th>
                                <th class="data-table__header">QR</th>
                                <th class="data-table__header">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="data-table__body">
                            @foreach($materias as $materia)
                            <tr>                                <td class="data-table__cell">{{ $materia->id }}</td>
                                <td class="data-table__cell">{{ $materia->nombre }}</td>
                                <td class="data-table__cell">{{ $materia->profesor->name ?? 'Sin asignar' }}</td>
                                <td class="data-table__cell">{{ $materia->aula->nombre ?? 'No especificado' }}</td>
                                <td class="data-table__cell">{{ \Carbon\Carbon::parse($materia->horario_ingreso)->format('H:i') }} - {{ \Carbon\Carbon::parse($materia->horario_salida)->format('H:i') }}</td>
                                <td class="data-table__cell">{{ $materia->curso->nombre ?? 'No especificado' }}</td>
                                <td class="data-table__cell">
                                    @if($materia->qr_path)
                                        <span class="data-table__status data-table__status--active">Generado</span>
                                    @else
                                        <span class="data-table__status data-table__status--inactive">No generado</span>
                                    @endif
                                </td>
                                <td class="data-table__cell">                                    <div class="data-table__actions" style="display: flex; gap: 8px; justify-content: center;">
                                        <button class="data-table__action btn-edit-materia" 
                                                title="Editar materia" 
                                                data-id="{{ $materia->id }}"
                                                style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; border: none; width: 36px; height: 36px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); transition: transform 0.2s, box-shadow 0.2s;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="data-table__action btn-generate-qr" 
                                                title="Generar QR" 
                                                data-id="{{ $materia->id }}"
                                                style="background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; border: none; width: 36px; height: 36px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); transition: transform 0.2s, box-shadow 0.2s;">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.materias.destroy', $materia->id) }}" 
                                              style="display: inline;" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta materia?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="data-table__action btn-delete-materia" 
                                                   title="Eliminar materia"
                                                   style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border: none; width: 36px; height: 36px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); transition: transform 0.2s, box-shadow 0.2s;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal para editar materia -->
<div class="modal-overlay" id="editMateriaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Materia</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="editMateriaForm" class="user-form">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="editMateriaId" name="materia_id">
            
            <div class="form-group">
                <label for="editNombre">Nombre de la Materia</label>
                <input type="text" id="editNombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="editProfesor">Profesor</label>
                <select id="editProfesor" name="profesor_id" required>
                    <option value="">Seleccionar profesor</option>
                    @foreach($profesores as $profesor)
                        <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="editAula">Aula</label>
                <input type="text" id="editAula" name="aula" required>
            </div>
            
            <div class="form-group">
                <label for="editHorarioIngreso">Horario de Ingreso</label>
                <input type="time" id="editHorarioIngreso" name="horario_ingreso" required>
            </div>
            
            <div class="form-group">
                <label for="editHorarioSalida">Horario de Salida</label>
                <input type="time" id="editHorarioSalida" name="horario_salida" required>
            </div>
            
            <div class="form-group">
                <label for="editCurso">Curso</label>
                <input type="text" id="editCurso" name="curso" required>
            </div>
            
            <div class="btn-container">
                <button type="button" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para agregar materia -->
<div class="modal-overlay" id="addMateriaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Añadir Nueva Materia</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addMateriaForm" action="{{ route('admin.materias.store') }}" method="POST" class="user-form">
            @csrf

            <div class="form-group">
                <label for="addNombre">Nombre de la Materia</label>
                <input type="text" id="addNombre" name="nombre" required>
            </div>

            <div class="form-group">
                <label for="addProfesor">Profesor</label>
                <select id="addProfesor" name="profesor_id" required>
                    <option value="">Seleccionar profesor</option>
                    @foreach($profesores as $profesor)
                        <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="addAula">Aula</label>
                <input type="text" id="addAula" name="aula" required>
            </div>

            <div class="form-group">
                <label for="addHorarioIngreso">Horario de Ingreso</label>
                <input type="time" id="addHorarioIngreso" name="horario_ingreso" required>
            </div>

            <div class="form-group">
                <label for="addHorarioSalida">Horario de Salida</label>
                <input type="time" id="addHorarioSalida" name="horario_salida" required>
            </div>

            <div class="form-group">
                <label for="addCurso">Curso</label>
                <input type="text" id="addCurso" name="curso" required placeholder="Ej: 3er Año - Grupo A">
            </div>

            <div class="btn-container">
                <button type="button" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-save">Añadir Materia</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para mostrar QR -->
<div class="modal-overlay" id="qrModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Código QR - <span id="qrMateriaName"></span></h3>
            <button class="modal-close">&times;</button>
        </div>        <div class="qr-content">
            <div id="qrContainer" style="text-align: center; padding: 20px;">
                <img id="qrImage" src="" alt="Código QR" style="max-width: 300px; margin-bottom: 20px;">
                
                <!-- Información detallada del QR -->
                <div class="qr-info" style="text-align: left; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px; color: #333;">Información contenida en el QR:</h4>
                    <div class="qr-data">
                        <div style="margin-bottom: 8px;"><strong>Token QR:</strong> <span id="qrTokenData"></span></div>
                        <div style="margin-bottom: 8px;"><strong>Materia:</strong> <span id="qrNombreData"></span></div>
                        <div style="margin-bottom: 8px;"><strong>Aula:</strong> <span id="qrAulaData"></span></div>
                        <div style="margin-bottom: 8px;"><strong>Curso:</strong> <span id="qrCursoData"></span></div>
                        <div style="margin-bottom: 8px;"><strong>Horario:</strong> <span id="qrHorarioData"></span></div>
                    </div>
                </div>
            </div>
            <div class="btn-container" style="margin-top: 20px;">
                <button type="button" class="btn-cancel">Cerrar</button>
                <a id="downloadQr" href="" download="" class="btn-save">Descargar QR</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Búsqueda de materias
        const searchInput = document.getElementById('materiaSearchInput');
        const tableRows = document.querySelectorAll('#materiasTable tbody tr');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                tableRows.forEach(row => {
                    const nombre = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const profesor = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const aula = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    const curso = row.querySelector('td:nth-child(6)').textContent.toLowerCase();

                    if (nombre.includes(searchTerm) || profesor.includes(searchTerm) ||
                        aula.includes(searchTerm) || curso.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Mostrar/Ocultar modales
        const btnAddMateria = document.getElementById('btnAddMateria');
        const editButtons = document.querySelectorAll('.btn-edit-materia');
        const generateQrButtons = document.querySelectorAll('.btn-generate-qr');
        const modalCloseButtons = document.querySelectorAll('.modal-close');
        const cancelButtons = document.querySelectorAll('.btn-cancel');
        const addMateriaModal = document.getElementById('addMateriaModal');
        const editMateriaModal = document.getElementById('editMateriaModal');
        const qrModal = document.getElementById('qrModal');

        // Abrir modal para agregar materia
        if (btnAddMateria && addMateriaModal) {
            btnAddMateria.addEventListener('click', function() {
                addMateriaModal.style.display = 'flex';
            });
        }

        // Abrir modal para editar materia
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Mostrar el modal inmediatamente para evitar percepción de retraso
                editMateriaModal.style.display = 'flex';
                
                const materiaId = this.getAttribute('data-id');
                
                // Función para obtener la URL base
                function getBaseUrl() {
                    const path = window.location.pathname;
                    const segments = path.split('/');
                    const adminIndex = segments.findIndex(segment => segment === 'admin');
                    
                    if (adminIndex > -1) {
                        const basePath = segments.slice(0, adminIndex).join('/');
                        return window.location.origin + basePath;
                    }
                    return window.location.origin;
                }
                
                // Construir URL completa
                const baseUrl = getBaseUrl();
                const editUrl = `${baseUrl}/admin/materias/${materiaId}/edit`;
                
                console.log('URL para edición:', editUrl);
                
                fetch(editUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Datos recibidos:', data);
                        document.getElementById('editMateriaId').value = data.id;
                        document.getElementById('editNombre').value = data.nombre;
                        document.getElementById('editProfesor').value = data.profesor_id;
                        document.getElementById('editAula').value = data.aula ? data.aula.nombre : '';
                        document.getElementById('editHorarioIngreso').value = data.horario_ingreso;
                        document.getElementById('editHorarioSalida').value = data.horario_salida;
                        document.getElementById('editCurso').value = data.curso ? data.curso.nombre : '';
                    })
                    .catch(error => {
                        console.error('Error al cargar datos:', error);
                        // No mostrar alerta, solo registrar el error
                    });
            });
        });

        // Generar QR
        generateQrButtons.forEach(button => {
            button.addEventListener('click', function() {
                const materiaId = this.getAttribute('data-id');
                const materiaName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                
                // Función para obtener la URL base
                function getBaseUrl() {
                    const path = window.location.pathname;
                    const segments = path.split('/');
                    const adminIndex = segments.findIndex(segment => segment === 'admin');
                    
                    if (adminIndex > -1) {
                        const basePath = segments.slice(0, adminIndex).join('/');
                        return window.location.origin + basePath;
                    }
                    return window.location.origin;
                }
                
                // Construir URL completa
                const baseUrl = getBaseUrl();
                const qrUrl = `${baseUrl}/admin/materias/${materiaId}/generate-qr`;
                
                console.log('URL para QR:', qrUrl);
                
                fetch(qrUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('qrMateriaName').textContent = materiaName;
                        document.getElementById('qrImage').src = data.qr_url;
                        document.getElementById('downloadQr').href = data.qr_url;
                        document.getElementById('downloadQr').download = `qr_${materiaName.replace(/\s+/g, '_')}.png`;
                        
                        // Mostrar los datos del QR
                        if (data.qr_data) {
                            document.getElementById('qrTokenData').textContent = data.qr_data.token_qr;
                            document.getElementById('qrNombreData').textContent = data.qr_data.nombre;
                            document.getElementById('qrAulaData').textContent = data.qr_data.aula;
                            document.getElementById('qrCursoData').textContent = data.qr_data.curso;
                            document.getElementById('qrHorarioData').textContent = data.qr_data.horario;
                        }
                        
                        qrModal.style.display = 'flex';
                        
                        // Actualizar la tabla
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al generar el código QR');
                });
            });
        });

        // Cerrar modales
        modalCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.modal-overlay').style.display = 'none';
            });
        });

        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.modal-overlay').style.display = 'none';
            });
        });
    });
</script>

<script src="{{ asset('js/admin/materias_edit.js') }}"></script>
@endsection