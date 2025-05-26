/**
 * Dashboard Admin - UniScan
 * JavaScript para la funcionalidad del dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM
    const sidebar = document.querySelector('.dashboard__sidebar');
    const content = document.querySelector('.dashboard__content');
    const sidebarToggle = document.querySelector('.sidebar__toggle');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    
    // Toggle sidebar en desktop
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('dashboard__sidebar--collapsed');
            content.classList.toggle('dashboard__content--expanded');
            
            // Ocultar texto si el sidebar está colapsado
            const sidebarTexts = document.querySelectorAll('.sidebar__logo-text, .nav__link-text, .user-info__details');
            sidebarTexts.forEach(text => {
                text.style.display = sidebar.classList.contains('dashboard__sidebar--collapsed') ? 'none' : 'block';
            });
        });
    }
    
    // Toggle sidebar en mobile
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('dashboard__sidebar--active');
        });
    }
    
    // Cerrar sidebar en mobile cuando se hace clic fuera
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnMobileMenu = mobileMenuBtn && mobileMenuBtn.contains(event.target);
        const isSidebarActive = sidebar.classList.contains('dashboard__sidebar--active');
        
        if (!isClickInsideSidebar && !isClickOnMobileMenu && isSidebarActive && window.innerWidth < 768) {
            sidebar.classList.remove('dashboard__sidebar--active');
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
        new Chart(weeklyActivityChart, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Registros de Asistencia',
                    data: [65, 59, 80, 81, 56, 40, 30],
                    borderColor: '#7b1fa2',
                    backgroundColor: 'rgba(123, 31, 162, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
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
        new Chart(attendanceDistChart, {
            type: 'pie',
            data: {
                labels: ['A tiempo', 'Tarde', 'Ausentes', 'Justificados'],
                datasets: [{
                    data: [60, 15, 10, 15],
                    backgroundColor: [
                        '#4caf50',
                        '#ff9800',
                        '#f44336',
                        '#2196f3'
                    ]
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
