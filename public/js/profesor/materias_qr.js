/**
 * Script para la generación y manejo de códigos QR para materias
 */
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const qrModal = document.getElementById('qrModal');
    const generateQrButtons = document.querySelectorAll('.btn-generate-qr');
    const downloadQrButton = document.getElementById('downloadQr');
      // Variable para evitar múltiples descargas simultáneas
    let isDownloading = false;    // Exponer la función globalmente para que pueda ser llamada desde materias.blade.php
    window.downloadQR = function() {
        // Evitar múltiples clics
        if (isDownloading) {
            console.log('Ya hay una descarga en proceso, evitando duplicados');
            return;
        }
        
        // Marcar como en proceso de descarga
        isDownloading = true;
        setTimeout(() => { isDownloading = false; }, 2000);
        
        // Verificar que existe el elemento de descarga
        if (!downloadQrButton) {
            console.error('Botón de descarga no encontrado');
            isDownloading = false;
            return;
        }
          try {
            // Obtener el elemento de la imagen QR
            const qrImage = document.getElementById('qrImage');
            if (!qrImage || !qrImage.src || qrImage.src === 'undefined') {
                console.error('Imagen QR no encontrada o no válida');
                alert('Error: No se encontró la imagen QR. Por favor, intente nuevamente.');
                isDownloading = false;
                return;
            }
            
            // Obtener la URL de la imagen directamente del botón
            const downloadUrl = downloadQrButton.getAttribute('href');
            if (!downloadUrl) {
                console.error('URL de descarga no configurada en el botón');
                isDownloading = false;
                return;
            }
            
            // Recuperar el nombre de la materia para crear un nombre de archivo descriptivo
            let fileName = 'qr_code.png';
            const materiaTitleElement = document.querySelector('.modal-title');
            if (materiaTitleElement && materiaTitleElement.textContent) {
                const materiaName = materiaTitleElement.textContent.replace('Código QR: ', '').trim();
                if (materiaName) {
                    // Usar siempre PNG para mejor compatibilidad
                    fileName = `qr_${materiaName.replace(/\s+/g, '_')}.png`;
                }
            }
            
            console.log('Iniciando descarga del QR:', downloadUrl, 'Nombre de archivo:', fileName);
            
            // Usar el generador de QR para descargar (método mejorado que convierte directamente a PNG)
            ClientQRGenerator.downloadImage(downloadUrl, fileName);
        } catch (error) {
            console.error('Error al descargar el QR:', error);
            alert('Error al descargar el código QR. Por favor, inténtelo de nuevo.');
            isDownloading = false;
        }
    }
      // Asignar manejo de eventos al botón de descarga
    if (downloadQrButton) {
        downloadQrButton.addEventListener('click', function(e) {
            e.preventDefault();
            downloadQR();
        });
    }
      // Se removió la funcionalidad de "Ver QR"
      // Función para obtener la URL base
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
            
            // Limpiar cualquier mensaje de error previo
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => msg.remove());
              
            // Construir URL completa (absoluta)
            const baseUrl = getBaseUrl();
            const qrUrl = `${baseUrl}/profesor/materias/${materiaId}/generate-qr`;
            
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
                    console.log('QR generado/recuperado con éxito:', data);                      
                    // Ocultar cualquier mensaje de error previo
                    const errorMessages = document.querySelectorAll('.error-message');
                    errorMessages.forEach(msg => msg.remove());
                    
                    // Actualizar el modal con los datos del QR
                    document.getElementById('qrMateriaName').textContent = materiaName;              
                    
                    // Usar la URL de visualización para mostrar la imagen
                    const qrUrl = data.qr_url;
                    const qrImage = document.getElementById('qrImage');
                    
                    // Establecer la imagen del QR
                    qrImage.src = qrUrl;
                    qrImage.onload = function() {
                        console.log('Imagen QR cargada exitosamente');
                        document.getElementById('serverQrContainer').style.display = 'block';
                    };
                    qrImage.onerror = function() {
                        console.error('Error al cargar la imagen QR');
                        // Si la imagen no carga, intentar generar con la biblioteca client-side
                        document.getElementById('serverQrContainer').style.display = 'none';
                        document.getElementById('clientQrContainer').style.display = 'block';
                        
                        if (typeof ClientQRGenerator !== 'undefined') {
                            ClientQRGenerator.generateFallbackForMateria(
                                'qrCodeCanvas',
                                data.qr_data.materia_id || materiaId,
                                data.qr_data.token_qr || '',
                                data.qr_data.nombre || materiaName,
                                data.qr_data.aula || '',
                                data.qr_data.curso || ''
                            );
                        }
                    };
                    
                    console.log('URL para mostrar QR:', qrUrl);
                        // Para la descarga usamos la misma URL pero con parámetro download
                    const downloadUrl = qrUrl.includes('?') ? 
                        qrUrl + '&download=1' : 
                        qrUrl + '?download=1';
                    
                    // Configurar botón de descarga - asegurando que se establecen correctamente los atributos
                    const downloadButton = document.getElementById('downloadQr');
                    if (downloadButton) {
                        // Configurar atributos para descarga directa
                        downloadButton.href = downloadUrl;
                        downloadButton.download = `qr_${materiaName.replace(/\s+/g, '_')}.png`;
                        
                        // Guardar también en atributos data para referencia
                        downloadButton.setAttribute('data-download-url', downloadUrl);
                        downloadButton.setAttribute('data-materia', materiaName);
                        
                        console.log('Configuración de descarga: ', {url: downloadUrl, filename: downloadButton.download});
                    }
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
                      // Restaurar el botón sin recargar la página
                    this.innerHTML = '<i class="fas fa-qrcode"></i>';
                    this.disabled = false;
                    
                } else {
                    // Error reportado en la respuesta
                    this.innerHTML = '<i class="fas fa-qrcode"></i>';
                    this.disabled = false;
                    
                    showErrorMessage(data.error || 'Error desconocido al generar el QR');
                }
            })            .catch(error => {
                console.error('Error en la generación del QR:', error);
                console.log('Intentando generar el QR localmente...');
                
                // Restaurar el botón
                this.innerHTML = '<i class="fas fa-qrcode"></i>';
                this.disabled = false;
                
                // Eliminar cualquier mensaje de error anterior
                const errorMessages = document.querySelectorAll('.error-message');
                errorMessages.forEach(msg => msg.remove());
                
                // Configurar datos para el QR local
                const qrData = {                    materia_id: materiaId,
                    token_qr: 'local_' + Math.random().toString(36).substring(2, 15),
                    nombre: materiaName,
                    aula: this.closest('tr').querySelector('td:nth-child(4)').textContent || 'No especificado',
                    curso: this.closest('tr').querySelector('td:nth-child(6)').textContent || 'No especificado',
                    horario: this.closest('tr').querySelector('td:nth-child(5)').textContent || 'No especificado'
                };
                
                // Actualizar el modal con los datos del QR
                document.getElementById('qrMateriaName').textContent = materiaName;
                
                // Ocultar el contenedor del servidor y mostrar el del cliente
                document.getElementById('serverQrContainer').style.display = 'none';
                document.getElementById('clientQrContainer').style.display = 'block';
                
                // Generar QR con la biblioteca del cliente
                if (typeof ClientQRGenerator !== 'undefined') {
                    ClientQRGenerator.generateFallbackForMateria(
                        'qrCodeCanvas',
                        materiaId,
                        qrData.token_qr,
                        materiaName,
                        qrData.aula,
                        qrData.curso
                    );
                }
                
                // Mostrar información del QR
                if (document.getElementById('qrTokenData')) {
                    document.getElementById('qrTokenData').textContent = qrData.token_qr || '';
                }
                document.getElementById('qrNombreData').textContent = materiaName;
                document.getElementById('qrAulaData').textContent = qrData.aula;
                document.getElementById('qrCursoData').textContent = qrData.curso;
                document.getElementById('qrHorarioData').textContent = qrData.horario;
                  // Mostrar modal
                qrModal.style.display = 'flex';
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
                            
                            // Configurar URL para descargar - asegurando que todos los atributos se establecen correctamente
                            const downloadButton = document.getElementById('downloadQr');
                            if (downloadButton) {
                                downloadButton.href = data.download_url || data.qr_url;
                                downloadButton.setAttribute('data-download-url', data.download_url || data.qr_url);
                                downloadButton.download = `qr_${materiaName.replace(/\s+/g, '_')}.png`;
                                console.log('URL alternativa para descargar QR:', data.download_url || data.qr_url);
                                console.log('Nombre archivo para descargar:', downloadButton.download);
                            }                              // Mostrar modal
                            qrModal.style.display = 'flex';
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
