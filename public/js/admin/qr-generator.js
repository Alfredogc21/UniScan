/**
 * Funcionalidad para generar QR directamente en el navegador
 * como alternativa si el servidor no puede generar o servir los archivos QR
 */
class ClientQRGenerator {
    /**
     * Método para intentar descargar una imagen directamente desde su URL
     * Funciona como alternativa cuando hay problemas de permisos
     * @param {string} imgUrl - URL de la imagen a descargar
     * @param {string} fileName - Nombre del archivo para guardar
     */
    static downloadImage(imgUrl, fileName = 'qr-code.png') {
        try {
            // Crear un canvas temporal 
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            img.crossOrigin = 'Anonymous'; // Intenta permitir CORS
            
            img.onload = function() {
                // Configurar el canvas con las dimensiones de la imagen
                canvas.width = img.width;
                canvas.height = img.height;
                
                // Dibujar la imagen en el canvas
                ctx.drawImage(img, 0, 0);
                
                // Crear enlace para descargar
                const link = document.createElement('a');
                link.download = fileName;
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Mostrar mensaje de éxito
                console.log('Imagen descargada exitosamente usando método alternativo');
            };
            
            img.onerror = function() {
                console.error('Error al cargar la imagen para descarga alternativa');
                alert('No se pudo descargar la imagen. Intente con el botón "Generar en navegador" para crear un QR local.');
            };
            
            // Añadir parámetro para evitar caché
            img.src = imgUrl + '?t=' + new Date().getTime();
            
            return true;
        } catch (error) {
            console.error('Error en downloadImage:', error);
            return false;
        }
    }
    /**
     * Genera un QR en el lado del cliente
     * @param {string} elementId - ID del elemento donde se generará el QR
     * @param {object} data - Datos para el QR
     * @param {object} options - Opciones adicionales
     */
    static generate(elementId, data, options = {}) {
        // Verificar si el elemento existe
        const container = document.getElementById(elementId);
        if (!container) {
            console.error('Elemento no encontrado:', elementId);
            return false;
        }

        // Configuración predeterminada
        const config = {
            width: options.width || 300,
            height: options.height || 300,
            colorDark: options.colorDark || "#000000",
            colorLight: options.colorLight || "#ffffff",
            correctLevel: options.correctLevel || QRCode.CorrectLevel.H
        };

        // Generar contenido JSON para el QR
        const qrContent = typeof data === 'string' ? data : JSON.stringify(data);

        try {
            // Limpiar el contenedor
            container.innerHTML = '';
            
            // Crear el QR
            new QRCode(container, {
                text: qrContent,
                width: config.width,
                height: config.height,
                colorDark: config.colorDark,
                colorLight: config.colorLight,
                correctLevel: config.correctLevel
            });
            
            console.log('QR generado con éxito en el cliente');
            return true;
        } catch (error) {
            console.error('Error al generar QR en el cliente:', error);
            container.innerHTML = '<div class="error-message">Error al generar QR</div>';
            return false;
        }
    }

    /**
     * Descarga el QR como imagen
     * @param {string} elementId - ID del elemento que contiene el QR
     * @param {string} fileName - Nombre del archivo a descargar
     */    static download(elementId, fileName = 'qr-code.png') {
        const container = document.getElementById(elementId);
        if (!container) {
            console.error('Elemento no encontrado:', elementId);
            return false;
        }

        // Buscar el canvas dentro del contenedor
        const canvas = container.querySelector('canvas');
        if (!canvas) {
            // Si no hay canvas, intentar con la imagen
            const img = container.querySelector('img');
            if (img) {
                return this.downloadImage(img.src, fileName);
            }
            console.error('Canvas o imagen no encontrados dentro del elemento:', elementId);
            return false;
        }

        try {
            // Crear un enlace para descargar
            const link = document.createElement('a');
            link.download = fileName;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link); // Necesario en algunos navegadores
            link.click();
            document.body.removeChild(link); // Limpieza
            return true;
        } catch (error) {
            console.error('Error al descargar QR:', error);
            return false;
        }
    }

    /**
     * Fallback para generar QR con información de token para una materia
     * @param {string} elementId - ID del elemento donde se generará el QR
     * @param {string} materiaId - ID de la materia
     * @param {string} tokenQr - Token QR de la materia
     * @param {string} materiaNombre - Nombre de la materia
     */
    static generateFallbackForMateria(elementId, materiaId, tokenQr, materiaNombre, aula, curso) {
        const data = {
            materia_id: materiaId,
            token_qr: tokenQr,
            nombre: materiaNombre,
            aula: aula || 'No especificado',
            curso: curso || 'No especificado'
        };

        return this.generate(elementId, data, {
            colorDark: "#5a46b7"
        });
    }
}

// Verificar si se incluye la biblioteca QRCode
document.addEventListener('DOMContentLoaded', function() {
    if (typeof QRCode === 'undefined') {
        console.warn('La biblioteca QRCode no está incluida. Agregue el script de QRCode.js antes de usar este generador de QR.');
    }
});
