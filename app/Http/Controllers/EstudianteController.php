<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Materia;
use App\Models\Asistencia;
use App\Models\TipoAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EstudianteController extends Controller
{
    /**
     * Mostrar el dashboard del estudiante
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 3) {
            abort(403, 'Acceso no autorizado');
        }

        $estudianteId = $user->id;

        // Obtener las materias en las que está matriculado el estudiante
        $materiasMatriculadas = Materia::whereHas('asistencias', function ($query) use ($estudianteId) {
            $query->where('alumno_id', $estudianteId);
        })->distinct()->count();

        // Contar asistencias del estudiante hoy
        $asistenciasHoy = Asistencia::where('alumno_id', $estudianteId)
            ->whereDate('fecha_hora', Carbon::today())
            ->count();

        // Obtener el total de asistencias del estudiante
        $totalAsistencias = Asistencia::where('alumno_id', $estudianteId)->count();

        // Calcular porcentaje de asistencia del estudiante
        $asistenciasPresente = Asistencia::where('alumno_id', $estudianteId)
            ->where('tipo_asistencia_id', 1) // Presente
            ->count();

        $porcentajeAsistencia = 0;
        if ($totalAsistencias > 0) {
            $porcentajeAsistencia = round(($asistenciasPresente / $totalAsistencias) * 100);
        }

        // Obtener datos para los gráficos
        $datosGraficoSemanal = $this->obtenerDatosAsistenciaSemanal($estudianteId);
        $datosGraficoDistribucion = $this->obtenerDatosDistribucionAsistencias($estudianteId);

        // Obtener asistencias recientes del estudiante
        $asistenciasRecientes = Asistencia::with(['materia', 'tipoAsistencia'])
            ->where('alumno_id', $estudianteId)
            ->orderBy('fecha_hora', 'desc')
            ->limit(10)
            ->get();

        return view('estudiante.dashboard', compact(
            'materiasMatriculadas',
            'asistenciasHoy',
            'totalAsistencias',
            'porcentajeAsistencia',
            'datosGraficoSemanal',
            'datosGraficoDistribucion',
            'asistenciasRecientes'
        ));
    }

    /**
     * Obtener datos de asistencia para el gráfico semanal del estudiante
     */
    private function obtenerDatosAsistenciaSemanal($estudianteId)
    {
        // Obtener los últimos 7 días
        $inicioSemana = Carbon::now()->startOfWeek();
        $labels = [];
        $datos = [];

        for ($i = 0; $i < 7; $i++) {
            $fecha = $inicioSemana->copy()->addDays($i);
            $labels[] = $fecha->format('D');

            // Contar asistencias del estudiante en ese día
            $count = Asistencia::where('alumno_id', $estudianteId)
                ->whereDate('fecha_hora', $fecha)
                ->count();

            $datos[] = $count;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Asistencias',
                'data' => $datos,
                'backgroundColor' => 'rgba(76, 175, 80, 0.2)',
                'borderColor' => '#4caf50',
                'borderWidth' => 2,
                'fill' => true
            ]]
        ];
    }

    /**
     * Obtener datos de distribución de asistencias del estudiante
     */
    private function obtenerDatosDistribucionAsistencias($estudianteId)
    {
        $tiposAsistencia = TipoAsistencia::all();
        $labels = [];
        $datos = [];
        $colores = [
            '#4caf50', // Verde para Presente
            '#f44336', // Rojo para Ausente
            '#ff9800', // Naranja para Justificado
            '#2196f3'  // Azul para otros
        ];

        foreach ($tiposAsistencia as $index => $tipo) {
            $count = Asistencia::where('alumno_id', $estudianteId)
                ->where('tipo_asistencia_id', $tipo->id)
                ->count();

            if ($count > 0) {
                $labels[] = $tipo->descripcion;
                $datos[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $datos,
                'backgroundColor' => array_slice($colores, 0, count($datos)),
                'borderWidth' => 2
            ]]
        ];
    }

    /**
     * Mostrar el perfil del estudiante
     */
    public function profile()
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 3) {
            abort(403, 'Acceso no autorizado');
        }

        return view('estudiante.profile', compact('user'));
    }

    /**
     * Actualizar el perfil del estudiante
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 3) {
            abort(403, 'Acceso no autorizado');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Obtener el modelo completo desde la base de datos
        $userModel = User::find($user->id);

        if (!$userModel) {
            abort(404, 'Usuario no encontrado');
        }

        $userModel->name = $validated['name'];
        $userModel->email = $validated['email'];

        if (!empty($validated['password'])) {
            $userModel->password = bcrypt($validated['password']);
        }

        $userModel->save();

        return redirect()->route('estudiante.profile')->with('status', 'Perfil actualizado correctamente');
    }

    /**
     * Mostrar las materias del estudiante
     */
    public function materias()
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 3) {
            abort(403, 'Acceso no autorizado');
        }

        // Obtener las materias en las que está matriculado el estudiante
        $materias = Materia::with(['profesor', 'aula', 'curso'])
            ->whereHas('asistencias', function ($query) use ($user) {
                $query->where('alumno_id', $user->id);
            })
            ->distinct()
            ->get();

        // Agregar estadísticas de asistencia para cada materia
        foreach ($materias as $materia) {
            $totalAsistencias = Asistencia::where('alumno_id', $user->id)
                ->where('materia_id', $materia->id)
                ->count();

            $asistenciasPresente = Asistencia::where('alumno_id', $user->id)
                ->where('materia_id', $materia->id)
                ->where('tipo_asistencia_id', 1)
                ->count();

            $materia->total_asistencias = $totalAsistencias;
            $materia->porcentaje_asistencia = $totalAsistencias > 0
                ? round(($asistenciasPresente / $totalAsistencias) * 100)
                : 0;
        }

        return view('estudiante.materias', compact('materias'));
    }

    /**
     * Mostrar las asistencias del estudiante
     */
    public function asistencias(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 3) {
            abort(403, 'Acceso no autorizado');
        }

        // Obtener las materias del estudiante para filtros
        $materias = Materia::whereHas('asistencias', function ($query) use ($user) {
            $query->where('alumno_id', $user->id);
        })->distinct()->get();

        // Obtener tipos de asistencia para filtros
        $tiposAsistencia = TipoAsistencia::all();

        // Query base para las asistencias del estudiante
        $query = Asistencia::with(['materia', 'tipoAsistencia'])
            ->where('alumno_id', $user->id);

        // Aplicar filtros si están presentes
        if ($request->filled('materia_id')) {
            $query->where('materia_id', $request->materia_id);
        }

        if ($request->filled('tipo_asistencia_id')) {
            $query->where('tipo_asistencia_id', $request->tipo_asistencia_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }
        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        // Calcular estadísticas para las tarjetas de resumen
        $totalAsistencias = Asistencia::where('alumno_id', $user->id)->count();
        $totalPresentes = Asistencia::where('alumno_id', $user->id)->where('tipo_asistencia_id', 1)->count();
        $totalAusentes = Asistencia::where('alumno_id', $user->id)->where('tipo_asistencia_id', 2)->count();
        $totalJustificados = Asistencia::where('alumno_id', $user->id)->where('tipo_asistencia_id', 3)->count();

        $porcentajeAsistencia = $totalAsistencias > 0 ?
            round(($totalPresentes / $totalAsistencias) * 100) : 0;

        return view('estudiante.asistencias', compact(
            'asistencias',
            'materias',
            'tiposAsistencia',
            'totalAsistencias',
            'totalPresentes',
            'totalAusentes',
            'totalJustificados',
            'porcentajeAsistencia'
        ));
    }

    /**
     * Obtener datos para gráficos (AJAX)
     */
    public function getDatosSemanal()
    {
        $estudianteId = Auth::id();
        $datos = $this->obtenerDatosAsistenciaSemanal($estudianteId);
        return response()->json($datos);
    }

    public function getDatosDistribucion()
    {
        $estudianteId = Auth::id();
        $datos = $this->obtenerDatosDistribucionAsistencias($estudianteId);
        return response()->json($datos);
    }

    /**
     * Mostrar la vista del escáner QR
     */
    public function asistenciaQR()
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 3) {
            abort(403, 'Acceso no autorizado');
        }

        return view('estudiante.asistencia-qr');
    }

    /**
     * Procesar el escaneo del código QR y registrar asistencia
     */
    public function procesarQR(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 3) {
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }        $validated = $request->validate([
            'token_qr' => 'required|string'
        ]);

        try {
            // Registrar el QR recibido para diagnóstico
            Log::info('QR recibido:', [
                'qr_data' => $validated['token_qr'],
                'estudiante_id' => $user->id
            ]);

            // Decodificar el QR si es un JSON
            $qrData = $validated['token_qr'];
            $tokenQr = null;
            $materiaId = null;
            $materiaDirecta = null;
            
            // Verificar si el QR contiene JSON
            if (is_string($qrData) && (str_starts_with(trim($qrData), '{') || str_starts_with(trim($qrData), '['))) {
                // Intentar decodificar el JSON
                $decodedQr = json_decode($qrData, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::info('JSON decodificado correctamente', ['decoded' => $decodedQr]);
                    
                    // Extraer información del JSON
                    if (isset($decodedQr['token_qr'])) {
                        $tokenQr = $decodedQr['token_qr'];
                        $materiaId = $decodedQr['materia_id'] ?? null;
                    } else {
                        // JSON válido pero sin token_qr
                        $tokenQr = $qrData; // Usar el JSON completo como token
                        Log::warning('JSON sin token_qr', ['qr_data' => $qrData]);
                    }
                } else {
                    // JSON inválido
                    Log::warning('Error al decodificar JSON', [
                        'error' => json_last_error_msg(),
                        'qr_data' => $qrData
                    ]);
                    $tokenQr = $qrData;
                }
            } else {
                // No parece JSON, tratar como token directo
                $tokenQr = $qrData;
                Log::info('QR no es JSON, usando como token directo', ['token' => $tokenQr]);
            }            // Buscar la materia por el token QR o por ID si está disponible
            $materia = null;
            
            // Estrategia 1: Por ID y token
            if ($materiaId) {
                $materia = Materia::with(['aula', 'curso', 'profesor'])
                    ->where('id', $materiaId)
                    ->where('token_qr', $tokenQr)
                    ->first();
                
                if ($materia) {
                    Log::info('Materia encontrada por ID y token', ['id' => $materiaId, 'token' => $tokenQr]);
                } else {
                    Log::warning('No se encontró materia por ID y token', ['id' => $materiaId, 'token' => $tokenQr]);
                }
            }
            
            // Estrategia 2: Solo por token si aún no hay materia
            if (!$materia) {
                $materia = Materia::with(['aula', 'curso', 'profesor'])
                    ->where('token_qr', $tokenQr)
                    ->first();
                    
                if ($materia) {
                    Log::info('Materia encontrada solo por token', ['token' => $tokenQr]);
                } else {
                    Log::warning('No se encontró materia por token', ['token' => $tokenQr]);
                }
            }
            
            // Estrategia 3: Solo por ID si aún no hay materia
            if (!$materia && $materiaId) {
                $materia = Materia::with(['aula', 'curso', 'profesor'])
                    ->where('id', $materiaId)
                    ->first();
                    
                if ($materia) {
                    Log::info('Materia encontrada solo por ID', ['id' => $materiaId]);
                } else {
                    Log::warning('No se encontró materia por ID', ['id' => $materiaId]);
                }
            }
            
            // Si todas las estrategias fallan
            if (!$materia) {
                // Para diagnóstico, obtener todas las materias
                $todasMaterias = Materia::all(['id', 'token_qr', 'nombre']);
                Log::warning('Materias existentes:', ['materias' => $todasMaterias->toArray()]);
                
                return response()->json([
                    'error' => 'Código QR inválido o no encontrado',
                    'debug_info' => 'Revisa los logs para más detalles'
                ], 404);
            }

            $now = Carbon::now();
            $hoy = $now->toDateString();
            $horaActual = $now->toTimeString();

            // Verificar si el estudiante ya tiene asistencia registrada hoy para esta materia
            $asistenciaExistente = Asistencia::where('alumno_id', $user->id)
                ->where('materia_id', $materia->id)
                ->whereDate('fecha_hora', $hoy)
                ->first();

            if ($asistenciaExistente) {
                return response()->json([
                    'error' => 'Ya tienes asistencia registrada para esta materia hoy',
                    'asistencia_existente' => true
                ], 422);
            }            // Verificar si está dentro del horario de la materia (con 15 minutos de tolerancia)
            $horarioIngreso = Carbon::createFromFormat('H:i:s', $materia->horario_ingreso);
            $horarioSalida = Carbon::createFromFormat('H:i:s', $materia->horario_salida);
            $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActual);

            // Agregar 15 minutos de tolerancia después del horario de ingreso
            $horarioIngresoConTolerancia = $horarioIngreso->copy()->addMinutes(15);

            $dentroDelHorario = $horaActualCarbon->between($horarioIngreso, $horarioSalida) ||
                $horaActualCarbon->between($horarioIngreso, $horarioIngresoConTolerancia);

            // Determinar el tipo de asistencia
            $tipoAsistenciaId = 1; // Presente por defecto (siempre 1 como solicitado)
            $justificacion = null;

            // === MODIFICADO: Permitir registro de asistencia independientemente de la hora ===
            if (!$dentroDelHorario) {
                // Registrar el horario incorrecto pero permitir la asistencia
                Log::info('Asistencia fuera de horario permitida', [
                    'materia' => $materia->nombre,
                    'hora_actual' => $horaActual,
                    'horario_materia' => [
                        'ingreso' => $materia->horario_ingreso,
                        'salida' => $materia->horario_salida,
                    ]
                ]);
                
                // Si está fuera del horario, pero dentro de los 30 minutos de tolerancia
                $horarioTardanza = $horarioIngreso->copy()->addMinutes(30);
                if ($horaActualCarbon->between($horarioIngresoConTolerancia, $horarioTardanza)) {
                    $justificacion = 'Llegada tardía';
                } else {
                    $justificacion = 'Asistencia registrada fuera de horario';
                }
                
                // El tipo de asistencia siempre será 1 (Presente) como solicitado
                // Esto es un cambio respecto a la versión anterior que bloqueaba el registro
            }

            // Crear el registro de asistencia
            $asistencia = Asistencia::create([
                'materia_id' => $materia->id,
                'alumno_id' => $user->id,
                'profesor_id' => $materia->profesor_id,
                'fecha_hora' => $now,
                'tipo_asistencia_id' => $tipoAsistenciaId,
                'justificacion' => $justificacion
            ]);
            Log::info('Asistencia registrada via QR', [
                'estudiante_id' => $user->id,
                'materia_id' => $materia->id,
                'fecha_hora' => $now,
                'token_qr' => $tokenQr
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asistencia registrada correctamente',
                'data' => [
                    'materia' => $materia->nombre,
                    'fecha_hora' => $now->format('d/m/Y H:i:s'),
                    'tipo_asistencia' => $justificacion ? 'Presente (Tarde)' : 'Presente',
                    'aula' => $materia->aula->nombre ?? 'N/A',
                    'curso' => $materia->curso->nombre ?? 'N/A',
                    'profesor' => $materia->profesor->name ?? 'N/A'
                ]
            ], 200);        } catch (\Exception $e) {
            // Registro detallado del error
            Log::error('Error al procesar QR de asistencia', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'estudiante_id' => $user->id,
                'qr_data' => $validated['token_qr'],
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Mensaje de error más descriptivo en entorno de desarrollo
            if (config('app.debug')) {
                return response()->json([
                    'error' => 'Error al procesar el código QR',
                    'debug_info' => [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ], 500);
            } else {
                // Mensaje genérico en producción
                return response()->json([
                    'error' => 'Error interno del servidor. Inténtalo de nuevo.'
                ], 500);
            }
        }
    }
}
