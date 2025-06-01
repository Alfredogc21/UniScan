@extends('layouts.admin')

@section('title', 'Gestión de Materias - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/materias.css') }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Biblioteca QRCode.js para generación de QR en el cliente -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
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
                    <a href="{{ route('admin.asistencias') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="nav__link-text">Asistencias</span>
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
                    <button id="btnAddMateria" class="section__action">
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
                            <tr>
                                <td class="data-table__cell">{{ $materia->id }}</td>
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
                                <td class="data-table__cell">
                                    <div class="data-table__actions">
                                        <button class="data-table__action btn-edit-materia"
                                            title="Editar materia"
                                            data-id="{{ $materia->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="data-table__action btn-generate-qr"
                                            title="Generar QR"
                                            data-id="{{ $materia->id }}">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.materias.destroy', $materia->id) }}"
                                            style="display: inline;"
                                            onsubmit="return confirm('¿Estás seguro de eliminar esta materia?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="data-table__action btn-delete-materia"
                                                title="Eliminar materia">
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
        </div>
        <div class="qr-content">
            <div id="qrContainer">
                <!-- Contenedor para la imagen del servidor -->
                <div id="serverQrContainer">
                    <img id="qrImage" src="" alt="Código QR">
                </div>

                <!-- Contenedor para QR generado en el cliente (alternativa) -->
                <div id="clientQrContainer">
                    <div id="qrCodeCanvas"></div>
                    <p style="color: #28a745; font-weight: 500; margin-bottom: 15px;">✓ QR generado localmente con éxito</p>
                </div>

                <!-- Información detallada del QR -->
                <div class="qr-info">
                    <h4>Información contenida en el QR:</h4>
                    <div class="qr-data">
                        <div><strong>Token QR:</strong> <span id="qrTokenData"></span></div>
                        <div><strong>Materia:</strong> <span id="qrNombreData"></span></div>
                        <div><strong>Aula:</strong> <span id="qrAulaData"></span></div>
                        <div><strong>Curso:</strong> <span id="qrCursoData"></span></div>
                        <div><strong>Horario:</strong> <span id="qrHorarioData"></span></div>
                    </div>
                </div>
            </div>
            <div class="btn-container">
                <button type="button" class="btn-cancel">Cerrar</button>
                <a id="downloadQr" href="" download="" data-format="png" class="btn-save">Descargar QR</a>
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

        // Mostrar/Ocultar modales básicos
        const btnAddMateria = document.getElementById('btnAddMateria');
        const modalCloseButtons = document.querySelectorAll('.modal-close');
        const cancelButtons = document.querySelectorAll('.btn-cancel');
        const addMateriaModal = document.getElementById('addMateriaModal');
        const editMateriaModal = document.getElementById('editMateriaModal');

        // Abrir modal para agregar materia
        if (btnAddMateria && addMateriaModal) {
            btnAddMateria.addEventListener('click', function() {
                addMateriaModal.style.display = 'flex';
            });
        }

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

<!-- QR Code Generator Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- Scripts principales para funcionalidad -->
<script src="{{ asset('js/admin/qr-generator.js') }}"></script>
<script src="{{ asset('js/admin/materias_edit.js') }}"></script>
<script src="{{ asset('js/admin/materias_qr.js') }}"></script>

<!-- Script para generar QR en el cliente como fallback -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mejorar el botón de descarga para usar el método alternativo si la descarga directa falla
        const downloadQrBtn = document.getElementById('downloadQr');
        if (downloadQrBtn) {
            // Simplificar el evento del botón de descarga
            downloadQrBtn.addEventListener('click', function(e) {
                // Prevenir la descarga predeterminada
                e.preventDefault();

                // La función downloadQR en materias_qr.js maneja toda la lógica
                // incluyendo prevención de descargas múltiples y conversión a PNG
                downloadQR();
            });
        }

        // Simplificamos la interfaz quitando la generación en el navegador
    });
</script>
@endsection