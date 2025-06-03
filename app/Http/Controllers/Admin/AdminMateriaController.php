<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\User;
use App\Models\Aula;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminMateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materias = Materia::with(['profesor', 'aula', 'curso'])->get();
        $profesores = User::where('role_id', 2)->get(); // Asumiendo que role_id=2 son profesores

        return view('admin.materias', compact('materias', 'profesores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'profesor_id' => 'required|exists:users,id',
            'aula' => 'required|string|max:50',
            'horario_ingreso' => 'required',
            'horario_salida' => 'required|after:horario_ingreso',
            'curso' => 'required|string|max:50'
        ]);

        // Buscar o crear el aula
        $aula = Aula::firstOrCreate(['nombre' => $request->aula]);
        
        // Buscar o crear el curso
        $curso = Curso::firstOrCreate(['nombre' => $request->curso]);

        $materia = Materia::create([
            'nombre' => $request->nombre,
            'profesor_id' => $request->profesor_id,
            'aula_id' => $aula->id,
            'horario_ingreso' => $request->horario_ingreso,
            'horario_salida' => $request->horario_salida,
            'curso_id' => $curso->id,
            'token_qr' => Str::random(40),
            'qr_path' => '0' // Inicializar con '0' para cumplir con constraint de base de datos
        ]);

        return redirect()->route('admin.materias')->with('success', 'Materia creada con éxito');
    }    /**
     * Show the form for editing the specified resource.
     */    public function edit($id)
    {
        try {
            $materia = Materia::with(['profesor', 'aula', 'curso'])->findOrFail($id);
            
            // Si es una solicitud AJAX, devolver JSON estructurado
            if (request()->ajax() || request()->wantsJson()) {
                Log::debug('Devolviendo materia por AJAX', ['materia_id' => $id]);
                return response()->json([
                    'success' => true,
                    'id' => $materia->id,
                    'nombre' => $materia->nombre,
                    'profesor_id' => $materia->profesor_id,
                    'aula' => $materia->aula,
                    'curso' => $materia->curso,
                    'horario_ingreso' => $materia->horario_ingreso,
                    'horario_salida' => $materia->horario_salida,
                    'token_qr' => $materia->token_qr,
                    'qr_path' => $materia->qr_path
                ]);
            }
            
            // De lo contrario, devolver vista con datos
            $profesores = User::where('role_id', 2)->get();
            return view('admin.edit_materia', compact('materia', 'profesores'));
        } catch (\Exception $e) {
            Log::error('Error al editar materia', [
                'materia_id' => $id,
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Ocurrió un error al cargar la materia: ' . $e->getMessage());
        }
    }/**
     * Update the specified resource in storage.
     */    public function update(Request $request, $id)
    {
        $materia = Materia::findOrFail($id);
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'profesor_id' => 'required|exists:users,id',
            'aula' => 'required|string|max:50',
            'horario_ingreso' => 'required',
            'horario_salida' => 'required|after:horario_ingreso',
            'curso' => 'required|string|max:50'
        ]);

        // Buscar o crear el aula
        $aula = Aula::firstOrCreate(['nombre' => $request->aula]);
        
        // Buscar o crear el curso
        $curso = Curso::firstOrCreate(['nombre' => $request->curso]);
        
        $materia->update([
            'nombre' => $request->nombre,
            'profesor_id' => $request->profesor_id,
            'aula_id' => $aula->id,
            'horario_ingreso' => $request->horario_ingreso,
            'horario_salida' => $request->horario_salida,
            'curso_id' => $curso->id
        ]);
        Log::debug('Materia actualizada correctamente', [
            'materia' => $materia->toArray()
        ]);
        
        // Si es una solicitud AJAX, devolver JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Materia actualizada con éxito',
                'materia' => $materia->fresh()->load(['profesor', 'aula', 'curso'])
            ]);
        }
        
        return redirect()->route('admin.materias')->with('success', 'Materia actualizada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $materia = Materia::findOrFail($id);
          // Si hay un QR generado, eliminarlo de ambas ubicaciones posibles
        if ($materia->qr_path) {
            // Intentar eliminar de la ubicación antigua (storage)
            if (Storage::exists('public/' . $materia->qr_path)) {
                Storage::delete('public/' . $materia->qr_path);
                Log::info('QR eliminado de storage/app/public/', ['path' => $materia->qr_path]);
            }
            
            // Intentar eliminar de la ubicación nueva (public/storage)
            $publicQrPath = public_path('storage/' . $materia->qr_path);
            if (file_exists($publicQrPath)) {
                unlink($publicQrPath);
                Log::info('QR eliminado de public/storage/', ['path' => $publicQrPath]);
            }
        }
        
        $materia->delete();
        
        return redirect()->route('admin.materias')->with('success', 'Materia eliminada con éxito');
    }    /**
     * Generate QR code for a subject.
     */    public function generateQr($id)
    {
        try {
            $materia = Materia::with(['profesor', 'aula', 'curso'])->findOrFail($id);
            
            // Registrar inicio del proceso para diagnóstico
            Log::info('Iniciando generación de QR para materia', [
                'materia_id' => $materia->id,
                'token_qr' => $materia->token_qr
            ]);
              
            // Verificar si ya existe un QR generado
            $hasQrPath = false;
            
            try {
                // Intentar acceder a la propiedad qr_path (podría fallar si la columna no existe)
                $hasQrPath = !empty($materia->qr_path);
                
                Log::info('Verificación de qr_path completada', [
                    'materia_id' => $materia->id,
                    'has_qr_path' => $hasQrPath
                ]);
            } catch (\Exception $e) {
                Log::warning('No se pudo acceder a la propiedad qr_path, posiblemente la columna no existe', [
                    'materia_id' => $materia->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Verificar si existe archivo basado en el token_qr (alternativa)
            $expectedFilename = 'qrcodes/materia_' . $materia->id . '_' . substr($materia->token_qr, 0, 8) . '.svg';
            $fileExists = Storage::exists('public/' . $expectedFilename);
            
            // Si ya existe un QR por cualquier método, devolverlo
            if ($hasQrPath || $fileExists) {
                $qrPath = $hasQrPath ? $materia->qr_path : $expectedFilename;
                
                Log::info('Usando QR existente para materia', [
                    'materia_id' => $materia->id,
                    'qr_path' => $qrPath,
                    'method' => $hasQrPath ? 'database' : 'file_check'
                ]);
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'existing' => true,
                        'qr_url' => asset('storage/' . $qrPath),
                        'qr_data' => [
                            'token_qr' => Str::limit($materia->token_qr, 15),
                            'nombre' => $materia->nombre,
                            'aula' => $materia->aula->nombre ?? 'No especificado',
                            'curso' => $materia->curso->nombre ?? 'No especificado',
                            'horario' => $materia->horario_ingreso . ' - ' . $materia->horario_salida
                        ]
                    ]);
                }
                
                return redirect()->route('admin.materias')->with('info', 'Esta materia ya tiene un código QR generado');
            }
              // Datos para el código QR
            $qrData = [
                'materia_id' => $materia->id,
                'token_qr' => $materia->token_qr,
                'nombre' => $materia->nombre,
                'profesor' => $materia->profesor->name ?? 'Sin asignar',
                'aula' => $materia->aula->nombre ?? 'No especificado',
                'curso' => $materia->curso->nombre ?? 'No especificado',
                'horario' => [
                    'ingreso' => $materia->horario_ingreso,
                    'salida' => $materia->horario_salida
                ]
            ];            // *** GUARDAR DIRECTAMENTE EN public/storage/qrcodes ***
            $publicQrDir = public_path('storage/qrcodes');
            
            // Crear el directorio public/storage/qrcodes si no existe
            if (!file_exists($publicQrDir)) {
                if (mkdir($publicQrDir, 0755, true)) {
                    Log::info("Directorio creado directamente: $publicQrDir");
                } else {
                    Log::error("No se pudo crear el directorio: $publicQrDir");
                    throw new \Exception('No se pudo crear el directorio para QR');
                }
            }
            
            // Asegurarse de que exista el enlace simbólico para acceder a los archivos
            if (!file_exists(public_path('storage'))) {
                Log::info('Creando enlace simbólico para storage');
                \Illuminate\Support\Facades\Artisan::call('storage:link');
            }
            
            // Generar el código QR en formato SVG (no requiere Imagick)
            try {
                Log::info('Generando QR SVG para materia', ['materia_id' => $materia->id]);
                
                $qrcode = QrCode::format('svg')
                                ->size(300)
                                ->errorCorrection('H')
                                ->margin(1)
                                ->generate(json_encode($qrData));
                
                // Generar nombre de archivo del QR con ruta completa
                $qrFilename = 'materia_' . $materia->id . '_' . time() . '.svg';
                $qrPath = $publicQrDir . '/' . $qrFilename;
                
                // Define el filename para la base de datos (ruta relativa desde public/storage)
                $filename = 'qrcodes/' . $qrFilename;
                
                Log::info('Guardando QR directamente en public/storage', [
                    'filename' => $filename, 
                    'qrPath' => $qrPath
                ]);
                
                // GUARDAR DIRECTAMENTE en public/storage/qrcodes
                $saved = file_put_contents($qrPath, $qrcode);
                
                if (!$saved) {
                    throw new \Exception('No se pudo guardar el archivo QR en la ubicación pública');
                }                  // Verificar permisos del archivo
                if (file_exists($qrPath)) {
                    // Ajustar permisos según el sistema operativo
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        // En Windows, usar icacls para dar permisos completos
                        exec('icacls "' . $qrPath . '" /grant Everyone:F', $output, $returnCode);
                        Log::info('Permisos del archivo QR ajustados en Windows', [
                            'path' => $qrPath,
                            'output' => $output,
                            'return_code' => $returnCode
                        ]);
                    } else {
                        // En sistemas Unix, usar chmod
                        chmod($qrPath, 0644);
                        Log::info('Permisos del archivo QR ajustados en Unix', ['path' => $qrPath]);
                    }
                }
                  // Verificar que el archivo se guardó correctamente
                if (!file_exists($qrPath)) {
                    throw new \Exception('Error al guardar el código QR en la ubicación pública');
                }
                
                // Verificar accesibilidad web del archivo
                $qrUrl = asset('storage/' . $filename);
                Log::info('URL del QR generado', ['url' => $qrUrl]);
                
                // Actualizar la materia con la ruta del QR
                try {
                    Log::info('Actualizando materia con ruta de QR', ['qr_path' => $filename]);
                    $materia->qr_path = $filename;
                    $materia->save();
                    
                    Log::info('Materia actualizada correctamente con qr_path');
                } catch (\Exception $e) {
                    // Si falla al guardar por columna inexistente, continuar sin guardar qr_path
                    Log::warning('No se pudo guardar qr_path en la base de datos, pero el QR se generó correctamente', [
                        'error' => $e->getMessage()
                    ]);
                    // No propagar el error para que el QR aún sea visible
                }
                  Log::info('QR generado exitosamente para materia', [
                    'materia_id' => $materia->id,
                    'qr_path' => $filename
                ]);
            } catch (\Exception $e) {
                Log::error('Error al generar QR SVG', [
                    'materia_id' => $materia->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Re-lanzar para manejo en catch superior
            }
        
        // Si es una solicitud AJAX, devolver datos del QR
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'existing' => false,
                'qr_url' => asset('storage/' . $filename),
                'qr_data' => [
                    'token_qr' => Str::limit($materia->token_qr, 15),
                    'nombre' => $materia->nombre,
                    'aula' => $materia->aula->nombre ?? 'No especificado',
                    'curso' => $materia->curso->nombre ?? 'No especificado',
                    'horario' => $materia->horario_ingreso . ' - ' . $materia->horario_salida
                ]
            ]);
        }
        
        return redirect()->route('admin.materias')->with('success', 'Código QR generado con éxito');
          } catch (\Exception $e) {
            Log::error('Error al generar QR para materia', [
                'materia_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Intentar método alternativo y simplificado para generar QR
            try {
                Log::info('Intentando método alternativo para generar QR');
                
                // Cargar QrGenerator.php si no está cargado
                if (!function_exists('App\Http\Controllers\Admin\generate_qr')) {
                    require_once app_path('Http/Controllers/Admin/QrGenerator.php');
                }
                
                // Generar QR usando método alternativo
                $result = \App\Http\Controllers\Admin\generate_qr($id);
                
                if ($result['success']) {
                    Log::info('QR generado correctamente usando método alternativo', [
                        'materia_id' => $id,
                        'qr_url' => $result['qr_url'] ?? 'No disponible'
                    ]);
                    
                    if (request()->ajax()) {
                        return response()->json($result);
                    }
                    
                    return redirect()->route('admin.materias')
                        ->with('success', 'Código QR generado con método alternativo');
                }
            } catch (\Exception $backupError) {
                Log::error('También falló el método alternativo para generar QR', [
                    'materia_id' => $id,
                    'error' => $backupError->getMessage()
                ]);
                // Continuar con el manejo normal de errores
            }
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al generar el código QR: ' . $e->getMessage(),
                    'details' => [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]
                ], 500);
            }
            
            return redirect()->route('admin.materias')->with('error', 'Error al generar el código QR: ' . $e->getMessage());
        }
    }
}
