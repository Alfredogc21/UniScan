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

class ProfesorController extends Controller
{    /**
     * Mostrar el dashboard del profesor
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 2) {
            abort(403, 'Acceso no autorizado');
        }

        // Obtener ID del profesor actual
        $profesorId = $user->id;        // Contar usuarios (alumnos) matriculados en las materias del profesor
        $totalUsuarios = User::whereHas('asistencias', function ($query) use ($profesorId) {
            $query->where(function ($q) use ($profesorId) {
                // Buscar por la relación con materias
                $q->whereHas('materia', function ($subq) use ($profesorId) {
                    $subq->where('profesor_id', $profesorId);
                })
                // O directamente por el profesor_id en asistencias
                ->orWhere('profesor_id', $profesorId);
            });        })->where('role_id', 3)->distinct()->count();

        // Contar asistencias registradas hoy en las materias del profesor
        $asistenciasHoy = Asistencia::whereDate('fecha_hora', Carbon::today())
            ->whereHas('materia', function ($query) use ($profesorId) {
                $query->where('profesor_id', $profesorId);
            })->count();

        // Contar materias del profesor
        $materiasActivas = Materia::where('profesor_id', $profesorId)->count();

        // Calcular porcentaje de asistencia para las materias del profesor
        // Obtenemos el ID del tipo "Presente" directamente de la base de datos
        $tipoPresente = DB::table('tipo_asistencia')
            ->where('descripcion', 'Presente')
            ->orWhere('descripcion', 'A tiempo')
            ->first();
            
        $tipoPresente_id = $tipoPresente ? $tipoPresente->id : 1;
        
        $estadisticas = DB::table('asistencias')
            ->join('materias', 'asistencias.materia_id', '=', 'materias.id')
            ->where('materias.profesor_id', $profesorId)
            ->selectRaw('COUNT(*) as totalAsistencias')
            ->selectRaw('SUM(CASE WHEN tipo_asistencia_id = ? THEN 1 ELSE 0 END) as totalPresentes', [$tipoPresente_id])
            ->first();
            
        $porcentajeAsistencia = 0;
        if ($estadisticas->totalAsistencias > 0) {
            $porcentajeAsistencia = round(($estadisticas->totalPresentes / $estadisticas->totalAsistencias) * 100);
        }

        // Obtener datos para los gráficos
        $datosGraficoSemanal = $this->obtenerDatosAsistenciaSemanal($profesorId);
        $datosGraficoDistribucion = $this->obtenerDatosDistribucionAsistencias($profesorId);

        // Obtener asistencias recientes para la tabla
        $asistenciasRecientes = Asistencia::with(['materia', 'alumno', 'tipoAsistencia'])
            ->whereHas('materia', function ($query) use ($profesorId) {
                $query->where('profesor_id', $profesorId);
            })
            ->orderBy('fecha_hora', 'desc')
            ->limit(5)
            ->get();

        return view('profesor.dashboard', compact(
            'totalUsuarios',
            'asistenciasHoy',
            'materiasActivas',
            'porcentajeAsistencia',
            'datosGraficoSemanal',
            'datosGraficoDistribucion',
            'asistenciasRecientes'
        ));
    }

    /**
     * Mostrar la lista de alumnos del profesor
     */    public function alumnos()
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            abort(403, 'Acceso no autorizado');
        }
        
        // Obtener alumnos que están inscritos en materias del profesor
        $alumnos = User::where('role_id', 3)
            ->whereHas('asistencias', function ($query) use ($user) {
                $query->whereHas('materia', function ($q) use ($user) {
                    $q->where('profesor_id', $user->id);
                });
            })
            ->get();
        
        return view('profesor.alumnos', compact('alumnos'));
    }

    /**
     * Mostrar las materias del profesor
     */    public function materias()
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            abort(403, 'Acceso no autorizado');
        }
        
        // Obtener todas las materias del profesor
        $materias = Materia::where('profesor_id', $user->id)
            ->orderBy('nombre')
            ->get();
        
        return view('profesor.materias', compact('materias'));
    }

    /**
     * Mostrar las asistencias de las materias del profesor
     */    public function asistencias()
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            abort(403, 'Acceso no autorizado');
        }
        
        // Obtener materias del profesor para el filtro
        $materias = Materia::where('profesor_id', $user->id)->get();
        
        // Obtener tipos de asistencia para el filtro
        $tiposAsistencia = TipoAsistencia::all();
        
        // Obtener asistencias de las materias del profesor
        $asistencias = Asistencia::with(['alumno', 'materia', 'tipoAsistencia'])
            ->whereHas('materia', function ($query) use ($user) {
                $query->where('profesor_id', $user->id);
            })
            ->orderBy('fecha_hora', 'desc')
            ->paginate(15);
        
        return view('profesor.asistencias', compact('asistencias', 'materias', 'tiposAsistencia'));
    }

    /**
     * Mostrar el perfil del profesor
     */    public function profile()
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            abort(403, 'Acceso no autorizado');
        }
        
        return view('profesor.profile', compact('user'));
    }    /**
     * Actualizar el perfil del profesor
     */    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            abort(403, 'Acceso no autorizado');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);
        
        // Asegúrate de obtener el modelo completo desde la base de datos
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
        
        return redirect()->route('profesor.profile')->with('status', 'Perfil actualizado correctamente');
    }    /**
     * Obtener datos para el gráfico semanal
     */
    public function getDatosSemanal()
    {
        $profesorId = Auth::id();
        
        $datos = $this->obtenerDatosAsistenciaSemanal($profesorId);
        
        return response()->json($datos);
    }

    /**
     * Obtener datos para el gráfico de distribución
     */
    public function getDatosDistribucion()
    {
        $profesorId = Auth::id();
        
        $datos = $this->obtenerDatosDistribucionAsistencias($profesorId);
        
        return response()->json($datos);
    }

    /**
     * Obtiene datos de asistencia para el gráfico semanal (filtrado por profesor)
     * 
     * @param int $profesorId
     * @return array
     */
    private function obtenerDatosAsistenciaSemanal($profesorId)
    {
        // Obtener fecha de inicio de la semana actual (lunes)
        $inicioSemana = Carbon::now()->startOfWeek();
        
        // Crear un array con los días de la semana en español
        $diasSemana = [
            'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'
        ];
        
        // Obtener tipos de asistencia directamente de la base de datos
        $tiposAsistencia = DB::table('tipo_asistencia')
            ->select('id', 'descripcion')
            ->get();
        
        // Colores para cada tipo de asistencia
        $coloresPorTipo = [
            1 => ['rgba(76, 175, 80, 0.2)', '#4caf50'], // Verde para Presente
            2 => ['rgba(244, 67, 54, 0.2)', '#f44336'], // Rojo para Ausente
            3 => ['rgba(255, 152, 0, 0.2)', '#ff9800'], // Naranja para Justificado
            4 => ['rgba(33, 150, 243, 0.2)', '#2196f3']  // Azul (por si existe otro tipo)
        ];
        
        // Preparar datasets
        $datasets = [];
        
        foreach ($tiposAsistencia as $tipo) {
            $datosPorDia = [];
            
            // Para cada día de la semana, contar las asistencias de este tipo
            for ($dia = 0; $dia < 7; $dia++) {
                $fecha = $inicioSemana->copy()->addDays($dia);
                
                // Contar registros de asistencia para este día y tipo (filtrado por profesor)
                $conteo = Asistencia::whereDate('fecha_hora', $fecha)
                    ->where('tipo_asistencia_id', $tipo->id)
                    ->whereHas('materia', function ($query) use ($profesorId) {
                        $query->where('profesor_id', $profesorId);
                    })
                    ->count();
                
                $datosPorDia[$dia] = $conteo;
            }
            
            // Color predeterminado si no está mapeado
            $bgColor = $coloresPorTipo[$tipo->id][0] ?? 'rgba(156, 39, 176, 0.2)';
            $borderColor = $coloresPorTipo[$tipo->id][1] ?? '#9c27b0';
            
            // Agregar dataset para este tipo de asistencia
            $datasets[] = [
                'label' => $tipo->descripcion,
                'data' => array_values($datosPorDia),
                'backgroundColor' => $bgColor,
                'borderColor' => $borderColor,
                'tension' => 0.3,
                'fill' => true
            ];
        }
        
        return [
            'labels' => $diasSemana,
            'datasets' => $datasets
        ];
    }

    /**
     * Obtiene datos para el gráfico de distribución de asistencias (filtrado por profesor)
     * 
     * @param int $profesorId
     * @return array
     */
    private function obtenerDatosDistribucionAsistencias($profesorId)
    {
        // Obtener todos los tipos de asistencia directamente de la base de datos
        $tiposAsistencia = DB::table('tipo_asistencia')
            ->select('id', 'descripcion')
            ->get();
        
        // Mapear los colores según el tipo de asistencia
        $coloresPorTipo = [
            1 => '#4caf50', // Verde para Presente
            2 => '#f44336', // Rojo para Ausente
            3 => '#ff9800', // Naranja para Justificado
            4 => '#2196f3'  // Azul (por si existe otro tipo)
        ];
        
        $labels = [];
        $datos = [];
        $colores = [];
        
        foreach ($tiposAsistencia as $tipo) {
            // Obtener el conteo para este tipo de asistencia (filtrado por profesor)
            $conteo = DB::table('asistencias')
                ->join('materias', 'asistencias.materia_id', '=', 'materias.id')
                ->where('materias.profesor_id', $profesorId)
                ->where('asistencias.tipo_asistencia_id', $tipo->id)
                ->count();
                
            // Agregar a los arrays para el gráfico
            $labels[] = $tipo->descripcion;
            $datos[] = $conteo;
            $colores[] = $coloresPorTipo[$tipo->id] ?? '#9c27b0'; // Color predeterminado si no está mapeado
        }
        
        return [
            'labels' => $labels,
            'datos' => $datos,
            'colores' => $colores
        ];
    }
}
