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
            'token_qr' => Str::random(40)
        ]);

        return redirect()->route('admin.materias')->with('success', 'Materia creada con éxito');
    }    /**
     * Show the form for editing the specified resource.
     */    public function edit($id)
    {
        try {
            $materia = Materia::with(['profesor', 'aula', 'curso'])->findOrFail($id);
            
            // Si es una solicitud AJAX, devolver JSON
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($materia);
            }
            
            // De lo contrario, devolver vista con datos
            $profesores = User::where('role_id', 2)->get();
            return view('admin.edit_materia', compact('materia', 'profesores'));
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
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
        
        // Si hay un QR generado, eliminarlo
        if ($materia->qr_path) {
            Storage::delete('public/' . $materia->qr_path);
        }
        
        $materia->delete();
        
        return redirect()->route('admin.materias')->with('success', 'Materia eliminada con éxito');
    }

    /**
     * Generate QR code for a subject.
     */
    public function generateQr($id)
    {
        $materia = Materia::with(['profesor', 'aula', 'curso'])->findOrFail($id);
        
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
        ];
        
        // Generar el código QR
        $qrcode = QrCode::format('png')
                        ->size(300)
                        ->errorCorrection('H')
                        ->margin(1)
                        ->generate(json_encode($qrData));
        
        // Guardar el QR
        $filename = 'qrcodes/materia_' . $materia->id . '_' . time() . '.png';
        Storage::put('public/' . $filename, $qrcode);
        
        // Actualizar la materia con la ruta del QR
        $materia->qr_path = $filename;
        $materia->save();
        
        // Si es una solicitud AJAX, devolver datos del QR
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
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
    }
}
