@extends('layouts.estudiante')

@section('title', 'Asistencia QR')

@section('page-title', 'Asistencia QR')

@section('styles')
<style>
    .qr-scanner-container {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        padding: 2rem;
        margin: 1rem auto;
        max-width: 500px;
    }

    .scanner-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .scanner-header h2 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .scanner-header p {
        color: #7f8c8d;
        font-size: 0.95rem;
    }

    #qr-reader {
        width: 100%;
        border-radius: 10px;
        overflow: hidden;
        border: 3px solid #e3f2fd;
        background: #f8f9fa;
        position: relative;
    }

    #qr-reader__scan_region {
        min-height: 300px !important;
        position: relative;
    }

    #qr-reader__camera {
        border-radius: 10px;
        width: 100% !important;
        height: auto !important;
    }

    .scan-region-highlight {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 200px;
        border: 2px solid #667eea;
        border-radius: 10px;
        box-shadow: 0 0 0 100vmax rgba(0, 0, 0, 0.5);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 100vmax rgba(0, 0, 0, 0.5), 0 0 0 0 rgba(102, 126, 234, 0.7);
        }

        70% {
            box-shadow: 0 0 0 100vmax rgba(0, 0, 0, 0.5), 0 0 0 10px rgba(102, 126, 234, 0);
        }

        100% {
            box-shadow: 0 0 0 100vmax rgba(0, 0, 0, 0.5), 0 0 0 0 rgba(102, 126, 234, 0);
        }
    }

    .scanner-controls {
        margin-top: 1.5rem;
        text-align: center;
    }

    .btn-scanner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 500;
        margin: 0 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-scanner:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-scanner:disabled {
        background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .status-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-top: 1.5rem;
        border-left: 4px solid #17a2b8;
    }

    .status-card h5 {
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .status-card p {
        color: #6c757d;
        margin: 0;
        font-size: 0.9rem;
    }

    .success-message {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: 1px solid #c3e6cb;
        border-left: 4px solid #28a745;
        color: #155724;
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
        animation: slideIn 0.5s ease-out;
    }

    .error-message {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border: 1px solid #f5c6cb;
        border-left: 4px solid #dc3545;
        color: #721c24;
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
        animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .scanning-indicator {
        display: none;
        text-align: center;
        margin-top: 1rem;
    }

    .scanning-indicator .spinner-border {
        color: #667eea;
        width: 2rem;
        height: 2rem;
    }

    .instruction-card {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e1bee7;
    }

    .instruction-card h6 {
        color: #4a148c;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .instruction-card ol {
        color: #6a1b9a;
        margin: 0;
        padding-left: 1.2rem;
    }

    .instruction-card li {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    /* Responsivo */
    @media (max-width: 768px) {
        .qr-scanner-container {
            margin: 0.5rem;
            padding: 1.5rem;
        }

        #qr-reader__scan_region {
            min-height: 250px !important;
        }

        .btn-scanner {
            padding: 10px 20px;
            font-size: 0.9rem;
            margin: 5px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <!-- Instrucciones -->
            <div class="instruction-card">
                <h6><i class="fas fa-info-circle me-2"></i>Instrucciones para el escaneo QR</h6>
                <ol>
                    <li>Presiona el botón "Iniciar Escáner" para activar la cámara</li>
                    <li>Apunta tu cámara hacia el código QR de la materia</li>
                    <li>Mantén el código dentro del marco de escaneo</li>
                    <li>El sistema registrará automáticamente tu asistencia</li>
                </ol>
            </div>

            <!-- Scanner Container -->
            <div class="qr-scanner-container">
                <div class="scanner-header">
                    <h2><i class="fas fa-qrcode me-2"></i>Escáner de Asistencia QR</h2>
                    <p>Escanea el código QR de tu materia para registrar tu asistencia</p>
                </div>

                <!-- QR Reader -->
                <div id="qr-reader">
                    <div class="scan-region-highlight" id="scan-region-highlight"></div>
                </div>

                <!-- Controls -->
                <div class="scanner-controls">
                    <button id="start-scanner" class="btn btn-scanner">
                        <i class="fas fa-play me-2"></i>Iniciar Escáner
                    </button>
                    <button id="stop-scanner" class="btn btn-scanner" disabled>
                        <i class="fas fa-stop me-2"></i>Detener Escáner
                    </button>
                    <button id="switch-camera" class="btn btn-scanner" style="display: none;">
                        <i class="fas fa-sync-alt me-2"></i>Cambiar Cámara
                    </button>
                </div>

                <!-- Scanning Indicator -->
                <div class="scanning-indicator" id="scanning-indicator">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Escaneando...</span>
                    </div>
                    <p class="mt-2">Escaneando código QR...</p>
                </div>

                <!-- Status Card -->
                <div class="status-card">
                    <h5><i class="fas fa-info-circle me-2"></i>Estado del Escáner</h5>
                    <p id="scanner-status">Presiona "Iniciar Escáner" para comenzar</p>
                </div>

                <!-- Messages Container -->
                <div id="messages-container"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    $(document).ready(function() {
        let html5QrCode = null;
        let isScanning = false;
        let currentCamera = 'environment'; // 'environment' para cámara trasera o 'user' para frontal
        let cameras = [];

        // Elementos del DOM
        const startBtn = $('#start-scanner');
        const stopBtn = $('#stop-scanner');
        const switchCameraBtn = $('#switch-camera');
        const statusEl = $('#scanner-status');
        const scanningIndicator = $('#scanning-indicator');
        const messagesContainer = $('#messages-container');
        const scanRegionHighlight = $('#scan-region-highlight');

        // Función para mostrar mensajes
        function showMessage(message, type = 'success') {
            const alertClass = type === 'success' ? 'success-message' : 'error-message';
            const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';

            const messageHtml = `
                    <div class="${alertClass}">
                        <i class="fas fa-${icon} me-2"></i>
                        <strong>${message}</strong>
                    </div>
                `;

            messagesContainer.html(messageHtml);

            // Auto-hide después de 5 segundos
            setTimeout(() => {
                messagesContainer.fadeOut(300, function() {
                    $(this).html('').show();
                });
            }, 5000);
        }

        // Función para actualizar el estado
        function updateStatus(status) {
            statusEl.text(status);
        }        // Función cuando se escanea exitosamente
        function onScanSuccess(decodedText, decodedResult) {
            console.log('QR Code escaneado:', decodedText);

            // Diagnosticar el QR para detectar problemas
            diagnosticarQR(decodedText);

            // Reproducir sonido de éxito (opcional)
            let successSound = new Audio('data:audio/mp3;base64,//uQxAAAAAAAAAAAAAAAAAAAAAAAWGluZwAAAA8AAAAFAAAGhgBVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVWqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqr///////////////////////////////////////////8AAAA8TEFNRTMuMTAwBEgAAAAAAAAAABUgJAMGQQABmgAABoYyf2HIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//sQxAADwAABpAAAACAAANIAAAAQAAAaQAAAAgAAA0gAAABExBTUUzLjEwMFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV//sQxDyDwAABpAAAACAAANIAAAAQAAAaQAAAAgAAA0gAAABFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV//sQxGuDwAABpAAAACAAANIAAAAQAAAaQAAAAgAAA0gAAABFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV//sQxJQDwAABpAAAACAAANIAAAAQAAAaQAAAAgAAA0gAAABFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV');
            successSound.play();

            // Añadir feedback visual de éxito
            scanRegionHighlight.css('border-color', '#28a745');
            setTimeout(() => {
                scanRegionHighlight.css('border-color', '#667eea');
            }, 1000);

            // Detener el escáner
            stopScanner();

            // Mostrar indicador de procesamiento
            scanningIndicator.show();
            updateStatus('Procesando código QR...'); // Enviar el código QR al servidor
            $.ajax({
                url: '{{ route("estudiante.procesar-qr") }}',
                method: 'POST',
                data: {
                    token_qr: decodedText,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    scanningIndicator.hide();

                    if (response.success) {
                        showMessage(response.message, 'success');
                        updateStatus('Asistencia registrada exitosamente');

                        // Mostrar detalles adicionales si están disponibles
                        if (response.data) {
                            const details = `
                                    <div class="success-message mt-2">
                                        <strong>Detalles de la asistencia:</strong><br>
                                        <small>
                                            <i class="fas fa-book me-1"></i>Materia: ${response.data.materia || 'N/A'}<br>
                                            <i class="fas fa-clock me-1"></i>Hora: ${response.data.fecha_hora || 'N/A'}<br>
                                            <i class="fas fa-check-circle me-1"></i>Estado: ${response.data.tipo_asistencia || 'N/A'}<br>
                                            <i class="fas fa-door-open me-1"></i>Aula: ${response.data.aula || 'N/A'}<br>
                                            <i class="fas fa-users me-1"></i>Curso: ${response.data.curso || 'N/A'}<br>
                                            <i class="fas fa-user-tie me-1"></i>Profesor: ${response.data.profesor || 'N/A'}
                                        </small>
                                    </div>
                                `;
                            messagesContainer.append(details);
                        }
                    } else {
                        showMessage(response.message || 'Error al procesar el código QR', 'error');
                        updateStatus('Error en el procesamiento');
                    }
                },                error: function(xhr, status, error) {
                    scanningIndicator.hide();
                    console.error('Error AJAX:', error, xhr);

                    let errorMessage = 'Error de conexión. Inténtalo de nuevo.';
                    
                    // Intentar obtener mensaje de error del servidor
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        // Mostrar información de debug en la consola (para desarrollo)
                        if (xhr.responseJSON.debug_info) {
                            console.warn('Información de debug:', xhr.responseJSON.debug_info);
                        }
                    } else if (xhr.status === 422) {
                        errorMessage = 'Código QR inválido o expirado';
                    } else if (xhr.status === 404) {
                        errorMessage = 'El código QR no fue reconocido por el sistema';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Error interno del servidor';
                    }

                    // Mostrar datos del QR escaneado para diagnóstico
                    console.info('QR escaneado:', decodedText);

                    showMessage(errorMessage, 'error');
                    updateStatus('Error en el escaneo');
                    
                    // Habilitar el botón para volver a escanear
                    startBtn.prop('disabled', false);
                }
            });
        }

        // Función para obtener las cámaras disponibles
        async function getCameras() {
            try {
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length) {
                    cameras = devices;
                    if (devices.length > 1) {
                        switchCameraBtn.show();
                    }
                }
            } catch (err) {
                console.error('Error al obtener cámaras:', err);
            }
        }

        // Función para cambiar de cámara
        function switchCamera() {
            if (isScanning && html5QrCode) {
                stopScanner(() => {
                    currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
                    startScanner();
                });
            }
        }

        // Función cuando hay error en el escaneo
        function onScanFailure(error) {
            // No hacer nada, esto se llama continuamente cuando no encuentra QR
        }

        // Función para iniciar el escáner
        function startScanner(callback) {
            if (isScanning) return;

            try {
                html5QrCode = new Html5Qrcode("qr-reader");

                scanRegionHighlight.show();

                const config = {
                    fps: 20, // Mayor velocidad para detección más rápida
                    qrbox: 250,
                    aspectRatio: 1.0,
                    disableFlip: false, // Permite voltear horizontalmente para mejor detección
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true // Usar detector nativo del navegador si está disponible
                    },
                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE] // Solo soportar QR para mayor velocidad
                };

                html5QrCode.start({
                        facingMode: currentCamera
                    },
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    isScanning = true;
                    startBtn.prop('disabled', true);
                    stopBtn.prop('disabled', false);
                    updateStatus('Enfoca el código QR para registrar asistencia');
                    messagesContainer.html('');

                    if (callback) callback();
                }).catch(err => {
                    console.error('Error al iniciar el escáner:', err);
                    showMessage('Error al acceder a la cámara. Verifica los permisos.', 'error');
                    updateStatus('Error al iniciar la cámara');
                });

            } catch (error) {
                console.error('Error al crear el escáner:', error);
                showMessage('Error al inicializar el escáner', 'error');
                updateStatus('Error de inicialización');
            }
        }

        // Función para detener el escáner
        function stopScanner(callback) {
            if (!isScanning || !html5QrCode) {
                if (callback) callback();
                return;
            }

            html5QrCode.stop().then(() => {
                html5QrCode = null;
                isScanning = false;
                startBtn.prop('disabled', false);
                stopBtn.prop('disabled', true);
                updateStatus('Escáner detenido');
                scanningIndicator.hide();
                scanRegionHighlight.hide();

                if (callback) callback();
            }).catch(err => {
                console.error('Error al detener el escáner:', err);
                if (callback) callback();
            });
        }

        // Event Listeners
        startBtn.on('click', () => startScanner());
        stopBtn.on('click', () => stopScanner());
        switchCameraBtn.on('click', switchCamera);

        // Función para validar un JSON y diagnosticar problemas
        function diagnosticarQR(qrText) {
            try {
                if (!qrText || typeof qrText !== 'string') {
                    return false;
                }

                // Verificar si parece JSON
                if (qrText.trim().startsWith('{') || qrText.trim().startsWith('[')) {
                    try {
                        const jsonData = JSON.parse(qrText);
                        console.info('QR JSON válido:', jsonData);
                        return true;
                    } catch (e) {
                        console.warn('QR contiene JSON inválido:', e.message);
                        console.info('Contenido del QR:', qrText);
                        return false;
                    }
                } else {
                    console.info('QR no es JSON, usando como token directo');
                    return true;
                }
            } catch (e) {
                console.error('Error al diagnosticar QR:', e);
                return false;
            }
        }

        // Iniciar automáticamente al cargar la página (opcional)
        setTimeout(() => {
            // Verificar soporte para cámaras
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showMessage('Tu navegador no soporta el acceso a la cámara', 'error');
                startBtn.prop('disabled', true);
                updateStatus('Navegador no compatible');
            } else {
                // Obtener cámaras disponibles
                getCameras().then(() => {
                    // Auto-iniciar scanner (opcional)
                    //startScanner();
                });
            }
        }, 1000);

        // Limpiar al salir de la página
        $(window).on('beforeunload', function() {
            if (isScanning) {
                stopScanner();
            }
        });

        // Orientación cambiada (mejora para dispositivos móviles)
        $(window).on('orientationchange', function() {
            if (isScanning) {
                stopScanner(() => {
                    setTimeout(() => {
                        startScanner();
                    }, 300);
                });
            }
        });
    });
</script>
@endsection