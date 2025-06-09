@extends('layouts.estudiante')

@section('title', 'Asistencia QR')

@section('page-title', 'Asistencia QR')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/estudiante/asistencia-qr.css') }}">
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
<script src="{{ asset('js/estudiante/dashboard.js') }}"></script>
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
        } // Función cuando se escanea exitosamente
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
                },
                error: function(xhr, status, error) {
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