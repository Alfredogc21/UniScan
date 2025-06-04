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
    const btnJustificarAsistencias = document.getElementById('btnJustificarAsistencias');
    
    // Función para obtener la base path
    function getBasePath() {
        return window.location.pathname.includes('/public') 
            ? '/Proyecto/UniScan/public' 
            : '';
    }
    
    // Función handler para cerrar modales
    function cerrarModalHandler(e) {
        e.preventDefault();
        e.stopPropagation();
        const modal = this.closest('.custom-modal');
        if (modal) {
            cerrarModal(modal.id);
        }
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
                abrirModalJustificar(asistenciaId);
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
            <div class="detalle-grid">                <div class="detalle-item detalle-highlight">
                    <span class="detalle-label">📋 Información de la Asistencia</span>
                    <div class="detalle-value">
                        <strong>ID:</strong> ${data.asistencia.id}<br>
                        <strong>Fecha:</strong> ${data.asistencia.fecha}<br>
                        <strong>Hora:</strong> ${data.asistencia.hora}<br>
                        <strong>Estado:</strong> <span style="color: ${data.asistencia.estado === 'Presente' ? '#27ae60' : data.asistencia.estado === 'Ausente' ? '#e74c3c' : '#f39c12'}; font-weight: bold;">${data.asistencia.estado}</span>
                        ${data.asistencia.justificacion ? `<br><strong>Justificación:</strong> <span style="color: #f39c12; font-style: italic;">${data.asistencia.justificacion}</span>` : ''}
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
    }    // Función para cerrar cualquier modal - ACTUALIZADA PARA NUEVA ESTRUCTURA
    function cerrarModal(modalId = 'modal-detalle-asistencia') {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Eliminar todas las clases y estilos
            modal.classList.remove('modal-show');
            modal.style.display = 'none';
            modal.style.zIndex = '';
            modal.removeAttribute('style');
            modal.className = 'custom-modal';
            
            // Si es el modal de justificación, limpiar el formulario
            if (modalId === 'modal-justificar-asistencia') {
                const formJustificar = document.getElementById('form-justificar');
                if (formJustificar) formJustificar.reset();
                
                // Reiniciar contador también
                const charCountSpan = document.getElementById('char-count');
                if (charCountSpan) {
                    charCountSpan.textContent = '255';
                    charCountSpan.className = 'char-count-normal';
                }
                
                // Limpiar campos específicos de la nueva estructura
                const justificacionTextarea = document.getElementById('justificacion');
                const materiaSelect = document.getElementById('materia-select');
                const estudianteSelect = document.getElementById('estudiante-select');
                const tipoAsistenciaSelect = document.getElementById('tipo-asistencia-select');
                
                if (justificacionTextarea) justificacionTextarea.value = '';
                if (materiaSelect) materiaSelect.value = '';
                if (estudianteSelect) estudianteSelect.innerHTML = '<option value="">Seleccione una materia primero</option>';
                if (tipoAsistenciaSelect) tipoAsistenciaSelect.value = '3'; // Justificado por defecto
            }
            
            // Restaurar scroll del body
            document.body.style.overflow = '';
        }
    }// Función para abrir el modal de justificación - ACTUALIZADA PARA NUEVA ESTRUCTURA
    function abrirModalJustificar(id) {
        const modal = document.getElementById('modal-justificar-asistencia');
        const justificacionTextarea = document.getElementById('justificacion');
        const charCountSpan = document.getElementById('char-count');
        
        // Obtener elementos del nuevo formulario
        const materiaSelect = document.getElementById('materia-select');
        const estudianteSelect = document.getElementById('estudiante-select');
        const tipoAsistenciaSelect = document.getElementById('tipo-asistencia-select');
        const fechaAsistencia = document.getElementById('fecha-asistencia');
        const horaAsistencia = document.getElementById('hora-asistencia');
        
        // Limpiar el formulario completamente
        if (justificacionTextarea) justificacionTextarea.value = '';
        if (estudianteSelect) estudianteSelect.innerHTML = '<option value="">Seleccione una materia primero</option>';
        if (tipoAsistenciaSelect) tipoAsistenciaSelect.value = '3'; // Justificado por defecto
        
        // Establecer fecha y hora actuales
        const now = new Date();
        if (fechaAsistencia) fechaAsistencia.value = now.toISOString().split('T')[0];
        if (horaAsistencia) horaAsistencia.value = now.toTimeString().slice(0, 5);
        
        // Reiniciar contador de caracteres
        if (charCountSpan && justificacionTextarea) {
            const maxLength = parseInt(justificacionTextarea.getAttribute('maxlength')) || 255;
            charCountSpan.textContent = maxLength;
            charCountSpan.className = 'char-count-normal';
        }
        
        // Cargar materias del profesor en el select
        cargarMateriasProfesor();
        
        if (id) {
            // Si hay un ID específico, cargar los datos de esa asistencia
            cargarDatosAsistenciaEspecifica(id);
        }
        
        // Mostrar el modal con z-index alto
        modal.className = 'custom-modal modal-show';
        modal.style.display = 'flex';
        modal.style.zIndex = '9999';
        
        // Asegurar que el body no tenga scroll
        document.body.style.overflow = 'hidden';
          // Agregar event listener para cambio de materia
        if (materiaSelect) {
            // Remover cualquier listener previo para evitar duplicados
            materiaSelect.removeEventListener('change', materiaSelectHandler);
            materiaSelect.addEventListener('change', materiaSelectHandler);
        }        // Asignar eventos a los botones de cerrar en este modal específico
        const closeButtons = modal.querySelectorAll('.modal-close-btn, [data-dismiss="modal"]');
        closeButtons.forEach(button => {
            // Remover los event listeners existentes para evitar duplicados
            button.removeEventListener('click', cerrarModalHandler);
            // Añadir el nuevo event listener
            button.addEventListener('click', cerrarModalHandler);
        });
        
        // Focus en el primer campo editable después de un pequeño delay
        setTimeout(() => {
            if (materiaSelect) {
                materiaSelect.focus();
            }
        }, 100);
    }
    
    // Handler para el cambio de materia (definido fuera para poder removerlo)
    function materiaSelectHandler(e) {
        e.stopPropagation();
        const materiaId = this.value;
        const estudianteSelect = document.getElementById('estudiante-select');
        
        if (materiaId) {
            if (estudianteSelect) {
                estudianteSelect.disabled = false;
            }
            cargarEstudiantesMateria(materiaId);
        } else {
            if (estudianteSelect) {
                estudianteSelect.innerHTML = '<option value="">Seleccione una materia primero</option>';
                estudianteSelect.disabled = true;
            }
        }
    }// Función para cargar materias del profesor
    function cargarMateriasProfesor() {
        const materiaSelect = document.getElementById('materia-select');
        if (!materiaSelect) return;
        
        // Mostrar loading
        materiaSelect.innerHTML = '<option value="">Cargando materias...</option>';
        
        fetch(`${getBasePath()}/profesor/materias-ajax`)
            .then(response => response.json())
            .then(data => {
                materiaSelect.innerHTML = '<option value="">Seleccione una materia</option>';
                  if (data.success && data.materias) {
                    data.materias.forEach(materia => {
                        const option = document.createElement('option');
                        option.value = materia.id;
                        
                        // Construir el texto de la opción
                        let texto = materia.nombre;
                        if (materia.aula && materia.aula.nombre) {
                            texto += ` - Aula ${materia.aula.nombre}`;
                        }
                        if (materia.curso && materia.curso.nombre) {
                            texto += ` - Curso ${materia.curso.nombre}`;
                        }
                        
                        option.textContent = texto;
                        materiaSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error cargando materias:', error);
                materiaSelect.innerHTML = '<option value="">Error al cargar materias</option>';
            });
    }
    
    // Función para cargar estudiantes de una materia
    function cargarEstudiantesMateria(materiaId) {
        const estudianteSelect = document.getElementById('estudiante-select');
        if (!estudianteSelect || !materiaId) return;
        
        // Mostrar loading
        estudianteSelect.innerHTML = '<option value="">Cargando estudiantes...</option>';
        
        fetch(`${getBasePath()}/profesor/materias/${materiaId}/estudiantes`)
            .then(response => response.json())
            .then(data => {
                estudianteSelect.innerHTML = '<option value="">Seleccione un estudiante</option>';
                
                if (data.success && data.estudiantes) {
                    data.estudiantes.forEach(estudiante => {
                        const option = document.createElement('option');
                        option.value = estudiante.id;
                        option.textContent = estudiante.name;
                        estudianteSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error cargando estudiantes:', error);
                estudianteSelect.innerHTML = '<option value="">Error al cargar estudiantes</option>';
            });
    }
    
    // Función para cargar datos de una asistencia específica
    function cargarDatosAsistenciaEspecifica(asistenciaId) {
        fetch(`${getBasePath()}/profesor/asistencias/${asistenciaId}/detalle`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.asistencia) {
                    const asistencia = data.asistencia;
                    
                    // Pre-seleccionar materia
                    const materiaSelect = document.getElementById('materia-select');
                    if (materiaSelect && asistencia.materia_id) {
                        materiaSelect.value = asistencia.materia_id;
                        // Cargar estudiantes de esa materia
                        cargarEstudiantesMateria(asistencia.materia_id);
                        
                        // Pre-seleccionar estudiante después de cargar la lista
                        setTimeout(() => {
                            const estudianteSelect = document.getElementById('estudiante-select');
                            if (estudianteSelect && asistencia.alumno_id) {
                                estudianteSelect.value = asistencia.alumno_id;
                            }
                        }, 500);
                    }
                    
                    // Pre-llenar otros campos
                    const fechaAsistencia = document.getElementById('fecha-asistencia');
                    const horaAsistencia = document.getElementById('hora-asistencia');
                    
                    if (fechaAsistencia && asistencia.fecha_hora) {
                        const fecha = new Date(asistencia.fecha_hora);
                        fechaAsistencia.value = fecha.toISOString().split('T')[0];
                        
                        if (horaAsistencia) {
                            horaAsistencia.value = fecha.toTimeString().slice(0, 5);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error cargando datos de asistencia:', error);
            });
    }

    // Función para cargar información de una asistencia específica
    function cargarInformacionAsistencia(id) {
        const baseUrl = `${getBasePath()}/profesor/asistencias/${id}/info`;
        
        fetch(baseUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            // Actualizar la información del alumno
            const alumnoInfo = document.getElementById('alumno-info');
            const estadoBadge = document.getElementById('estado-asistencia');
            
            if (data.alumno && data.asistencia) {
                alumnoInfo.textContent = `${data.alumno.nombre} - ${data.materia.nombre} (${data.asistencia.fecha})`;
                
                // Configurar el badge de estado
                estadoBadge.textContent = data.asistencia.estado;
                if (data.asistencia.estado === 'Presente') {
                    estadoBadge.className = 'badge bg-success';
                } else if (data.asistencia.estado === 'Ausente') {
                    estadoBadge.className = 'badge bg-danger';
                } else if (data.asistencia.estado === 'Justificado') {
                    estadoBadge.className = 'badge bg-warning';
                }
            } else {
                alumnoInfo.textContent = 'No se pudo cargar la información del alumno';
                estadoBadge.textContent = '';
                estadoBadge.className = 'badge';
            }
        })
        .catch(error => {
            console.error('Error al cargar la información:', error);
            document.getElementById('alumno-info').textContent = 'Error al cargar la información';
        });    }

    // Función para crear asistencia justificada - NUEVA FUNCIONALIDAD
    function crearAsistenciaJustificada(datos) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const baseUrl = `${getBasePath()}/profesor/asistencias/crear-justificada`;
        
        fetch(baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(datos),
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
                showNotification('Asistencia justificada registrada correctamente.');
                aplicarFiltros();
                cerrarModal('modal-justificar-asistencia');
            } else {
                showNotification(data.error || 'Error al registrar la asistencia justificada.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al procesar la solicitud de justificación.', 'error');
        });
    }

    // Función para justificar una asistencia - SIMPLIFICADA PARA BD
    function justificarAsistencia(id, justificacion) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const baseUrl = `${getBasePath()}/profesor/asistencias/${id}/justificar`;
        
        // Hacer la petición solo con el campo justificacion
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
                cerrarModal('modal-justificar-asistencia');
            } else {
                showNotification(data.error || 'Error al justificar la asistencia.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al procesar la solicitud de justificación.', 'error');
        });
    }// Event listeners para cerrar los modales - COMPLETAMENTE CORREGIDO
    document.addEventListener('click', function(e) {
        // Prevenir que los clics dentro del modal-dialog cierren el modal
        if (e.target.closest('.modal-dialog')) {
            return;
        }
        
        // Cerrar con botón X
        if (e.target.classList.contains('modal-close-btn') || 
            e.target.closest('.modal-close-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const modal = e.target.closest('.custom-modal');
            if (modal) {
                cerrarModal(modal.id);
            }
            return;
        }
        
        // Cerrar al hacer clic SOLO en el backdrop (fuera del contenido del modal)
        if (e.target.classList.contains('custom-modal') && e.target.classList.contains('modal-show')) {
            e.preventDefault();
            e.stopPropagation();
            cerrarModal(e.target.id);
        }
    }, true); // Usar captura para evitar conflictos
    
    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modales = document.querySelectorAll('.custom-modal.modal-show');
            modales.forEach(modal => {
                cerrarModal(modal.id);
            });
        }
    });    // Eventos para cerrar el modal con los botones X y Cancelar
    document.querySelectorAll('.modal-close-btn, [data-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const modal = this.closest('.custom-modal');
            if (modal) {
                cerrarModal(modal.id);
            }
        });
    });
    
    // Botón para guardar justificación - ACTUALIZADO PARA NUEVA ESTRUCTURA
    document.getElementById('btn-guardar-justificacion').addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Obtener valores de los nuevos campos
        const materiaId = document.getElementById('materia-select').value;
        const estudianteId = document.getElementById('estudiante-select').value;
        const tipoAsistencia = document.getElementById('tipo-asistencia-select').value;
        const fechaAsistencia = document.getElementById('fecha-asistencia').value;
        const horaAsistencia = document.getElementById('hora-asistencia').value;
        const justificacion = document.getElementById('justificacion').value;
        
        // Validar campos obligatorios
        if (!materiaId) {
            showNotification('Debe seleccionar una materia', 'error');
            return;
        }
        
        if (!estudianteId) {
            showNotification('Debe seleccionar un estudiante', 'error');
            return;
        }
        
        if (!fechaAsistencia) {
            showNotification('Debe seleccionar una fecha', 'error');
            return;
        }
        
        if (!horaAsistencia) {
            showNotification('Debe seleccionar una hora', 'error');
            return;
        }
        
        if (!justificacion.trim()) {
            showNotification('Debe ingresar un motivo de justificación', 'error');
            return;
        }
        
        // Validar longitud máxima (255 caracteres según BD)
        if (justificacion.length > 255) {
            showNotification('El motivo no puede exceder 255 caracteres', 'error');
            return;
        }
        
        // Crear o actualizar asistencia justificada
        crearAsistenciaJustificada({
            materia_id: materiaId,
            alumno_id: estudianteId,
            tipo_asistencia_id: tipoAsistencia,
            fecha: fechaAsistencia,
            hora: horaAsistencia,
            justificacion: justificacion
        });
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
      // Evento para el botón de justificar asistencias
    if (btnJustificarAsistencias) {
        btnJustificarAsistencias.addEventListener('click', function() {
            // Abrir modal de justificar asistencias sin ID específico
            abrirModalJustificar(null);
        });    }

    // Manejar la selección de archivos y mostrar nombre del archivo - COMPLETAMENTE CORREGIDO
    const documentoInput = document.getElementById('documento-justificacion');
    const fileNameDisplay = document.querySelector('.file-name');
    const clearFileBtn = document.querySelector('.btn-clear-file');
    
    if (documentoInput && fileNameDisplay && clearFileBtn) {
        documentoInput.addEventListener('change', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
                clearFileBtn.style.display = 'inline-block';
                
                // Verificar tamaño del archivo (max 2MB = 2 * 1024 * 1024 bytes)
                if (this.files[0].size > 2 * 1024 * 1024) {
                    showNotification('El archivo excede el tamaño máximo permitido (2MB)', 'error');
                    this.value = '';
                    fileNameDisplay.textContent = 'Ningún archivo seleccionado';
                    clearFileBtn.style.display = 'none';
                }
            } else {
                fileNameDisplay.textContent = 'Ningún archivo seleccionado';
                clearFileBtn.style.display = 'none';
            }
        });
        
        // Prevenir propagación en otros eventos
        documentoInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        documentoInput.addEventListener('focus', function(e) {
            e.stopPropagation();
        });
        
        clearFileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            documentoInput.value = '';
            fileNameDisplay.textContent = 'Ningún archivo seleccionado';
            this.style.display = 'none';
        });
    }
    
    // Adjuntar eventos iniciales a los botones de acción
    attachActionButtonListeners();
    
    // Event listeners adicionales para prevenir problemas de interacción en el modal
    
    // Prevenir propagación en todos los campos del formulario de justificación
    const formFields = [
        '#justificacion',
        '#categoria-justificacion', 
        '#fecha-justificacion',
        '#profesor-justificacion'
    ];
    
    formFields.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
            element.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            element.addEventListener('focus', function(e) {
                e.stopPropagation();
            });
            
            element.addEventListener('change', function(e) {
                e.stopPropagation();
            });
            
            element.addEventListener('input', function(e) {
                e.stopPropagation();
            });
        }
    });
    
    // Prevenir propagación en todos los botones del modal
    const modalButtons = document.querySelectorAll('#modal-justificar-asistencia button, #modal-detalle-asistencia button');
    modalButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Prevenir propagación en el contenido del modal-body
    const modalBodies = document.querySelectorAll('.modal-body');
    modalBodies.forEach(body => {
        body.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Prevenir propagación en el modal-footer
    const modalFooters = document.querySelectorAll('.modal-footer');
    modalFooters.forEach(footer => {
        footer.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Prevenir propagación en el modal-header
    const modalHeaders = document.querySelectorAll('.modal-header');
    modalHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});
