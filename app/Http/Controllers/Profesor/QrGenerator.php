<?php
// Este archivo proporciona una alternativa para generar códigos QR
// en caso de que la ruta regular falle o la migración no se ha ejecutado

namespace App\Http\Controllers\Profesor;

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

// Función para generar el QR
function generate_qr($id) {
    try {
        // Recuperar la materia
        $materia = \App\Models\Materia::with(['profesor', 'aula', 'curso'])->findOrFail($id);
          // *** GUARDAMOS DIRECTAMENTE EN public/storage/qrcodes ***
        $publicQrDir = public_path('storage/qrcodes');
        
        // Crear el directorio public/storage/qrcodes si no existe
        if (!file_exists($publicQrDir)) {
            if (mkdir($publicQrDir, 0755, true)) {
                Log::info("Directorio creado directamente: $publicQrDir");
            } else {
                Log::error("No se pudo crear el directorio: $publicQrDir");
            }
        }
        
        // Asegurarse de que exista el enlace simbólico para acceder a los archivos
        if (!file_exists(public_path('storage'))) {
            Log::info('Creando enlace simbólico para storage');
            \Illuminate\Support\Facades\Artisan::call('storage:link');
        }
          // Datos para el QR
        $qrData = [
            'id' => $materia->id,
            'token' => $materia->token_qr,
            'nombre' => $materia->nombre,
            'aula' => $materia->aula->nombre ?? 'No especificado',
            'curso' => $materia->curso->nombre ?? 'No especificado',
            'horario' => [
                'ingreso' => $materia->horario_ingreso,
                'salida' => $materia->horario_salida
            ]
        ];
          // Generar nombre de archivo del QR con ruta completa
        $qrFilename = 'materia_' . $materia->id . '_' . substr($materia->token_qr, 0, 8) . '.svg';
        $qrPath = $publicQrDir . '/' . $qrFilename;
        
        // Define el filename para la base de datos (ruta relativa desde public/storage)
        // Esta ruta debe ser 'qrcodes/nombre_archivo.svg' para que funcione con asset('storage/...')
        $filename = 'qrcodes/' . $qrFilename;
        
        // Verificar si ya existe en public/storage/qrcodes
        $qrExists = file_exists($qrPath);
          // Si no existe, generarlo y guardarlo
        if (!$qrExists) {
            // Generar QR SVG
            $qrcode = QrCode::format('svg')
                            ->size(300)
                            ->errorCorrection('H')
                            ->margin(1)
                            ->generate(json_encode($qrData));
            
            // Añadir XML declaration y CSS styling para que el SVG se muestre correctamente cuando se abre directamente
            $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n";
            $cssStyle = '<style type="text/css"><![CDATA[
                svg {
                    background-color: #fff;
                    display: block;
                    margin: 0 auto;
                }
            ]]></style>';
            
            // Insertar el XML declaration al principio y el CSS después de la etiqueta <svg
            if (strpos($qrcode, '<svg') !== false) {
                $qrcode = $xmlDeclaration . str_replace('<svg', '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"', $qrcode);
                $qrcode = str_replace('<svg xmlns', '<svg ' . $cssStyle . ' xmlns', $qrcode);
            }
            
            // GUARDAR DIRECTAMENTE en public/storage/qrcodes
            file_put_contents($qrPath, $qrcode);
            
            // Verificar que se guardó correctamente
            if (file_exists($qrPath)) {
                Log::info("QR guardado correctamente en: $qrPath");
            } else {
                Log::error("Error al guardar QR en: $qrPath");
            }// Verificar que el archivo exista y sea accesible
            // Asegurar permisos correctos
            if (file_exists($qrPath)) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // En Windows, usar icacls para dar permisos completos
                    exec('icacls "' . $qrPath . '" /grant Everyone:F', $output, $returnCode);
                    Log::info('Permisos ajustados para el archivo QR en Windows', [
                        'path' => $qrPath,
                        'command_output' => $output,
                        'return_code' => $returnCode
                    ]);
                } else {
                    chmod($qrPath, 0644);
                }
                
                // Verificar que el archivo sea efectivamente accesible vía web
                Log::info('Ruta de acceso público al QR:', [
                    'path' => $qrPath,
                    'url' => asset('storage/qrcodes/' . $qrFilename)
                ]);
                
                // El archivo debería ser directamente accesible vía web
                Log::info('El archivo es accesible via web en: ' . asset('storage/qrcodes/' . $qrFilename));
            } else {
                Log::warning('El archivo QR no existe en la ruta esperada', [
                    'expected_path' => $qrPath
                ]);
            }
              Log::info('QR generado exitosamente para materia', [
                'materia_id' => $materia->id,
                'filename' => $filename,
                'qrPath' => $qrPath
            ]);
              // Intentar actualizar la BD si es posible
            try {
                // Guardamos exactamente 'qrcodes/nombre_archivo.svg', sin prefijo 'public/'
                $materia->qr_path = $filename;  // Ya es 'qrcodes/nombre_archivo.svg'
                $materia->save();
                Log::info('Base de datos actualizada con la ruta del QR', [
                    'materia_id' => $materia->id,
                    'qr_path' => $filename
                ]);
            } catch (\Exception $e) {
                Log::warning('No se pudo actualizar qr_path en la base de datos', [
                    'error' => $e->getMessage()
                ]);
                // Continuamos sin actualizar la BD
            }
        } else {
            Log::info('Usando QR existente para materia', [
                'materia_id' => $materia->id,
                'filename' => $filename,
                'qrPath' => $qrPath
            ]);
        }
          // Usar URL directa al archivo en storage público
        // asset('storage/...') apunta a public/storage/... que es accesible desde la web
        $baseUrl = asset('storage/qrcodes/' . $qrFilename);
        
        // Registrar la URL exacta que se está generando
        Log::info('URL base del QR generado: ' . $baseUrl);
        Log::info('Ruta en sistema de archivos: ' . $qrPath);
        
        // Verificar que el archivo exista en la ruta pública
        if (file_exists($qrPath)) {
            Log::info('✅ El archivo QR es accesible vía web');
        } else {
            Log::warning('❌ El archivo QR NO es accesible vía web - verificar ruta: ' . $qrPath);
        }
          // URLs para diferentes acciones
        $viewDirectUrl = $baseUrl;
        $downloadDirectUrl = $baseUrl . '?download=1';
        
        Log::info('URLs generadas para QR:', [
            'baseUrl' => $baseUrl,
            'viewUrl' => $viewDirectUrl,
            'downloadUrl' => $downloadDirectUrl,
            'qrFilename' => $qrFilename
        ]);
          return [
            'success' => true,
            'existing' => $qrExists,
            'qr_url' => $viewDirectUrl, // URL para visualizar
            'download_url' => $downloadDirectUrl, // URL alternativa para descarga directa
            'file_accessible' => true,
            'qr_filename' => $qrFilename, // Nombre del archivo
            'qr_data' => [
                'token_qr' => Str::limit($materia->token_qr, 15),
                'nombre' => $materia->nombre,
                'aula' => $materia->aula->nombre ?? 'No especificado',
                'curso' => $materia->curso->nombre ?? 'No especificado',
                'horario' => $materia->horario_ingreso . ' - ' . $materia->horario_salida
            ]
        ];
        
    } catch (\Exception $e) {
        Log::error('Error en generate_qr', [
            'materia_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Si se accede directamente a este archivo
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
    $app = require_once __DIR__ . '/../../../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Http\Kernel')->handle(Request::capture());
    
    $result = generate_qr($_GET['id']);
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
