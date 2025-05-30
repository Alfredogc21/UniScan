<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MateriaController extends Controller
{
    public function index()
    {
        $materias = Materia::with(['profesor', 'aula', 'curso'])->orderBy('id', 'desc')->get();
        $profesores = User::where('role_id', 2)->where('estado_id', 1)->get(); // Solo profesores activos
        
        return view('admin.materias', compact('materias', 'profesores'));
    }    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'aula' => 'required|string|max:50',
            'horario_ingreso' => 'required|date_format:H:i',
            'horario_salida' => 'required|date_format:H:i|after:horario_ingreso',
            'curso' => 'required|string|max:50',
            'profesor_id' => 'required|exists:users,id'
        ]);

        // Buscar o crear aula
        $aula = \App\Models\Aula::firstOrCreate(['nombre' => $request->aula]);
        
        // Buscar o crear curso
        $curso = \App\Models\Curso::firstOrCreate(['nombre' => $request->curso]);
        
        $materia = Materia::create([
            'nombre' => $request->nombre,
            'aula_id' => $aula->id,
            'horario_ingreso' => $request->horario_ingreso,
            'horario_salida' => $request->horario_salida,
            'curso_id' => $curso->id,
            'profesor_id' => $request->profesor_id,
            'token_qr' => Str::random(40)
        ]);

        return redirect()->route('admin.materias')->with('success', 'Materia creada correctamente');
    }    public function edit($id)
    {
        $materia = Materia::with(['aula', 'curso'])->findOrFail($id);
        
        // Add aula and curso values for form fields
        if ($materia->aula) {
            $materia->aula_nombre = $materia->aula->nombre;
        }
        
        if ($materia->curso) {
            $materia->curso_nombre = $materia->curso->nombre;
        }
        
        return response()->json($materia);
    }public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'aula' => 'required|string|max:50',
            'horario_ingreso' => 'required|date_format:H:i',
            'horario_salida' => 'required|date_format:H:i|after:horario_ingreso',
            'curso' => 'required|string|max:50',
            'profesor_id' => 'required|exists:users,id'
        ]);

        // Buscar o crear aula
        $aula = \App\Models\Aula::firstOrCreate(['nombre' => $request->aula]);
        
        // Buscar o crear curso
        $curso = \App\Models\Curso::firstOrCreate(['nombre' => $request->curso]);
        
        $materia = Materia::findOrFail($id);
        $materia->update([
            'nombre' => $request->nombre,
            'aula_id' => $aula->id,
            'horario_ingreso' => $request->horario_ingreso,
            'horario_salida' => $request->horario_salida,
            'curso_id' => $curso->id,
            'profesor_id' => $request->profesor_id
        ]);

        return redirect()->route('admin.materias')->with('success', 'Materia actualizada correctamente');
    }

    public function destroy($id)
    {
        $materia = Materia::findOrFail($id);
        
        // Eliminar archivo QR si existe de ambas ubicaciones posibles
        if ($materia->qr_path) {
            // Intentar eliminar de la ubicación antigua (storage)
            if (Storage::disk('public')->exists($materia->qr_path)) {
                Storage::disk('public')->delete($materia->qr_path);
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
        
        return redirect()->route('admin.materias')->with('success', 'Materia eliminada correctamente');
    }

    public function generateQr($id)
    {
        $materia = Materia::findOrFail($id);
        
        try {
            // *** GUARDAR DIRECTAMENTE EN public/storage/qrcodes ***
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
              // Generar QR con todos los datos incluyendo aula y curso
            $qrData = json_encode([
                'token_qr' => $materia->token_qr,
                'materia_id' => $materia->id,
                'nombre' => $materia->nombre,
                'aula' => $materia->aula ? $materia->aula->nombre : '',
                'curso' => $materia->curso ? $materia->curso->nombre : '',
                'horario_ingreso' => $materia->horario_ingreso,
                'horario_salida' => $materia->horario_salida,
                'profesor_id' => $materia->profesor_id
            ]);
            
            $fileName = 'qr_materia_' . $materia->id . '_' . time() . '.png';
            $qrPath = $publicQrDir . '/' . $fileName;
            $path = 'qrcodes/' . $fileName;
            
            $qrCode = QrCode::format('png')
                ->size(300)
                ->margin(2)
                ->generate($qrData);
            
            // GUARDAR DIRECTAMENTE en public/storage/qrcodes
            $saved = file_put_contents($qrPath, $qrCode);
            
            if (!$saved) {
                throw new \Exception('No se pudo guardar el archivo QR en la ubicación pública');
            }
            
            // Verificar permisos del archivo en Windows
            if (file_exists($qrPath) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                exec('icacls "' . $qrPath . '" /grant Everyone:F', $output, $returnCode);
            }
            
            // Guardar la ruta en la base de datos
            $materia->update(['qr_path' => $path]);            return response()->json([
                'success' => true,
                'message' => 'Código QR generado correctamente',
                'qr_url' => asset('storage/' . $path),
                'qr_data' => [
                    'token_qr' => $materia->token_qr,
                    'nombre' => $materia->nombre,
                    'aula' => $materia->aula ? $materia->aula->nombre : '',
                    'curso' => $materia->curso ? $materia->curso->nombre : '',
                    'horario' => $materia->horario_ingreso . ' - ' . $materia->horario_salida
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el código QR: ' . $e->getMessage()
            ], 500);
        }
    }
}