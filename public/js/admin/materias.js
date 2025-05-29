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
    
    // Función para obtener la URL base del sitio
    function getBaseUrl() {
        return window.location.href.split('/admin/')[0];
    }
    
    // Configuración CSRF para peticiones AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Botones de edición
    const editButtons = document.querySelectorAll('.btn-edit-materia');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const materiaId = this.getAttribute('data-id');
            openEditModal(materiaId);
        });
    });
    
    // Botones para generar QR
    const qrButtons = document.querySelectorAll('.btn-generate-qr');
    qrButtons.forEach(button => {
        button.addEventListener('click', function() {
            const materiaId = this.getAttribute('data-id');
            generateQR(materiaId);
        });
    });
      // Botón para agregar nueva materia
    if (btnAddMateria) {
        btnAddMateria.addEventListener('click', function() {
            addMateriaModal.style.display = 'flex';
            addMateriaModal.classList.add('modal-show');
        });
    }
      // Cerrar modales
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            modal.style.display = 'none';
            modal.classList.remove('modal-show');
        });
    });
    
    // Botón de cancelar en formularios
    const cancelButtons = document.querySelectorAll('.btn-cancel');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            modal.style.display = 'none';
            modal.classList.remove('modal-show');
        });
    });    // Función para obtener la URL base del sitio
    function getBaseUrl() {
        // Detectar si estamos en un sitio con una estructura de carpetas
        const path = window.location.pathname;
        
        // Si el path contiene 'Proyecto/UniScan', extraemos hasta ese punto
        if (path.includes('Proyecto/UniScan')) {
            const basePathIndex = path.indexOf('Proyecto/UniScan');
            return window.location.origin + path.substring(0, basePathIndex + 'Proyecto/UniScan'.length);
        }
        
        return window.location.origin;
    }
      // Función para procesar los datos de la materia y llenar el formulario
    function procesarDatosMateria(data) {
        console.log('Procesando datos recibidos:', data);
        
        try {
            // Extraer datos del objeto, ya sea que venga directamente o dentro de data.data
            const materiaData = data.data || data;
            
            // Llenar el formulario con los datos
            document.getElementById('editMateriaId').value = materiaData.id;
            document.getElementById('editNombre').value = materiaData.nombre;
            
            // Seleccionar el profesor correcto
            const profesorSelect = document.getElementById('editProfesor');
            for (let i = 0; i < profesorSelect.options.length; i++) {
                if (profesorSelect.options[i].value == materiaData.profesor_id) {
                    profesorSelect.options[i].selected = true;
                    break;
                }
            }
            
            // Llenar resto de campos
            document.getElementById('editAula').value = materiaData.aula ? materiaData.aula.nombre : '';
            document.getElementById('editHorarioIngreso').value = materiaData.horario_ingreso;
            document.getElementById('editHorarioSalida').value = materiaData.horario_salida;
            document.getElementById('editCurso').value = materiaData.curso ? materiaData.curso.nombre : '';
            
            // Mostrar el modal
            editMateriaModal.style.display = 'flex';
            editMateriaModal.classList.add('modal-show');
        } catch (error) {
            console.error('Error procesando datos:', error);
            // Aún si hay error, mostrar el modal
            editMateriaModal.style.display = 'flex';
            editMateriaModal.classList.add('modal-show');
        }
    }
    
    // Función para abrir el modal de edición y cargar datos    
    function openEditModal(materiaId) {
        console.log('Abriendo modal para materia ID:', materiaId);
        
        // Mostrar el modal inmediatamente para evitar percepción de retraso
        editMateriaModal.style.display = 'flex';
        editMateriaModal.classList.add('modal-show');
        
        // Construir la URL completa basada en la estructura del sitio
        const baseUrl = getBaseUrl();
        const editUrl = `${baseUrl}/debug-materia/${materiaId}`;
        
        console.log('URL para edición:', editUrl);
        
        // Silenciosamente cargar los datos en segundo plano
        fetch(editUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(response => {
            procesarDatosMateria(response);
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
            // No se muestra alerta, solo se registra el error
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
            
            fetch(updateUrl, {
                method: 'POST', // Se usa POST con _method=PUT para compatibilidad con Laravel
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            }).then(response => {
            // Capturar el texto de la respuesta y registrarlo para depuración
            return response.text().then(text => {
                console.log('Respuesta completa:', text);
                
                try {
                    // Intentar parsear como JSON
                    const data = JSON.parse(text);
                    if (!response.ok) {
                        throw new Error('Error al guardar los cambios: ' + (data.message || response.statusText));
                    }
                    return data;
                } catch (e) {
                    console.error('Error al parsear JSON:', e);
                    if (!response.ok) {
                        throw new Error('Error al guardar los cambios: ' + response.statusText);
                    }
                    throw new Error('Error al procesar la respuesta');
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Cerrar modal y recargar página para ver cambios
                editMateriaModal.style.display = 'none';
                editMateriaModal.classList.remove('modal-show');
                window.location.reload();
                    editMateriaModal.classList.remove('modal-show');
                    window.location.reload();
                } else {
                    alert('Hubo un problema al actualizar la materia.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al guardar los cambios.');
            });
        });
    }
      // Función para generar QR
    function generateQR(materiaId) {
        const baseUrl = getBaseUrl();
        fetch(`${baseUrl}/admin/materias/${materiaId}/generate-qr`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                console.warn('Respuesta no satisfactoria al generar QR');
            }
            return response.json().catch(e => ({})); // En caso de error, devolver objeto vacío
        })        
        .then(data => {
            if (data.success) {
                // Mostrar el QR en modal o recargar página
                if (data.qr_url) {
                    showQrModal(data.qr_url, materiaId);
                } else {
                    console.log('QR generado, recargando página');
                    window.location.reload();
                }
            } else {
                console.warn('No se recibió confirmación de éxito al generar QR');
                window.location.reload(); // Recargar de todas formas
            }
        })
        .catch(error => {
            console.error('Error al generar QR:', error);
            // No mostrar alerta, solo recargar
            window.location.reload();
        });
    }
    
    // Función para mostrar el QR en un modal
    function showQrModal(qrPath, materiaId) {
        // Implementar la visualización del QR
        const qrModalContent = qrModal.querySelector('.modal-content') || qrModal;
        qrModalContent.innerHTML = `
            <div class="modal-header">
                <h3>Código QR Generado</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="qr-container" style="text-align: center; padding: 20px;">
                <img src="${qrPath}" alt="Código QR" style="max-width: 100%; height: auto;">
                <p style="margin-top: 15px;">Escanea este código QR para registrar asistencia</p>
                <div class="btn-container" style="margin-top: 20px;">
                    <button type="button" class="btn-download" data-qr="${qrPath}" data-id="${materiaId}">Descargar QR</button>
                </div>
            </div>
        `;
        
        // Mostrar el modal
        qrModal.classList.add('modal-show');
        
        // Cerrar modal al hacer click en "X"
        const closeBtn = qrModal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                qrModal.classList.remove('modal-show');
                window.location.reload(); // Recargar para ver estado actualizado
            });
        }
        
        // Manejar descarga de QR
        const downloadBtn = qrModal.querySelector('.btn-download');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function() {
                const qrImage = this.getAttribute('data-qr');
                // Crear un enlace temporal para descargar la imagen
                const link = document.createElement('a');
                link.href = qrImage;
                link.download = `qr-materia-${this.getAttribute('data-id')}.png`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
    }
    
    // Filtro de búsqueda para materias
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = materiasTable.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const nombre = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const profesor = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const curso = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                
                if (nombre.includes(searchTerm) || profesor.includes(searchTerm) || curso.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});