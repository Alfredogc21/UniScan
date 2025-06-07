@extends('layouts.admin')

@section('title', 'Guía de Usuario - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/menu.css') }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <li class="nav__item">
                    <a href="{{ route('admin.asistencias') }}" class="nav__link">
                        <span class="nav__link-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="nav__link-text">Asistencias</span>
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('admin.menu') }}" class="nav__link nav__link--active">
                        <span class="nav__link-icon"><i class="fas fa-question-circle"></i></span>
                        <span class="nav__link-text">Ayuda</span>
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
    <main class="dashboard__content">        <header class="content__header">
            <button class="actions__button mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="header__title">Guía del Administrador</h1>

            <div class="header__actions">
                @include('partials.logout_button')
            </div>
        </header>

        <div class="content__main">
            <!-- Contenido principal de la página -->
            <div class="help-container">
                <!-- Sección de Introducción -->
                <div class="help-section">
                    <div class="help-section__header">
                        <div class="help-section__icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h2 class="help-section__title">Introducción</h2>
                    </div>
                    <div class="help-section__content">
                        <p>Bienvenido al panel de administración de UniScan. Esta guía le ayudará a entender cómo utilizar todas las funcionalidades disponibles para gestionar usuarios, materias y asistencias de manera eficiente.</p>
                    </div>
                </div>

                <!-- Sección Dashboard -->
                <div class="help-card" id="dashboard-section">
                    <div class="help-card__header">
                        <div class="help-card__icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="help-card__title">Dashboard</h3>
                        <div class="help-card__toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="help-card__content">                        <div class="help-card__description">
                            <p>El Dashboard proporciona una vista general del sistema con estadísticas en tiempo real.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                                    <div class="feature-text">
                                        <strong>Tarjetas de resumen:</strong> Visualice rápidamente información sobre usuarios, asistencias y materias activas.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                                    <div class="feature-text">
                                        <strong>Gráficos interactivos:</strong> Analice tendencias de asistencia y estadísticas semanales.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-table"></i></div>
                                    <div class="feature-text">
                                        <strong>Tabla de asistencias recientes:</strong> Consulte las últimas asistencias registradas en el sistema.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="help-image">
                            <img src="{{ asset('img/imagenDashboardAdmin.png') }}" alt="Dashboard">
                        </div>
                    </div>
                </div>

                <!-- Sección Usuarios -->
                <div class="help-card" id="users-section">
                    <div class="help-card__header">
                        <div class="help-card__icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="help-card__title">Gestión de Usuarios</h3>
                        <div class="help-card__toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="help-card__content">
                        <div class="help-card__description">
                            <p>Administre todos los usuarios del sistema incluyendo estudiantes, profesores y administradores.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-user-plus"></i></div>
                                    <div class="feature-text">
                                        <strong>Añadir usuarios:</strong> Cree nuevas cuentas asignando roles específicos.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-user-edit"></i></div>
                                    <div class="feature-text">
                                        <strong>Editar información:</strong> Actualice datos de contacto y configuraciones de los usuarios.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-user-slash"></i></div>
                                    <div class="feature-text">
                                        <strong>Desactivar cuentas:</strong> Gestione el acceso de usuarios al sistema.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección Materias -->
                <div class="help-card" id="materias-section">
                    <div class="help-card__header">
                        <div class="help-card__icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="help-card__title">Gestión de Materias</h3>
                        <div class="help-card__toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="help-card__content">
                        <div class="help-card__description">
                            <p>Administre las materias académicas, asignaciones de profesores y generación de códigos QR.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-plus-circle"></i></div>
                                    <div class="feature-text">
                                        <strong>Crear materias:</strong> Configure nuevas materias con horarios y profesores.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-qrcode"></i></div>
                                    <div class="feature-text">
                                        <strong>Generación de QR:</strong> Cree códigos QR únicos para registro de asistencia.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-edit"></i></div>
                                    <div class="feature-text">
                                        <strong>Modificar materias:</strong> Actualice información o reasigne profesores a las materias.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección Asistencias -->
                <div class="help-card" id="asistencias-section">
                    <div class="help-card__header">
                        <div class="help-card__icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h3 class="help-card__title">Control de Asistencias</h3>
                        <div class="help-card__toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="help-card__content">
                        <div class="help-card__description">
                            <p>Supervise y gestione los registros de asistencia de todos los estudiantes en las diferentes materias.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-search"></i></div>
                                    <div class="feature-text">
                                        <strong>Filtrar asistencias:</strong> Busque registros por fecha, materia o estudiante.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-file-export"></i></div>
                                    <div class="feature-text">
                                        <strong>Exportar datos:</strong> Descargue reportes para análisis externos.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-pencil-alt"></i></div>
                                    <div class="feature-text">
                                        <strong>Actualizar estados:</strong> Modifique justificaciones o corrija registros erróneos.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mejores prácticas -->
                <div class="help-section">
                    <div class="help-section__header">
                        <div class="help-section__icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h2 class="help-section__title">Mejores Prácticas</h2>
                    </div>
                    <div class="help-section__content">
                        <div class="tips-container">
                            <div class="tip-card">
                                <div class="tip-icon"><i class="fas fa-shield-alt"></i></div>
                                <div class="tip-content">
                                    <h4>Seguridad</h4>
                                    <p>Cambie su contraseña regularmente y nunca comparta sus credenciales de administrador.</p>
                                </div>
                            </div>
                            <div class="tip-card">
                                <div class="tip-icon"><i class="fas fa-database"></i></div>
                                <div class="tip-content">
                                    <h4>Respaldos</h4>
                                    <p>Exporte periódicamente los datos importantes para prevenir pérdidas.</p>
                                </div>
                            </div>
                            <div class="tip-card">
                                <div class="tip-icon"><i class="fas fa-sync-alt"></i></div>
                                <div class="tip-content">
                                    <h4>Actualización</h4>
                                    <p>Mantenga la información de usuarios y materias actualizada para evitar inconsistencias.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Sección -->
                <div class="help-section">
                    <div class="help-section__header">
                        <div class="help-section__icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h2 class="help-section__title">Preguntas Frecuentes</h2>
                    </div>
                    <div class="help-section__content">
                        <div class="accordion">
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <span>¿Cómo puedo restablecer la contraseña de un usuario?</span>
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="accordion-content">
                                    <p>Para restablecer la contraseña, vaya a la sección de Usuarios, busque al usuario específico, haga clic en "Editar" y utilice la opción de cambio de contraseña.</p>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <span>¿Cómo generar un nuevo código QR para una materia?</span>
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="accordion-content">
                                    <p>Acceda a la sección de Materias, localice la materia deseada y haga clic en el botón de "Generar QR". El sistema creará un código único que puede ser descargado o imprimido.</p>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <span>¿Puedo modificar asistencias pasadas?</span>
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="accordion-content">
                                    <p>Sí. En la sección de Asistencias, puede filtrar por fecha y materia para encontrar registros antiguos y modificar su estado, como marcar una ausencia como justificada.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Soporte -->
                <div class="help-footer">
                    <div class="contact-support">
                        <div class="support-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="support-content">
                            <h4>¿Necesita más ayuda?</h4>
                            <p>Si tiene preguntas adicionales o enfrenta problemas técnicos, póngase en contacto con soporte.</p>
                            <a href="mailto:karenprada038@gmail.com" class="support-button">Contactar Soporte</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
<script src="{{ asset('js/admin/menu.js') }}"></script>
@endsection