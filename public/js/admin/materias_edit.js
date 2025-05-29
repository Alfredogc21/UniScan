document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const editMateriaModal = document.getElementById('editMateriaModal');
    const addMateriaModal = document.getElementById('addMateriaModal');
    const qrModal = document.getElementById('qrModal');
    const editMateriaForm = document.getElementById('editMateriaForm');
    const addMateriaForm = document.getElementById('addMateriaForm');
    const btnAddMateria = document.getElementById('btnAddMateria');
    const materiasTable = document.getElementById('materiasTable');
    const searchInput = document.getElementById('materiaSearchInput');
    
    // Configuración CSRF para peticiones AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Función para obtener la URL base del sitio
    function getBaseUrl() {
        const path = window.location.pathname;
        const segments = path.split('/');
        const adminIndex = segments.findIndex(segment => segment === 'admin');
        
        if (adminIndex > -1) {
            const basePath = segments.slice(0, adminIndex).join('/');
            return window.location.origin + basePath;
        }
        return window.location.origin;
    }
    
    // Botones de edición
    const editButtons = document.querySelectorAll('.btn-edit-materia');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const materiaId = this.getAttribute('data-id');
            openEditModal(materiaId);
        });
    });    // Función para abrir el modal de edición y cargar datos
    function openEditModal(materiaId) {
        console.log('Abriendo modal para materia ID:', materiaId);
        
        // Mostrar el modal inmediatamente para evitar percepción de retraso
        editMateriaModal.style.display = 'flex';
        
        // Obtener la URL base y construir la URL completa
        const baseUrl = getBaseUrl();
        // Usar directamente la ruta de diagnóstico que sabemos que funciona
        const debugUrl = `${baseUrl}/debug-materia/${materiaId}`;
        console.log('URL para cargar datos:', debugUrl);
        
        fetch(debugUrl, {
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
        .then(responseData => {
            // Extraer los datos de la materia de la respuesta
            const data = responseData.data || responseData;
            console.log('Datos recibidos:', data);
            
            if (!data || !data.id) {
                console.warn('Datos incompletos recibidos');
                return;
            }
            
            // Llenar el formulario con los datos
            document.getElementById('editMateriaId').value = data.id;
            document.getElementById('editNombre').value = data.nombre || '';
            
            // Seleccionar el profesor correcto
            const profesorSelect = document.getElementById('editProfesor');
            if (profesorSelect) {
                for (let i = 0; i < profesorSelect.options.length; i++) {
                    if (profesorSelect.options[i].value == data.profesor_id) {
                        profesorSelect.options[i].selected = true;
                        break;
                    }
                }
            }
            
            // Llenar resto de campos
            document.getElementById('editAula').value = data.aula ? data.aula.nombre : '';
            document.getElementById('editHorarioIngreso').value = data.horario_ingreso || '';
            document.getElementById('editHorarioSalida').value = data.horario_salida || '';
            document.getElementById('editCurso').value = data.curso ? data.curso.nombre : '';
        })
        .catch(error => {
            // Solo registrar el error en la consola, sin mostrar alerta
            console.error('Error al cargar datos:', error);
        });
    }
    
    // Procesar formulario de edición
    if (editMateriaForm) {
        editMateriaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const materiaId = document.getElementById('editMateriaId').value;
            const formData = new FormData(this);
            
            // Asegurarnos de tener el método correcto para Laravel
            formData.append('_method', 'PUT');
            
            const baseUrl = getBaseUrl();
            const updateUrl = `${baseUrl}/admin/materias/${materiaId}`;
            console.log('URL para actualizar:', updateUrl);
            
            fetch(updateUrl, {
                method: 'POST', // Se usa POST con _method=PUT para compatibilidad con Laravel
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response:', text);
                        throw new Error(`Error ${response.status}: ${response.statusText}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta actualización:', data);
                if (data.success) {
                    // Cerrar modal y recargar página para ver cambios
                    editMateriaModal.style.display = 'none';
                    alert('¡Materia actualizada correctamente!');
                    window.location.reload();
                } else {
                    alert('Hubo un problema al actualizar la materia.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al guardar los cambios: ' + error.message);
            });
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
