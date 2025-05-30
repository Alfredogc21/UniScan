/**
 * Script para la generación y manejo de códigos QR para materias
 */
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const qrModal = document.getElementById('qrModal');
    const generateQrButtons = document.querySelectorAll('.btn-generate-qr');
    const downloadQrButton = document.getElementById('downloadQr');
    const viewQrButton = document.getElementById('viewQr');
      // Función para descargar QR directamente
    function downloadQR() {
        // Verificar que existe el elemento de descarga
        if (!downloadQrButton) return;
        
        // Obtener la URL directamente del botón
        const downloadUrl = downloadQrButton.getAttribute('data-download-url') || downloadQrButton.href;
        
        if (!downloadUrl || downloadUrl === '#' || downloadUrl.includes('undefined')) {
            console.error('URL de descarga no válida');
            
            // Intentar obtener la URL de la imagen mostrada
            const qrImage = document.getElementById('qrImage');
            if (qrImage && qrImage.src && qrImage.src !== 'undefined') {                // Crear un enlace temporal para la descarga con la URL de la imagen
                const downloadLink = document.createElement('a');
                const imgSrc = qrImage.src;
                downloadLink.href = imgSrc.includes('?') ? 
                    imgSrc + '&download=1' : 
                    imgSrc + '?download=1';
                downloadLink.download = `qr_code_${Date.now()}.svg`;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
                console.log('Descarga alternativa iniciada');
                return;
            }
            
            alert('Error: URL de descarga no disponible. Por favor, haga clic derecho en la imagen y seleccione "Guardar imagen como...".');
            return;
        }
        
        const fileName = downloadQrButton.getAttribute('download') || 'qr_code.svg';
        
        console.log('Iniciando descarga del QR:', downloadUrl);
        
        // Método simple: redirección directa al script de descarga
        try {
            // Usar un enlace para la descarga
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            console.log('Descarga iniciada con éxito');
        } catch (error) {
            console.error('Error al iniciar la descarga:', error);
            
            // Fallback: intentar con redirección directa
            try {
                window.location.href = downloadUrl;
            } catch (redirectError) {
                console.error('Error al redirigir:', redirectError);
                alert('Error al descargar el QR. Por favor, intente con el botón "Ver QR" y guarde la imagen manualmente.');
            }
        }
    }
      // Asignar manejo de eventos al botón de descarga
    if (downloadQrButton) {
        downloadQrButton.addEventListener('click', function(e) {
            e.preventDefault();
            downloadQR();
        });
    }
      // Asignar manejo de eventos al botón de ver QR
    if (viewQrButton) {
        viewQrButton.addEventListener('click', function() {
            const qrImage = document.getElementById('qrImage');
            // Usar la URL de la imagen mostrada en el modal
            const viewUrl = qrImage.src;
            
            // Abrir la imagen en una nueva ventana/pestaña
            window.open(viewUrl, '_blank');
            console.log('Abriendo QR para visualización:', viewUrl);
        });
    }
    
    // Función para obtener la URL base
    function getBaseUrl() {
        return window.location.origin + window.location.pathname.split('/admin')[0];
    }
    
    // Configuración CSRF para peticiones AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Generar QR con manejo de errores mejorado
    generateQrButtons.forEach(button => {
        button.addEventListener('click', function() {
            const materiaId = this.getAttribute('data-id');
            const materiaName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
            
            // Mostrar indicador de carga
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            // Construir URL completa (absoluta)
            const baseUrl = getBaseUrl();
            const qrUrl = `${baseUrl}/admin/materias/${materiaId}/generate-qr`;
            
            console.log('Generando QR para materia:', materiaId);
            console.log('URL para QR:', qrUrl);
              // Realizar la solicitud principal
            fetch(qrUrl, {
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
                    return response.text().then(text => {
                        try {
                            // Intentar parsear como JSON si es posible
                            const errorData = JSON.parse(text);
                            console.error('Error JSON recibido:', errorData);
                            throw new Error(errorData.error || `Error ${response.status}: ${response.statusText}`);
                        } catch (e) {
                            // Si no se puede parsear como JSON, devolver el texto como está
                            console.error('Error al parsear respuesta:', text);
                            throw new Error(`Error ${response.status}: ${text || response.statusText}`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log('QR generado/recuperado con éxito:', data);                      // Actualizar el modal con los datos del QR
                    document.getElementById('qrMateriaName').textContent = materiaName;              // Usar la URL de visualización para mostrar la imagen
                    const qrUrl = data.qr_url;
                    document.getElementById('qrImage').src = qrUrl;
                    console.log('URL para mostrar QR:', qrUrl);
                      // Para la descarga usamos la misma URL pero con parámetro download
                    const downloadUrl = qrUrl.includes('?') ? 
                        qrUrl + '&download=1' : 
                        qrUrl + '?download=1';
                    
                    // Configurar botón de descarga
                    document.getElementById('downloadQr').href = downloadUrl;
                    document.getElementById('downloadQr').setAttribute('data-download-url', downloadUrl);
                    document.getElementById('downloadQr').setAttribute('data-materia', materiaName);
                    document.getElementById('qrMateriaName').textContent = materiaName;
                    console.log('Materia para el QR:', materiaName);
                    console.log('URL para descargar configurada:', downloadUrl);
                    
                    // Mostrar los datos del QR si están disponibles
                    if (data.qr_data) {
                        if (document.getElementById('qrTokenData')) {
                            document.getElementById('qrTokenData').textContent = data.qr_data.token_qr || '';
                        }
                        document.getElementById('qrNombreData').textContent = data.qr_data.nombre || '';
                        document.getElementById('qrAulaData').textContent = data.qr_data.aula || '';
                        document.getElementById('qrCursoData').textContent = data.qr_data.curso || '';
                        document.getElementById('qrHorarioData').textContent = data.qr_data.horario || '';
                    }
                    
                    // Mostrar modal
                    qrModal.style.display = 'flex';
                    
                    // Restaurar el botón o recargar según sea necesario
                    if (data.existing) {
                        this.innerHTML = '<i class="fas fa-qrcode"></i>';
                        this.disabled = false;
                    } else {
                        // Es un nuevo QR, recargar para actualizar estado
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                } else {
                    // Error reportado en la respuesta
                    this.innerHTML = '<i class="fas fa-qrcode"></i>';
                    this.disabled = false;
                    
                    showErrorMessage(data.error || 'Error desconocido al generar el QR');
                }
            })
            .catch(error => {
                console.error('Error en la generación del QR:', error);
                console.log('Intentando con ruta alternativa...');
                
                // Intentar con ruta alternativa
                const alternativeUrl = `${baseUrl}/generate-qr/${materiaId}`;
                
                // Mostrar mensaje de espera
                const waitMessage = document.createElement('div');
                waitMessage.className = 'info-message';
                waitMessage.textContent = 'La primera solicitud falló. Intentando método alternativo...';
                waitMessage.style.position = 'fixed';
                waitMessage.style.top = '20px';
                waitMessage.style.left = '50%';
                waitMessage.style.transform = 'translateX(-50%)';
                waitMessage.style.backgroundColor = '#cce5ff';
                waitMessage.style.color = '#004085';
                waitMessage.style.padding = '10px 20px';
                waitMessage.style.borderRadius = '5px';
                waitMessage.style.zIndex = '9999';
                
                document.body.appendChild(waitMessage);
                
                // Intento con método alternativo
                fetch(alternativeUrl)
                    .then(response => response.json())
                    .then(data => {
                        document.body.removeChild(waitMessage);
                        
                        if (data.success) {
                            console.log('QR generado correctamente con método alternativo');                            // Actualizar datos en el modal
                            document.getElementById('qrMateriaName').textContent = materiaName;
                            // Usar la URL de visualización directa
                            document.getElementById('qrImage').src = data.qr_url;
                            console.log('URL alternativa para mostrar QR:', data.qr_url);
                            
                            // Configurar URL para descargar
                            document.getElementById('downloadQr').href = data.download_url;
                            document.getElementById('downloadQr').download = `qr_${materiaName.replace(/\s+/g, '_')}.svg`;
                            console.log('URL alternativa para descargar QR:', data.download_url);
                              // Mostrar modal
                            qrModal.style.display = 'flex';
                            
                            // Recargar para actualizar estado
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            // Restaurar el botón
                            this.innerHTML = '<i class="fas fa-qrcode"></i>';
                            this.disabled = false;
                            
                            // Mostrar mensaje de error
                            showErrorMessage(`Error con método alternativo: ${data.error || 'Error desconocido'}`);
                        }
                    })
                    .catch(alternativeError => {
                        document.body.removeChild(waitMessage);
                        
                        console.error('Error con método alternativo:', alternativeError);
                        
                        // Restaurar el botón
                        this.innerHTML = '<i class="fas fa-qrcode"></i>';
                        this.disabled = false;
                        
                        // Mostrar error con más detalles
                        showErrorMessage(`No se pudo generar el QR. Por favor, contacte al administrador del sistema.`);
                    });
            });
        });
    });
    
    // Función para mostrar mensajes de error de manera más visual
    function showErrorMessage(message) {
        // Crear elemento de alerta
        const errorAlert = document.createElement('div');
        errorAlert.className = 'error-message';
        errorAlert.textContent = message;
        errorAlert.style.position = 'fixed';
        errorAlert.style.top = '20px';
        errorAlert.style.left = '50%';
        errorAlert.style.transform = 'translateX(-50%)';
        errorAlert.style.backgroundColor = '#f8d7da';
        errorAlert.style.color = '#721c24';
        errorAlert.style.padding = '10px 20px';
        errorAlert.style.borderRadius = '5px';
        errorAlert.style.zIndex = '9999';
        errorAlert.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        errorAlert.style.width = 'auto';
        errorAlert.style.maxWidth = '80%';
        errorAlert.style.textAlign = 'center';
        
        // Añadir al DOM
        document.body.appendChild(errorAlert);
        
        // Eliminar después de 5 segundos
        setTimeout(() => {
            errorAlert.style.opacity = '0';
            errorAlert.style.transition = 'opacity 0.5s ease';
            
            setTimeout(() => {
                if (errorAlert.parentNode) {
                    document.body.removeChild(errorAlert);
                }
            }, 500);
        }, 5000);
    }
    
    // Cerrar modal de QR
    const closeModalButtons = document.querySelectorAll('#qrModal .modal-close, #qrModal .btn-cancel');
    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            qrModal.style.display = 'none';
        });
    });
});
