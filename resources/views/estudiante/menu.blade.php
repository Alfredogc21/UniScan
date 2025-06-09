@extends('layouts.estudiante')

@section('title', 'Guía de Usuario - UniScan')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/estudiante/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/menu.css') }}"> <!-- Reutilizamos los estilos del menú de ayuda -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@endsection

@section('page-title', 'Guía del Estudiante')

@section('content')
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
            <p>Bienvenido al panel de estudiante de UniScan. Esta guía te ayudará a entender cómo utilizar todas las funcionalidades disponibles para gestionar tus asistencias, revisar tus materias y escanear códigos QR para registrar tu presencia en clases.</p>
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
                <p>El Dashboard proporciona una vista general de tus estadísticas académicas y asistencias.</p>
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="feature-text">
                            <strong>Tarjetas de resumen:</strong> Visualiza rápidamente información sobre tus materias matriculadas, asistencias del día, y porcentaje de asistencia general.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                        <div class="feature-text">
                            <strong>Gráficos interactivos:</strong> Analiza tus tendencias de asistencia y estadísticas semanales.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-table"></i></div>
                        <div class="feature-text">
                            <strong>Tabla de asistencias recientes:</strong> Consulta tus últimas asistencias registradas.
                        </div>
                    </div>
                </div>
            </div>
            <div class="help-image">
                <img src="{{ asset('img/imagenDashboardEstudiante.png') }}" alt="Dashboard Estudiante">
            </div>
        </div>
    </div>

    <!-- Sección Mis Materias -->
    <div class="help-card" id="materias-section">
        <div class="help-card__header">
            <div class="help-card__icon">
                <i class="fas fa-book"></i>
            </div>
            <h3 class="help-card__title">Mis Materias</h3>
            <div class="help-card__toggle">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
        <div class="help-card__content">
            <div class="help-card__description">
                <p>En esta sección puedes visualizar todas las materias en las que estás matriculado.</p>
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-list"></i></div>
                        <div class="feature-text">
                            <strong>Listado de materias:</strong> Visualiza todas tus materias con información detallada.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-percentage"></i></div>
                        <div class="feature-text">
                            <strong>Porcentaje de asistencia:</strong> Revisa tu porcentaje de asistencia por materia.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-calendar"></i></div>
                        <div class="feature-text">
                            <strong>Horarios de clase:</strong> Consulta los horarios de entrada y salida de cada materia.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Asistencia QR -->
    <div class="help-card" id="asistencia-qr-section">
        <div class="help-card__header">
            <div class="help-card__icon">
                <i class="fas fa-qrcode"></i>
            </div>
            <h3 class="help-card__title">Asistencia QR</h3>
            <div class="help-card__toggle">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
        <div class="help-card__content">
            <div class="help-card__description">
                <p>Registra tu asistencia a clases mediante el escaneo de códigos QR.</p>
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-camera"></i></div>
                        <div class="feature-text">
                            <strong>Escáner de códigos QR:</strong> Utiliza la cámara de tu dispositivo para escanear el código QR de la clase.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                        <div class="feature-text">
                            <strong>Cambio de cámara:</strong> Alterna entre cámara frontal y trasera si tu dispositivo lo permite.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-clipboard-check"></i></div>
                        <div class="feature-text">
                            <strong>Confirmación de asistencia:</strong> Recibe confirmación inmediata de tu asistencia registrada.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Mis Asistencias -->
    <div class="help-card" id="asistencias-section">
        <div class="help-card__header">
            <div class="help-card__icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <h3 class="help-card__title">Mis Asistencias</h3>
            <div class="help-card__toggle">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
        <div class="help-card__content">
            <div class="help-card__description">
                <p>Revisa y filtra tu historial completo de asistencias a todas tus materias.</p>
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-search"></i></div>
                        <div class="feature-text">
                            <strong>Filtrar asistencias:</strong> Busca registros por fecha, materia o estado de asistencia.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                        <div class="feature-text">
                            <strong>Estadísticas de asistencia:</strong> Visualiza el total de presencias, ausencias y porcentaje general.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-history"></i></div>
                        <div class="feature-text">
                            <strong>Historial completo:</strong> Accede al registro histórico de todas tus asistencias.
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
                <p>Gestiona tu información personal y credenciales de acceso.</p>
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-user-edit"></i></div>
                        <div class="feature-text">
                            <strong>Editar información:</strong> Actualiza tus datos personales como nombre y correo electrónico.
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-key"></i></div>
                        <div class="feature-text">
                            <strong>Cambiar contraseña:</strong> Modifica tu contraseña para mantener segura tu cuenta.
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
                        <p>Cambia tu contraseña regularmente y nunca compartas tus credenciales de acceso.</p>
                    </div>
                </div>
                <div class="tip-card">
                    <div class="tip-icon"><i class="fas fa-qrcode"></i></div>
                    <div class="tip-content">
                        <h4>Escáner QR</h4>
                        <p>Asegúrate de permitir el acceso a la cámara cuando utilices el escáner de códigos QR.</p>
                    </div>
                </div>
                <div class="tip-card">
                    <div class="tip-icon"><i class="fas fa-bell"></i></div>
                    <div class="tip-content">
                        <h4>Puntualidad</h4>
                        <p>Recuerda escanear el código QR dentro del periodo permitido para registrar correctamente tu asistencia.</p>
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
                        <span>¿Qué hago si no puedo escanear el código QR?</span>
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Si tienes problemas para escanear el código QR, verifica que has permitido el acceso a la cámara en tu navegador, asegúrate de tener buena iluminación y que el código esté claramente visible. Si el problema persiste, contacta a tu profesor para que registre tu asistencia manualmente.</p>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>¿Cómo justifico una inasistencia?</span>
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Para justificar una inasistencia, debes contactar directamente con tu profesor o con el administrador del sistema, presentando la documentación que respalde el motivo de tu ausencia (certificado médico, constancia, etc.).</p>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>¿Puedo ver mi porcentaje de asistencia por materia?</span>
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Sí, en la sección de "Mis Materias" podrás ver el porcentaje de asistencia para cada una de tus materias matriculadas, así como estadísticas detalladas sobre tu asistencia.</p>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>¿Qué significa el color del porcentaje de asistencia?</span>
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="accordion-content">
                        <p>El color indica el estado de tu asistencia: verde (bueno) si tienes 80% o más, amarillo (advertencia) si tienes entre 60% y 79%, y rojo (peligro) si es menor al 60%. Es importante mantener un porcentaje alto para aprobar el curso.</p>
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
                <h4>¿Necesitas más ayuda?</h4>
                <p>Si tienes preguntas adicionales o enfrentas problemas técnicos, ponte en contacto con soporte.</p>
                <a href="mailto:karenprada038@gmail.com" class="support-button">Contactar Soporte</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script src="{{ asset('js/estudiante/dashboard.js') }}"></script>
<script src="{{ asset('js/admin/menu.js') }}"></script> <!-- Reutilizamos el js del menú de ayuda -->
@endsection