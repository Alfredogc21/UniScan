<?php

/**
 * Script para descargar QR sin problemas de permisos
 * Este script lee directamente el archivo QR y lo entrega al navegador
 */

// Configurar error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtener parámetros
$filename = $_GET['file'] ?? null;
$id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;

// Verificación básica para evitar Directory Traversal
if ($filename && (strpos($filename, '..') !== false || strpos($filename, '/') === 0)) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Acceso denegado';
    exit;
}

// Rutas posibles donde puede estar el archivo (priorizar la ubicación pública)
$publicPath = __DIR__ . '/storage/';  // Nueva ubicación principal
$storagePath = __DIR__ . '/../storage/app/public/';  // Ubicación secundaria
$privatePath = __DIR__ . '/../storage/app/private/public/';  // Antigua ubicación (para compatibilidad)

// Si se proporciona un filename, buscarlo en todas las ubicaciones posibles
if ($filename) {
    // Primero buscar en la ubicación pública (es donde deberían estar todos los QR ahora)
    if (file_exists($publicPath . $filename)) {
        $fullPath = $publicPath . $filename;
    } elseif (file_exists($publicPath . 'qrcodes/' . $filename)) {
        $fullPath = $publicPath . 'qrcodes/' . $filename;
    } elseif (file_exists($storagePath . $filename)) {
        $fullPath = $storagePath . $filename;
    } elseif (file_exists($privatePath . $filename)) {
        $fullPath = $privatePath . $filename;
    } else {
        // Intenta generar el QR si se proporciona ID
        if ($id) {
            // Intentar regenerar el QR
            $regenerarQr = true;
        } else {
            header('HTTP/1.0 404 Not Found');
            echo 'Archivo QR no encontrado en ninguna ubicación: ' . $filename;
            exit;
        }
    }
}
// Si no hay filename pero hay id y token, buscar por patrón en todas las rutas
elseif ($id && $token) {
    $found = false;
    // Buscar primero en la ubicación pública, que es donde deberían estar los QR ahora
    $searchLocations = [
        $publicPath . 'qrcodes/', 
        $storagePath . 'qrcodes/', 
        $privatePath . 'qrcodes/'
    ];
    
    foreach ($searchLocations as $location) {
        $pattern = $location . 'materia_' . $id . '_*';
        $files = glob($pattern);
        
        if (!empty($files)) {
            $fullPath = $files[0];
            $filename = basename($fullPath);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        // Intentar regenerar el QR
        $regenerarQr = true;
    }
} else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Parámetros insuficientes';
    exit;
}

// Si debemos regenerar el QR
if (isset($regenerarQr) && $regenerarQr && $id) {
    // Cargar el autoloader de Laravel para poder usar el generador de QR
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    
        // Intentar cargar la aplicación de Laravel
        if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
            $app = require_once __DIR__ . '/../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Http\Kernel')->handle(Illuminate\Http\Request::capture());
            
            // Cargar y usar el generador de QR
            require_once __DIR__ . '/../app/Http/Controllers/Admin/QrGenerator.php';
            $result = \App\Http\Controllers\Admin\generate_qr($id);
            
            if ($result['success']) {
                // Obtener la ruta del archivo recién generado
                $newFilename = str_replace('qrcodes/', '', explode('?', basename($result['qr_url']))[0]);
                  // Buscar el archivo en las posibles ubicaciones (priorizar la nueva ubicación pública)
                if (file_exists($publicPath . 'qrcodes/' . $newFilename)) {
                    $fullPath = $publicPath . 'qrcodes/' . $newFilename;
                } elseif (file_exists($storagePath . 'qrcodes/' . $newFilename)) {
                    $fullPath = $storagePath . 'qrcodes/' . $newFilename;
                } elseif (file_exists($privatePath . 'qrcodes/' . $newFilename)) {
                    $fullPath = $privatePath . 'qrcodes/' . $newFilename;
                } else {
                    // Si no existe, buscar sin la extensión específica
                    $baseFilename = preg_replace('/\.[^.]+$/', '', $newFilename);
                    $patterns = [
                        $publicPath . 'qrcodes/' . $baseFilename . '.*',
                        $storagePath . 'qrcodes/' . $baseFilename . '.*',
                        $privatePath . 'qrcodes/' . $baseFilename . '.*'
                    ];
                    
                    $found = false;
                    foreach ($patterns as $pattern) {
                        $matchedFiles = glob($pattern);
                        if (!empty($matchedFiles)) {
                            $fullPath = $matchedFiles[0];
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        header('HTTP/1.0 404 Not Found');
                        echo 'No se encontró el QR después de regenerarlo: ' . $newFilename;
                        exit;
                    }
                }
            } else {
                header('HTTP/1.0 500 Internal Server Error');
                echo 'Error al regenerar el QR: ' . ($result['error'] ?? 'Error desconocido');
                exit;
            }
        } else {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'No se pudo cargar la aplicación Laravel para regenerar el QR';
            exit;
        }
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo 'No se pudo cargar el autoloader para regenerar el QR';
        exit;
    }
}

// Verificar que el archivo exista
if (!file_exists($fullPath)) {
    header('HTTP/1.0 404 Not Found');
    echo 'Archivo no encontrado: ' . $fullPath;
    exit;
}

// Tamaño del archivo
$fileSize = filesize($fullPath);
if ($fileSize === false) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Error al obtener tamaño del archivo';
    exit;
}

// Determinar el tipo MIME
$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
switch($ext) {
    case 'svg':
        $contentType = 'image/svg+xml';
        break;
    case 'png':
        $contentType = 'image/png';
        break;
    case 'jpg':
    case 'jpeg':
        $contentType = 'image/jpeg';
        break;
    default:
        $contentType = 'application/octet-stream';
}

// Verificar si se solicita visualización o descarga
$action = $_GET['action'] ?? 'download';

// Establecer encabezados según la acción solicitada
if ($action === 'view') {
    // Configurar encabezados para visualizar en el navegador
    header('Content-Type: ' . $contentType);
    header('Cache-Control: max-age=3600, public');
} else {
    // Configurar encabezados para forzar descarga
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="qr_materia_' . $id . '.' . $ext . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
}

// Añadir el tamaño del contenido en todos los casos
header('Content-Length: ' . $fileSize);

// Leer y entregar el archivo directamente
readfile($fullPath);
exit;
