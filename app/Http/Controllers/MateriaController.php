<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Asistencia;
use Illuminate\Http\Request; // ¡Faltaba este import!
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MateriaController extends Controller // ¡Faltaba la clase!
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombreMateria' => 'required|string|max:100',
            'aula' => 'required|string|max:50',
            'horaIngreso' => 'required|date_format:H:i',
            'horaSalida' => 'required|date_format:H:i|after:horaIngreso',
            'curso' => 'required|string|max:50'
        ]);

        $materia = Materia::create([
            'profesor_id' => auth()->id(),
            'nombre' => $request->nombreMateria,
            'aula' => $request->aula,
            'horario_ingreso' => $request->horaIngreso,
            'horario_salida' => $request->horaSalida,
            'curso' => $request->curso,
            'token_qr' => Str::random(40)
        ]);

        return response()->json([
            'success' => true,
            'materia' => $materia
        ]);
    }

    public function registrarAsistencia(Request $request)
    {
        $data = $request->validate([
            'token_qr' => 'required|string',
            'alumno_id' => 'required|integer'
        ]);

        $materia = Materia::where('token_qr', $data['token_qr'])->firstOrFail();

        $asistenciaExistente = Asistencia::where([
            'materia_id' => $materia->id,
            'alumno_id' => $data['alumno_id'],
            'fecha' => now()->toDateString()
        ])->exists();

        if ($asistenciaExistente) {
            return response()->json(['error' => 'Ya registraste asistencia hoy'], 400);
        }

        Asistencia::create([
            'materia_id' => $materia->id,
            'alumno_id' => $data['alumno_id'],
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString()
        ]);

        return response()->json(['success' => true]);
    }
}