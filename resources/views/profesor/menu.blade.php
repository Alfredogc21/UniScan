@extends('layouts.profesor')

@section('title', 'Guía de Usuario - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/profesor/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/menu.css') }}"> <!-- Reutilizamos los estilos del menú de ayuda -->
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
                <li class="nav__item">
                    <a href="{{ route('profesor.menu') }}" class="nav__link nav__link--active">
                        <span class="nav__link-icon"><i class="fas fa-question-circle"></i></span>
                        <span class="nav__link-text">Ayuda</span>
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

            <h1 class="header__title">Guía del Profesor</h1>

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
                        <p>Bienvenido al panel de profesor de UniScan. Esta guía le ayudará a entender cómo utilizar todas las funcionalidades disponibles para gestionar sus materias, alumnos y asistencias de manera eficiente.</p>
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
                    <div class="help-card__content">
                        <div class="help-card__description">
                            <p>El Dashboard proporciona una vista general del sistema con estadísticas en tiempo real de sus materias y alumnos.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                                    <div class="feature-text">
                                        <strong>Tarjetas de resumen:</strong> Visualice rápidamente información sobre alumnos en sus materias, asistencias del día, materias activas y porcentaje de asistencia.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                                    <div class="feature-text">
                                        <strong>Gráficos interactivos:</strong> Analice tendencias de asistencia semanal y distribución de asistencias en sus materias.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-table"></i></div>
                                    <div class="feature-text">
                                        <strong>Tabla de asistencias recientes:</strong> Consulte las últimas asistencias registradas en sus clases.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="help-image">
                            <img src="{{ asset('img/imagenDashboardProfesor.png') }}" alt="Dashboard Profesor">
                        </div>
                    </div>
                </div>

                <!-- Sección Alumnos -->
                <div class="help-card" id="alumnos-section">
                    <div class="help-card__header">
                        <div class="help-card__icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="help-card__title">Gestión de Alumnos</h3>
                        <div class="help-card__toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="help-card__content">
                        <div class="help-card__description">
                            <p>Administre los alumnos asignados a sus materias y visualice su información detallada.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-search"></i></div>
                                    <div class="feature-text">
                                        <strong>Buscar alumnos:</strong> Encuentre rápidamente a un alumno por nombre o identificación.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-filter"></i></div>
                                    <div class="feature-text">
                                        <strong>Filtrar por materia:</strong> Visualice alumnos según la materia que imparte.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-history"></i></div>
                                    <div class="feature-text">
                                        <strong>Historial de asistencia:</strong> Revise el historial de asistencias de cada alumno.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección Mis Materias -->
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
                            <p>Administre sus materias asignadas, horarios y genere códigos QR para registrar asistencia.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-qrcode"></i></div>
                                    <div class="feature-text">
                                        <strong>Generación de QR:</strong> Cree códigos QR para cada clase que los alumnos escanearán para registrar su asistencia.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                                    <div class="feature-text">
                                        <strong>Gestión de horarios:</strong> Visualice y administre los horarios de sus materias.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                                    <div class="feature-text">
                                        <strong>Estadísticas por materia:</strong> Analice el rendimiento y asistencia en cada una de sus materias.
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
                            <p>Supervise y gestione los registros de asistencia de sus alumnos en las diferentes materias.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-search"></i></div>
                                    <div class="feature-text">
                                        <strong>Filtrar asistencias:</strong> Busque registros por fecha, materia o estudiante.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                                    <div class="feature-text">
                                        <strong>Justificar ausencias:</strong> Registre justificaciones para las faltas de los alumnos.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-history"></i></div>
                                    <div class="feature-text">
                                        <strong>Historial detallado:</strong> Acceda al registro histórico completo de asistencias.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección Mi Perfil -->
                <div class="help-card" id="profile-section">
                    <div class="help-card__header">
                        <div class="help-card__icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="help-card__title">Mi Perfil</h3>
                        <div class="help-card__toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="help-card__content">
                        <div class="help-card__description">
                            <p>Gestione su información personal y credenciales de acceso.</p>
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-user-edit"></i></div>
                                    <div class="feature-text">
                                        <strong>Editar información:</strong> Actualice sus datos personales como nombre y correo electrónico.
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-key"></i></div>
                                    <div class="feature-text">
                                        <strong>Cambiar contraseña:</strong> Modifique su contraseña para mantener segura su cuenta.
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
                                <div class="tip-icon"><i class="fas fa-qrcode"></i></div>
                                <div class="tip-content">
                                    <h4>Códigos QR</h4>
                                    <p>Genere un nuevo código QR para cada clase para asegurar que solo se registre la asistencia de alumnos presentes en ese momento.</p>
                                </div>
                            </div>
                            <div class="tip-card">
                                <div class="tip-icon"><i class="fas fa-clock"></i></div>
                                <div class="tip-content">
                                    <h4>Tiempo de Escaneo</h4>
                                    <p>Establezca un periodo limitado para escanear el código QR al inicio de cada clase para asegurar la puntualidad.</p>
                                </div>
                            </div>
                            <div class="tip-card">
                                <div class="tip-icon"><i class="fas fa-shield-alt"></i></div>
                                <div class="tip-content">
                                    <h4>Seguridad</h4>
                                    <p>Cambie su contraseña regularmente y no comparta su pantalla mostrando el código QR en plataformas virtuales.</p>
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
                                    <span>¿Cómo genero un código QR para mi clase?</span>
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="accordion-content">
                                    <p>Para generar un código QR, vaya a la sección de "Mis Materias", seleccione la materia deseada y haga clic en el botón de "Generar QR". El sistema creará un código único que puede mostrar a sus alumnos o descargar para imprimirlo.</p>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <span>¿Cómo justifico la inasistencia de un alumno?</span>
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="accordion-content">
                                    <p>Acceda a la sección de "Asistencias", busque el registro específico del alumno y haga clic en el botón de "Justificar". Ingrese el motivo de la justificación y guarde los cambios. La inasistencia quedará marcada como justificada en el sistema.</p>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <span>¿Puedo modificar asistencias pasadas?</span>
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="accordion-content">
                                    <p>Sí. En la sección de "Asistencias", puede filtrar por fecha y materia para encontrar registros antiguos y modificar su estado, como marcar una ausencia como justificada o corregir un registro erróneo.</p>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <span>¿Cómo puedo ver el historial de asistencia de un alumno específico?</span>
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="accordion-content">
                                    <p>En la sección de "Alumnos", busque al alumno deseado y haga clic en el botón de "Ver asistencias" o "Detalles". Se mostrará una vista detallada de todas sus asistencias, con opciones para filtrar por materia o fecha.</p>
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
<script src="{{ asset('js/profesor/dashboard.js') }}"></script>
<script src="{{ asset('js/admin/menu.js') }}"></script> <!-- Reutilizamos el js del menú de ayuda -->
@endsection
