<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Clase;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    // Solo accesible para usuarios autenticados
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Mostrar todas las asistencias (solo admin o profesor)
    public function index()
    {
        $usuario = Auth::user();

        if ($usuario->rol_id == 1 || $usuario->rol_id == 2) {
            $asistencias = Asistencia::with('estudiante', 'clase')->latest()->get();
            return view('asistencias.index', compact('asistencias'));
        }

        abort(403, 'No autorizado');
    }

    // Mostrar formulario para crear una nueva asistencia (opcional)
    public function create()
    {
        $clases = Clase::all();
        $estudiantes = Estudiante::all();
        return view('asistencias.create', compact('clases', 'estudiantes'));
    }

    // Guardar asistencia cuando se escanea el QR o manualmente
    public function store(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'clase_id' => 'required|exists:clases,id',
        ]);

        // Evita duplicados en el mismo día
        $existe = Asistencia::where('estudiante_id', $request->estudiante_id)
            ->where('clase_id', $request->clase_id)
            ->whereDate('fecha', Carbon::today())
            ->first();

        if ($existe) {
            return redirect()->back()->with('error', 'Ya se registró la asistencia hoy para este estudiante.');
        }

        Asistencia::create([
            'estudiante_id' => $request->estudiante_id,
            'clase_id' => $request->clase_id,
            'fecha' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Asistencia registrada correctamente.');
    }

    // Mostrar una asistencia específica
    public function show($id)
    {
        $asistencia = Asistencia::with('estudiante', 'clase')->findOrFail($id);
        return view('asistencias.show', compact('asistencia'));
    }

    // Mostrar formulario para editar una asistencia
    public function edit($id)
    {
        $asistencia = Asistencia::findOrFail($id);
        $clases = Clase::all();
        $estudiantes = Estudiante::all();
        return view('asistencias.edit', compact('asistencia', 'clases', 'estudiantes'));
    }

    // Actualizar asistencia
    public function update(Request $request, $id)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'clase_id' => 'required|exists:clases,id',
        ]);

        $asistencia = Asistencia::findOrFail($id);
        $asistencia->update([
            'estudiante_id' => $request->estudiante_id,
            'clase_id' => $request->clase_id,
            'fecha' => $request->fecha ?? Carbon::now(),
        ]);

        return redirect()->route('asistencias.index')->with('success', 'Asistencia actualizada correctamente.');
    }

    // Eliminar asistencia
    public function destroy($id)
    {
        $asistencia = Asistencia::findOrFail($id);
        $asistencia->delete();

        return redirect()->back()->with('success', 'Asistencia eliminada.');
    }
}
