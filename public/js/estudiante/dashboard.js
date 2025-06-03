/**
 * Dashboard Admin - UniScan
 * JavaScript para la funcionalidad del dashboard
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referencias a elementos DOM
    const sidebar = document.querySelector('.dashboard__sidebar');
    const content = document.querySelector('.dashboard__content');
    const sidebarToggle = document.querySelector('.sidebar__toggle');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

    // Crear un overlay para el sidebar en móvil
    const sidebarOverlay = document.createElement('div');
    sidebarOverlay.className = 'sidebar-overlay';
    document.body.appendChild(sidebarOverlay);
    // Función para mostrar/ocultar el overlay
    function toggleSidebarOverlay() {
        if (window.innerWidth <= 991 && sidebar.classList.contains('dashboard__sidebar--active')) {
            sidebarOverlay.classList.add('sidebar-overlay--active');
        } else {
            sidebarOverlay.classList.remove('sidebar-overlay--active');
        }
    }
    // Toggle sidebar en desktop
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            // Comportamiento diferente según el tamaño de pantalla
            if (window.innerWidth <= 991) {
                sidebar.classList.toggle('dashboard__sidebar--active');
                toggleSidebarOverlay();
            } else {
                sidebar.classList.toggle('dashboard__sidebar--collapsed');
                content.classList.toggle('dashboard__content--expanded');
            }
        });
    }
    // Toggle sidebar en mobile
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation(); // Evitar que el clic se propague al documento
            sidebar.classList.toggle('dashboard__sidebar--active');
            toggleSidebarOverlay();

            console.log('Mobile menu clicked, sidebar classes:', sidebar.className);
        });
    }

    // Cerrar sidebar al hacer clic en el overlay
    sidebarOverlay.addEventListener('click', function () {
        sidebar.classList.remove('dashboard__sidebar--active');
        sidebarOverlay.classList.remove('sidebar-overlay--active');
    });
    // Cerrar sidebar en mobile cuando se hace clic fuera
    document.addEventListener('click', function (event) {
        const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
        const isClickOnMobileMenu = mobileMenuBtn && mobileMenuBtn.contains(event.target);
        const isClickOnOverlay = event.target === sidebarOverlay;
        const isSidebarActive = sidebar && sidebar.classList.contains('dashboard__sidebar--active');

        if (!isClickInsideSidebar && !isClickOnMobileMenu && !isClickOnOverlay && isSidebarActive && window.innerWidth < 992) {
            sidebar.classList.remove('dashboard__sidebar--active');
            sidebarOverlay.classList.remove('sidebar-overlay--active');
        }
    });      // Manejo de redimensión de ventana
    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
            sidebar.classList.remove('dashboard__sidebar--active');
            sidebarOverlay.classList.remove('sidebar-overlay--active');

            // Restaurar estado del sidebar basado en preferencia de usuario
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('dashboard__sidebar--collapsed');
                content.classList.add('dashboard__content--expanded');
            } else {
                sidebar.classList.remove('dashboard__sidebar--collapsed');
                content.classList.remove('dashboard__content--expanded');
            }
        }
    });

    // Marcar enlace activo según la URL actual
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav__link');

    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (linkPath && currentPath.includes(linkPath) && linkPath !== '/') {
            link.classList.add('nav__link--active');
        }
    });

    // Inicialización de gráficos si existen
    initCharts();

    // Configurar actualización automática de datos cada 5 minutos
    setInterval(refreshChartData, 300000); // 300000 ms = 5 minutos
});

/**
 * Inicializa los gráficos utilizando Chart.js
 * Para que esta función funcione, es necesario incluir Chart.js en el proyecto
 */
function initCharts() {
    // Verificar si Chart.js está disponible
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js no está cargado. Los gráficos no se inicializarán.');
        return;
    }

    // Gráfico de actividad semanal
    const weeklyActivityChart = document.getElementById('weeklyActivityChart');
    if (weeklyActivityChart) {
        // Obtener datos del elemento para el gráfico semanal
        const datosGraficoSemanal = weeklyActivityChart.dataset.chartData ?
            JSON.parse(weeklyActivityChart.dataset.chartData) :
            {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [
                    {
                        label: 'Presentes',
                        data: [0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: 'rgba(76, 175, 80, 0.2)',
                        borderColor: '#4caf50',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Ausentes',
                        data: [0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: 'rgba(244, 67, 54, 0.2)',
                        borderColor: '#f44336',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Justificados',
                        data: [0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: 'rgba(33, 150, 243, 0.2)',
                        borderColor: '#2196f3',
                        tension: 0.3,
                        fill: true
                    }
                ]
            };

        new Chart(weeklyActivityChart, {
            type: 'line',
            data: {
                labels: datosGraficoSemanal.labels,
                datasets: datosGraficoSemanal.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Gráfico de distribución de asistencias
    const attendanceDistChart = document.getElementById('attendanceDistChart');
    if (attendanceDistChart) {
        // Obtener datos del elemento para el gráfico de distribución
        const datosGraficoDistribucion = attendanceDistChart.dataset.chartData ?
            JSON.parse(attendanceDistChart.dataset.chartData) :
            {
                labels: ['A tiempo', 'Ausentes', 'Tardanzas', 'Justificados'],
                datos: [0, 0, 0, 0],
                colores: ['#4caf50', '#f44336', '#ff9800', '#2196f3']
            };

        new Chart(attendanceDistChart, {
            type: 'pie',
            data: {
                labels: datosGraficoDistribucion.labels,
                datasets: [{
                    data: datosGraficoDistribucion.datos,
                    backgroundColor: datosGraficoDistribucion.colores
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

/**
 * Función para mostrar notificaciones toast
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de notificación: success, error, warning, info
 */
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification--${type} animate-fade-in`;
    notification.innerHTML = `
        <div class="notification__content">
            <i class="notification__icon fas ${getIconForType(type)}"></i>
            <span class="notification__message">${message}</span>
        </div>
        <button class="notification__close">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Añadir al DOM
    const container = document.querySelector('.notifications-container') || document.body;
    container.appendChild(notification);

    // Configurar cierre automático
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);

    // Evento para cerrar manualmente
    const closeBtn = notification.querySelector('.notification__close');
    closeBtn.addEventListener('click', () => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
}

/**
 * Obtener icono para el tipo de notificación
 * @param {string} type - Tipo de notificación
 * @returns {string} - Clase del icono
 */
function getIconForType(type) {
    switch (type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        case 'info': return 'fa-info-circle';
        default: return 'fa-info-circle';
    }
}

/**
 * Actualiza los datos de los gráficos mediante peticiones AJAX
 */
function refreshChartData() {
    // Actualizar gráfico de actividad semanal
    fetch('/admin/dashboard/datos-semanal')
        .then(response => response.json())
        .then(data => {
            const weeklyChart = Chart.getChart('weeklyActivityChart');
            if (weeklyChart) {
                weeklyChart.data.labels = data.labels;
                weeklyChart.data.datasets = data.datasets;
                weeklyChart.update();
                showNotification('Datos de actividad semanal actualizados', 'info');
            }
        })
        .catch(error => {
            console.error('Error al actualizar datos semanales:', error);
        });

    // Actualizar gráfico de distribución de asistencias
    fetch('/admin/dashboard/datos-distribucion')
        .then(response => response.json())
        .then(data => {
            const distChart = Chart.getChart('attendanceDistChart');
            if (distChart) {
                distChart.data.labels = data.labels;
                distChart.data.datasets[0].data = data.datos;
                distChart.data.datasets[0].backgroundColor = data.colores;
                distChart.update();
                showNotification('Datos de distribución de asistencias actualizados', 'info');
            }
        })
        .catch(error => {
            console.error('Error al actualizar datos de distribución:', error);
        });
}
