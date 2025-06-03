document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const aplicarFiltrosBtn = document.getElementById('aplicar-filtros');
    const materiaFilter = document.getElementById('materia-filter');
    const estadoFilter = document.getElementById('estado-filter');
    const fechaDesdeInput = document.getElementById('fecha-desde');
    const fechaHastaInput = document.getElementById('fecha-hasta');
    const tablaAsistencias = document.querySelector('.data-table__body');
    const notificationsContainer = document.querySelector('.notifications-container');
    const searchInput = document.querySelector('.search__input');
    
    // Función para obtener la base path
    function getBasePath() {
        return window.location.pathname.includes('/public') 
            ? '/Proyecto/UniScan/public' 
            : '';
    }
    
    // Función para mostrar notificaciones
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.innerHTML = `
            <div class="notification__content">
                <i class="notification__icon fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <p class="notification__message">${message}</p>
            </div>
            <button class="notification__close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        notificationsContainer.appendChild(notification);
        
        // Agregar evento para cerrar la notificación
        const closeBtn = notification.querySelector('.notification__close');
        closeBtn.addEventListener('click', function() {
            notification.remove();
        });
        
        // Eliminar automáticamente después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Función para aplicar los filtros y actualizar la tabla
    function aplicarFiltros() {
        const materia_id = materiaFilter.value;
        const estado_id = estadoFilter.value;
        const fecha_desde = fechaDesdeInput.value;
        const fecha_hasta = fechaHastaInput.value;
        
        // Validación de fechas
        if (fecha_desde && fecha_hasta && new Date(fecha_desde) > new Date(fecha_hasta)) {
            showNotification('La fecha inicial no puede ser mayor que la fecha final', 'error');
            return;
        }
        
        // Mostrar indicador de carga
        tablaAsistencias.innerHTML = '<tr><td colspan="7" class="data-table__cell text-center">Cargando...</td></tr>';
        
        // Preparar parámetros para la URL
        const params = new URLSearchParams();
        if (materia_id) params.append('materia_id', materia_id);
        if (estado_id) params.append('estado_id', estado_id);
        if (fecha_desde) params.append('fecha_desde', fecha_desde);
        if (fecha_hasta) params.append('fecha_hasta', fecha_hasta);
        
        // Construir URL
        const baseUrl = `${getBasePath()}/profesor/asistencias/filtrar`;
        
        // Llamada AJAX
        fetch(`${baseUrl}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        }).then(response => {
            console.log('Respuesta del servidor:', response.status, response.statusText);
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Respuesta de error:', text);
                    throw new Error(`Error en la respuesta del servidor: ${response.status} ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            if (!data.html) {
                throw new Error('Respuesta incompleta: falta el campo html');
            }
            
            // Actualizar la tabla con los resultados
            tablaAsistencias.innerHTML = data.html;
            
            // Agregar eventos a los botones de acción
            attachActionButtonListeners();
        })
        .catch(error => {
            console.error('Error detallado:', error);
            tablaAsistencias.innerHTML = '<tr><td colspan="7" class="data-table__cell text-center">Error al cargar datos. Inténtelo de nuevo.</td></tr>';
            showNotification('Error al cargar los datos. Por favor, inténtelo de nuevo.', 'error');
        });
    }
    
    // Función para adjuntar eventos a los botones de acción
    function attachActionButtonListeners() {
        // Botones para ver detalles
        document.querySelectorAll('.btn-ver-asistencia').forEach(btn => {
            btn.addEventListener('click', function() {
                const asistenciaId = this.getAttribute('data-id');
                mostrarDetalleAsistencia(asistenciaId);
            });
        });
        
        // Botones para justificar asistencia
        document.querySelectorAll('.btn-justificar-asistencia').forEach(btn => {
            btn.addEventListener('click', function() {
                const asistenciaId = this.getAttribute('data-id');
                const justificacion = prompt('Ingrese la justificación:');
                if (justificacion) {
                    justificarAsistencia(asistenciaId, justificacion);
                }
            });
        });
    }
      // Función SIMPLE para mostrar detalles de asistencia - SIN OPACIDAD
    function mostrarDetalleAsistencia(asistenciaId) {
        console.log('Mostrando detalles para asistencia ID:', asistenciaId);
        
        const modal = document.getElementById('modal-detalle-asistencia');
        const modalContent = document.getElementById('modal-detalle-content');
        
        if (!modal || !modalContent) {
            console.error('Elementos del modal no encontrados');
            return;
        }
        
        // Limpiar estilos previos
        modal.removeAttribute('style');
        modal.className = 'custom-modal';
        
        // Mostrar spinner
        modalContent.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando detalles...</p>
            </div>
        `;
        
        // Mostrar modal simplemente agregando clase
        modal.classList.add('modal-show');
        
        // Realizar petición
        fetch(`${getBasePath()}/profesor/asistencias/${asistenciaId}/detalle`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                modalContent.innerHTML = generarHtmlDetalles(data);
            })
            .catch(error => {
                console.error('Error:', error);
                modalContent.innerHTML = `
                    <div class="error-message">
                        <h5>Error al cargar los detalles</h5>
                        <p>${error.message}</p>
                    </div>
                `;
            });
    }
      // Función para generar HTML de detalles - MEJORADO
    function generarHtmlDetalles(data) {
        return `
            <div class="detalle-grid">
                <div class="detalle-item detalle-highlight">
                    <span class="detalle-label">📋 Información de la Asistencia</span>
                    <div class="detalle-value">
                        <strong>ID:</strong> ${data.asistencia.id}<br>
                        <strong>Fecha:</strong> ${data.asistencia.fecha}<br>
                        <strong>Hora:</strong> ${data.asistencia.hora}<br>
                        <strong>Estado:</strong> <span style="color: ${data.asistencia.estado === 'Presente' ? '#27ae60' : data.asistencia.estado === 'Ausente' ? '#e74c3c' : '#f39c12'}; font-weight: bold;">${data.asistencia.estado}</span>
                    </div>
                </div>
                
                <div class="detalle-item">
                    <span class="detalle-label">👤 Información del Alumno</span>
                    <div class="detalle-value">
                        <strong>Nombre:</strong> ${data.alumno.nombre}<br>
                        <strong>Email:</strong> ${data.alumno.email}
                    </div>
                </div>
                
                <div class="detalle-item">
                    <span class="detalle-label">📚 Información de la Materia</span>
                    <div class="detalle-value">
                        <strong>Materia:</strong> ${data.materia.nombre}<br>
                        <strong>Aula:</strong> ${data.materia.aula}<br>
                        <strong>Curso:</strong> ${data.materia.curso}
                    </div>
                </div>
                
                <div class="detalle-item detalle-highlight">
                    <span class="detalle-label">📊 Estadísticas del Alumno</span>
                    <div class="detalle-value">
                        <strong>Total de Asistencias:</strong> ${data.estadisticas.total_asistencias}<br>
                        <strong>Presentes:</strong> <span style="color: #27ae60; font-weight: bold;">${data.estadisticas.presentes}</span><br>
                        <strong>Ausentes:</strong> <span style="color: #e74c3c; font-weight: bold;">${data.estadisticas.ausentes}</span><br>
                        <strong>Justificadas:</strong> <span style="color: #f39c12; font-weight: bold;">${data.estadisticas.justificadas}</span><br>
                        <strong>Porcentaje de Asistencia:</strong> <span style="color: ${data.estadisticas.porcentaje_asistencia >= 80 ? '#27ae60' : data.estadisticas.porcentaje_asistencia >= 60 ? '#f39c12' : '#e74c3c'}; font-weight: bold; font-size: 1.1rem;">${data.estadisticas.porcentaje_asistencia}%</span>
                    </div>
                </div>
            </div>
            
            ${data.asistencias_recientes && data.asistencias_recientes.length > 0 ? `
                <div class="detalle-item" style="margin-top: 1.5rem;">
                    <span class="detalle-label">🕒 Asistencias Recientes</span>
                    <div class="detalle-value">
                        <table class="detalle-table">
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.asistencias_recientes.map(asistencia => `
                                    <tr>
                                        <td>${asistencia.fecha}</td>
                                        <td><span style="color: ${asistencia.estado === 'Presente' ? '#27ae60' : asistencia.estado === 'Ausente' ? '#e74c3c' : '#f39c12'}; font-weight: bold;">${asistencia.estado}</span></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            ` : ''}
        `;
    }
      // Función para cerrar el modal - LIMPIA
    function cerrarModal() {
        const modal = document.getElementById('modal-detalle-asistencia');
        if (modal) {
            modal.classList.remove('modal-show');
            modal.removeAttribute('style');
            modal.className = 'custom-modal';
        }
    }
    
    // Función para justificar una asistencia
    function justificarAsistencia(id, justificacion) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const baseUrl = `${getBasePath()}/profesor/asistencias/${id}/justificar`;
        
        fetch(baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                justificacion: justificacion
            }),
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Asistencia justificada correctamente.');
                aplicarFiltros();
            } else {
                showNotification(data.error || 'Error al justificar la asistencia.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al procesar la solicitud de justificación.', 'error');
        });
    }    // Event listeners para cerrar el modal - CORREGIDO
    document.addEventListener('click', function(e) {
        // Cerrar con botón X
        if (e.target.classList.contains('modal-close-btn') || 
            e.target.closest('.modal-close-btn')) {
            cerrarModal();
            return;
        }
        
        // Cerrar al hacer clic SOLO en el backdrop (no en el contenido)
        if (e.target.classList.contains('custom-modal') && 
            e.target.id === 'modal-detalle-asistencia') {
            cerrarModal();
        }
    });    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal();
        }
    });
    
    // Búsqueda rápida
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.data-table__body tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Evento para aplicar filtros
    aplicarFiltrosBtn.addEventListener('click', aplicarFiltros);
    
    // Adjuntar eventos iniciales a los botones de acción
    attachActionButtonListeners();
});
