/**
 * Script para la visualización de detalles de materias para profesores
 */
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const viewMateriaModal = document.getElementById('viewMateriaModal');
    const viewButtons = document.querySelectorAll('.btn-view-materia');
    const generateQrFromModalBtn = document.getElementById('btnGenerateQrFromModal');
    
    // Configuración CSRF para peticiones AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Función para obtener la URL base del sitio
    function getBaseUrl() {
        const path = window.location.pathname;
        const segments = path.split('/');
        const profesorIndex = segments.findIndex(segment => segment === 'profesor');
        
        if (profesorIndex > -1) {
            const basePath = segments.slice(0, profesorIndex).join('/');
            return window.location.origin + basePath;
        }
        return window.location.origin;
    }
    
    // Botones para ver detalles
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const materiaId = this.getAttribute('data-id');
            openViewModal(materiaId);
        });
    });
    
    // Función para abrir el modal de visualización y cargar datos
    function openViewModal(materiaId) {
        console.log('Abriendo modal para materia ID:', materiaId);
        
        // Mostrar el modal inmediatamente para evitar percepción de retraso
        viewMateriaModal.style.display = 'flex';
        
        // Obtener la URL base y construir la URL completa
        const baseUrl = getBaseUrl();
        // Usar la ruta oficial para ver detalles
        const viewUrl = `${baseUrl}/profesor/materias/${materiaId}`;
        console.log('URL para cargar datos:', viewUrl);
        
        fetch(viewUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                console.warn(`Respuesta no OK: ${response.status}`);
                return response.json().catch(() => ({}));
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            if (!data || !data.id) {
                console.warn('Datos incompletos recibidos');
                return;
            }
            
            // Llenar el modal con los datos de la materia
            document.getElementById('viewNombre').textContent = data.nombre || '';
            document.getElementById('viewAula').textContent = data.aula ? data.aula.nombre : 'No especificado';
            document.getElementById('viewHorario').textContent = `${data.horario_ingreso || ''} - ${data.horario_salida || ''}`;
            document.getElementById('viewCurso').textContent = data.curso ? data.curso.nombre : 'No especificado';
            
            // Contar alumnos si está disponible
            if (data.alumnos_count !== undefined) {
                document.getElementById('viewAlumnos').textContent = data.alumnos_count;
            } else {
                document.getElementById('viewAlumnos').textContent = 'No disponible';
            }
            
            // Configurar el botón de generar QR desde el modal si existe
            if (generateQrFromModalBtn) {
                generateQrFromModalBtn.setAttribute('data-id', data.id);
            }
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
        });
    }
    
    // Configurar el botón de generar QR desde el modal
    if (generateQrFromModalBtn) {
        generateQrFromModalBtn.addEventListener('click', function() {
            const materiaId = this.getAttribute('data-id');
            // Cerrar el modal actual
            viewMateriaModal.style.display = 'none';
            // Llamar a la función de generación de QR (debe estar definida en materias_qr.js)
            if (typeof generateQR === 'function') {
                generateQR(materiaId);
            } else {
                console.error('La función generateQR no está disponible');
                alert('No se pudo generar el QR. Por favor, intente nuevamente.');
            }
        });
    }
    
    // Botones para cerrar modales
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            modal.style.display = 'none';
        });
    });
    
    // Botones de cancelar en formularios
    const cancelButtons = document.querySelectorAll('.btn-cancel');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            modal.style.display = 'none';
        });
    });
});
