@extends('layouts.profesor')

@section('title', 'Mis Materias - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/profesor/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/profesor/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/profesor/materias.css') }}">
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
                    <a href="{{ route('profesor.materias') }}" class="nav__link nav__link--active">
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
            <a href="{{ route('profesor.profile') }}" class="user-info">
                <div class="user-info__avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info__details">
                    <div class="user-info__name">{{ Auth::user()->name }}</div>
                    <div class="user-info__role">Profesor</div>
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

            <h1 class="header__title">Mis Materias</h1>

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
                    <h2 class="section__title">Mis Materias Asignadas</h2>
                    <!-- El botón de añadir materia se ha eliminado ya que los profesores no deberían añadir materias directamente -->
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
                                        <button class="data-table__action btn-view-materia"
                                            title="Ver detalles"
                                            data-id="{{ $materia->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="data-table__action btn-generate-qr"
                                            title="Generar QR"
                                            data-id="{{ $materia->id }}">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <a href="{{ route('profesor.asistencias', ['materia_id' => $materia->id]) }}" 
                                           class="data-table__action"
                                           title="Ver asistencias">
                                            <i class="fas fa-clipboard-check"></i>
                                        </a>
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

<!-- No necesitamos el modal de edición ya que lo hemos reemplazado por el modal de visualización -->
<!-- Modal de edición eliminado ya que los profesores no editan materias -->

<!-- Modal para visualizar detalles de materia -->
<div class="modal-overlay" id="viewMateriaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalles de la Materia</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="materia-details">
            <div class="detail-item">
                <span class="detail-label">Nombre:</span>
                <span id="viewNombre" class="detail-value"></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Aula:</span>
                <span id="viewAula" class="detail-value"></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Horario:</span>
                <span id="viewHorario" class="detail-value"></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Curso:</span>
                <span id="viewCurso" class="detail-value"></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Alumnos Registrados:</span>
                <span id="viewAlumnos" class="detail-value"></span>
            </div>

            <div class="btn-container">
                <button type="button" class="btn-cancel">Cerrar</button>
            </div>
        </div>
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
                    <p class="qr-success-message">✓ QR generado localmente con éxito</p>
                </div>

                <!-- Información detallada del QR -->
                <div class="qr-info">
                    <!-- Aquí se insertará el mensaje de validez del QR -->
                    <div id="qrValidityInfo"></div>
                    
                    <h4 class="qr-info-title">
                        Información contenida en el QR:
                    </h4>
                    <div class="qr-data">
                        <div><strong>Token QR:</strong> <span id="qrTokenData" class="monospace-text"></span></div>
                        <div><strong>Materia:</strong> <span id="qrNombreData"></span></div>
                        <div><strong>Aula:</strong> <span id="qrAulaData"></span></div>
                        <div><strong>Curso:</strong> <span id="qrCursoData"></span></div>
                        <div><strong>Horario:</strong> <span id="qrHorarioData"></span></div>
                        <div class="qr-data-full-width"><strong>Fecha de generación:</strong> <span id="qrFechaGenData"></span></div>
                        <div class="qr-data-note">Los códigos QR se regeneran automáticamente cada semana para mayor seguridad.</div>
                    </div>
                </div>
            </div>
            <div class="btn-container">
                <button type="button" class="btn-cancel">Cerrar</button>
                <a id="downloadQr" href="" download="qr-code.png" data-format="png" class="btn-save">
                   <i class="fas fa-download"></i>Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/profesor/dashboard.js') }}"></script>
<!-- QR Code Generator Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- Scripts principales para funcionalidad -->
<script src="{{ asset('js/profesor/qr-generator.js') }}"></script>
<script src="{{ asset('js/profesor/materias_qr.js') }}"></script>

<!-- Script para la descarga directa del QR -->
<script>
// Script para asegurar que la descarga funcione correctamente y manejar la validación semanal
document.addEventListener('DOMContentLoaded', function() {
    // Asegurar que el modal permanezca visible
    window.keepModalOpen = function() {
        const qrModal = document.getElementById('qrModal');
        if (qrModal) {
            qrModal.style.display = 'flex';
        }
    }
    
    // Mejorar la funcionalidad de descarga
    const downloadQrBtn = document.getElementById('downloadQr');
    if (downloadQrBtn) {
        downloadQrBtn.addEventListener('click', function(e) {
            console.log('Iniciando descarga directa:', this.href);
            // Crear un enlace temporal con la misma URL
            const tempLink = document.createElement('a');
            tempLink.href = this.href;
            tempLink.download = this.download || 'qr-code.png';
            tempLink.target = '_blank';
            document.body.appendChild(tempLink);
            tempLink.click();
            document.body.removeChild(tempLink);
            
            // Evita que el modal se cierre
            e.preventDefault();
            window.keepModalOpen();
            return false;
        });
    }
    
    // Función para mostrar información de validez del QR
    window.showQrValidityInfo = function(isExistingQr, updateDate) {
        const validityContainer = document.getElementById('qrValidityInfo');
        if (!validityContainer) return;
        
        let updateDateObj = null;
        try {
            if (updateDate) {
                updateDateObj = new Date(updateDate);
                // Establecer fecha de generación
                const fechaGenEl = document.getElementById('qrFechaGenData');
                if (fechaGenEl) {
                    fechaGenEl.textContent = updateDateObj.toLocaleString();
                }
            }
        } catch (e) {
            console.error('Error al parsear fecha:', e);
        }
        
        if (isExistingQr) {
            // Es un QR existente, calcular cuándo se regenerará
            validityContainer.className = 'qr-validity existing-qr';
            
            let mensaje = '<i class="fas fa-info-circle"></i> Este QR todavía es válido.';
            
            if (updateDateObj) {
                // Calcular fecha de regeneración (una semana después)
                const nextUpdate = new Date(updateDateObj);
                nextUpdate.setDate(nextUpdate.getDate() + 7);
                mensaje += ` Se regenerará automáticamente después del ${nextUpdate.toLocaleDateString()}.`;
            }
            
            validityContainer.innerHTML = mensaje;
        } else {
            // Es un QR recién generado
            validityContainer.className = 'qr-validity new-qr';
            validityContainer.innerHTML = '<i class="fas fa-sync"></i> Se ha generado un nuevo código QR que estará vigente durante 7 días.';
        }
    };
});
</script>

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
                        row.classList.remove('hidden-row');
                    } else {
                        row.classList.add('hidden-row');
                    }
                });
            });
        }

        // Mostrar/Ocultar modales
        const modalCloseButtons = document.querySelectorAll('.modal-close');
        const cancelButtons = document.querySelectorAll('.btn-cancel');
        const viewMateriaModal = document.getElementById('viewMateriaModal');
        const viewButtons = document.querySelectorAll('.btn-view-materia');

        // Configurar botones para ver detalles
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const materiaId = this.getAttribute('data-id');
                openViewModal(materiaId);
            });
        });

        // Función para abrir el modal de visualización
        function openViewModal(materiaId) {
            // Construir la URL correcta para la ruta de show
            const showUrl = `{{ url('/profesor/materias') }}/${materiaId}`;
            
            fetch(showUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                // Llenar el modal con los datos de la materia
                document.getElementById('viewNombre').textContent = data.nombre || '';
                document.getElementById('viewAula').textContent = data.aula ? data.aula.nombre : 'No especificado';
                document.getElementById('viewHorario').textContent = `${data.horario_ingreso || ''} - ${data.horario_salida || ''}`;
                document.getElementById('viewCurso').textContent = data.curso ? data.curso.nombre : 'No especificado';
                document.getElementById('viewAlumnos').textContent = data.alumnos_count || '0';
                
                // Ya no hay botón de generar QR en el modal de detalles
                
                // Mostrar el modal
                viewMateriaModal.style.display = 'flex';
            })
            .catch(error => {
                console.error('Error al obtener datos:', error);
                alert('Error al cargar los datos de la materia. Por favor, inténtelo de nuevo.');
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

        // Sobreescribir el comportamiento del fetch para interceptar las respuestas de QR
        if (window.fetch) {
            const originalFetch = window.fetch;
            window.fetch = function() {
                const fetchPromise = originalFetch.apply(this, arguments);
                
                // Solo procesar si es una petición al endpoint de generate-qr
                if (arguments[0] && arguments[0].toString().includes('generate-qr')) {
                    return fetchPromise.then(response => {
                        const originalJson = response.json;
                        response.json = function() {
                            return originalJson.call(this).then(data => {
                                if (data && data.success) {
                                    console.log('Datos QR recibidos:', data);
                                    
                                    // Verificar si es un QR existente o uno nuevo
                                    const isExistingQr = data.message && data.message.includes('existente');
                                    
                                    // Añadir timestamp a la URL de la imagen para evitar problemas de caché
                                    if (data.qr_url) {
                                        const qrImage = document.getElementById('qrImage');
                                        if (qrImage) {
                                            const timestamp = new Date().getTime();
                                            const separator = data.qr_url.includes('?') ? '&' : '?';
                                            qrImage.src = `${data.qr_url}${separator}t=${timestamp}`;
                                        }
                                    }
                                    
                                    // Mostrar información de validez del QR si está disponible la función
                                    setTimeout(() => {
                                        if (typeof window.showQrValidityInfo === 'function') {
                                            window.showQrValidityInfo(isExistingQr, data.updated_at);
                                        }
                                        
                                        // Actualizar todos los datos del QR en la UI
                                        if (data.qr_data) {
                                            document.getElementById('qrTokenData').textContent = data.qr_data.token_qr || '';
                                            document.getElementById('qrNombreData').textContent = data.qr_data.nombre || '';
                                            document.getElementById('qrAulaData').textContent = data.qr_data.aula || 'No especificado';
                                            document.getElementById('qrCursoData').textContent = data.qr_data.curso || 'No especificado';
                                            document.getElementById('qrHorarioData').textContent = data.qr_data.horario || '';
                                            
                                            const fechaGenEl = document.getElementById('qrFechaGenData');
                                            if (fechaGenEl && data.updated_at) {
                                                fechaGenEl.textContent = new Date(data.updated_at).toLocaleString();
                                            }
                                        }
                                    }, 100);
                                }
                                return data;
                            });
                        };
                        return response;
                    });
                }
                return fetchPromise;
            };
        }
        
        // Configurar botón de descarga del QR - método directo
        const downloadQrBtn = document.getElementById('downloadQr');
        if (downloadQrBtn) {
            downloadQrBtn.addEventListener('click', function(e) {
                // No prevenir el comportamiento predeterminado para permitir la descarga directa
                // Solo añadir lógica adicional si es necesario
                console.log('Descargando QR:', this.href);
            });
        }
    });
</script>
@endsection