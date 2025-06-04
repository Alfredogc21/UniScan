<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\Curso;
use App\Models\Asistencia;
use App\Models\TipoAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
     */      public function alumnos(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            abort(403, 'Acceso no autorizado');
        }
        
        // Obtener materias del profesor para el formulario de agregar alumno y filtros
        $materias = Materia::where('profesor_id', $user->id)->get();
        $aulas = DB::table('aulas')->orderBy('nombre')->get();
        $cursos = DB::table('cursos')->orderBy('nombre')->get();
        
        // Preparar la consulta base - más eficiente al precargar las relaciones
        $query = User::where('role_id', 3);
        
        // Filtro por estado (activo/inactivo)
        if ($request->filled('estado')) {
            $query->where('estado_id', $request->estado);
        }
        
        // Subquery para filtrar por materias del profesor actual
        $profesorMateriasSubQuery = function ($q) use ($user) {
            $q->where('profesor_id', $user->id);
        };
        
        // Filtro base: alumnos que tienen asistencias en materias del profesor
        $query->whereHas('asistencias', function ($subquery) use ($user, $request, $profesorMateriasSubQuery) {
            $subquery->whereHas('materia', function ($q) use ($user, $request, $profesorMateriasSubQuery) {
                $q->where('profesor_id', $user->id);
                
                // Filtro por materia
                if ($request->filled('materia')) {
                    $q->where('id', $request->materia);
                }
                
                // Filtro por aula
                if ($request->filled('aula')) {
                    $q->where('aula_id', $request->aula);
                }
                
                // Filtro por curso
                if ($request->filled('curso')) {
                    $q->where('curso_id', $request->curso);
                }
            });
        });
        
        // Incluir conteo de asistencias para cálculos de porcentaje
        // Esta parte trae las relaciones necesarias para el cálculo del porcentaje y evita n+1 queries
        $query->withCount([
            'asistencias as total_asistencias' => function ($q) use ($profesorMateriasSubQuery) {
                $q->whereHas('materia', $profesorMateriasSubQuery);
            },
            'asistencias as asistencias_presente' => function ($q) use ($profesorMateriasSubQuery) {
                $q->whereHas('materia', $profesorMateriasSubQuery)
                  ->where('tipo_asistencia_id', 1); // Tipo "Presente"
            }
        ])->with(['asistencias' => function ($q) use ($profesorMateriasSubQuery) {
            $q->whereHas('materia', $profesorMateriasSubQuery)
              ->with('materia')
              ->latest();
        }]);
        
        // Ejecutar la consulta y procesar los resultados
        $alumnos = $query->get()->map(function ($alumno) {
            // Calcular porcentaje de asistencia usando los conteos
            $totalAsistencias = $alumno->total_asistencias;
            $asistenciasPresente = $alumno->asistencias_presente;
            $porcentajeAsistencia = $totalAsistencias > 0 ? round(($asistenciasPresente / $totalAsistencias) * 100) : 0;
            
            // Obtener materias compartidas con el profesor
            $materiasCompartidas = $alumno->asistencias
                ->pluck('materia')
                ->unique('id')
                ->pluck('nombre')
                ->toArray();
            
            // Obtener último acceso (última asistencia registrada)
            $ultimaAsistencia = $alumno->asistencias->first();
            $ultimoAcceso = $ultimaAsistencia ? $ultimaAsistencia->fecha_hora : null;
            
            // Agregar atributos calculados al modelo del alumno
            $alumno->porcentaje_asistencia = $porcentajeAsistencia;
            $alumno->materias_compartidas = implode(', ', $materiasCompartidas);
            $alumno->ultimo_acceso = $ultimoAcceso;
            
            return $alumno;
        });
        
        // Filtro por rango de porcentaje de asistencia
        // Se aplica al final porque es un atributo calculado que no está en la base de datos
        if ($request->filled('asistencia_min')) {
            $alumnos = $alumnos->filter(function ($alumno) use ($request) {
                return $alumno->porcentaje_asistencia >= $request->asistencia_min;
            });
        }
        
        if ($request->filled('asistencia_max')) {
            $alumnos = $alumnos->filter(function ($alumno) use ($request) {
                return $alumno->porcentaje_asistencia <= $request->asistencia_max;
            });
        }
        
        return view('profesor.alumnos', compact('alumnos', 'materias', 'aulas', 'cursos'));
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
     * Mostrar detalles de una materia específica
     */
    public function showMateria($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }
        
        try {
            // Verificar que la materia pertenece al profesor
            $materia = Materia::with(['aula', 'curso'])
                ->where('id', $id)
                ->where('profesor_id', $user->id)
                ->first();
            
            if (!$materia) {
                return response()->json(['error' => 'Materia no encontrada'], 404);
            }
            
            // Contar alumnos registrados en esta materia
            $alumnosCount = User::whereHas('asistencias', function ($query) use ($id) {
                $query->where('materia_id', $id);
            })->distinct('id')->count();
            
            // Preparar los datos para la respuesta
            $response = [
                'id' => $materia->id,
                'nombre' => $materia->nombre,
                'horario_ingreso' => $materia->horario_ingreso,
                'horario_salida' => $materia->horario_salida,
                'aula' => $materia->aula ? [
                    'id' => $materia->aula->id,
                    'nombre' => $materia->aula->nombre
                ] : null,
                'curso' => $materia->curso ? [
                    'id' => $materia->curso->id,
                    'nombre' => $materia->curso->nombre
                ] : null,
                'alumnos_count' => $alumnosCount
            ];
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles de materia', [
                'materia_id' => $id,
                'profesor_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Almacenar una nueva materia creada por el profesor
     */
    public function storeMateria(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            return redirect()->back()->with('error', 'Acceso no autorizado');
        }

        // Validación
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'aula' => 'required|string|max:50',
            'horario_ingreso' => 'required',
            'horario_salida' => 'required|after:horario_ingreso',
            'curso' => 'required|string|max:50'
        ]);

        // Buscar o crear el aula
        $aula = \App\Models\Aula::firstOrCreate(['nombre' => $request->aula]);
        
        // Buscar o crear el curso
        $curso = \App\Models\Curso::firstOrCreate(['nombre' => $request->curso]);

        // Crear la materia asignándola automáticamente al profesor actual
        $materia = Materia::create([
            'nombre' => $request->nombre,
            'profesor_id' => $user->id, // Asignar automáticamente al profesor actual
            'aula_id' => $aula->id,
            'horario_ingreso' => $request->horario_ingreso,
            'horario_salida' => $request->horario_salida,
            'curso_id' => $curso->id,
            'token_qr' => \Illuminate\Support\Str::random(40),
            'qr_path' => '0' // Inicializar con '0' para cumplir con constraint de base de datos
        ]);

        return redirect()->route('profesor.materias')->with('success', 'Materia creada exitosamente');
    }

    /**
     * Mostrar las asistencias de las materias del profesor
     */    public function asistencias()
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            abort(403, 'Acceso no autorizado');
        }
        
        // Obtener materias del profesor para el filtro con sus relaciones
        $materias = Materia::with(['aula', 'curso'])->where('profesor_id', $user->id)->get();
        
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
     */    private function obtenerDatosDistribucionAsistencias($profesorId)
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
    
    /**
     * Obtener datos de un alumno para edición
     */
    public function editAlumno($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }
        
        // Verificar que el alumno pertenece a una materia del profesor
        $alumno = User::where('id', $id)
            ->where('role_id', 3)
            ->whereHas('asistencias', function ($query) use ($user) {
                $query->whereHas('materia', function ($q) use ($user) {
                    $q->where('profesor_id', $user->id);
                });
            })
            ->first();
        
        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado o no está matriculado en sus materias'], 404);
        }
        
        return response()->json($alumno);
    }
    
    /**
     * Actualizar datos de un alumno
     */
    public function updateAlumno(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }
        
        // Verificar que el alumno pertenece a una materia del profesor
        $alumno = User::where('id', $id)
            ->where('role_id', 3)
            ->whereHas('asistencias', function ($query) use ($user) {
                $query->whereHas('materia', function ($q) use ($user) {
                    $q->where('profesor_id', $user->id);
                });
            })
            ->first();
        
        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado o no está matriculado en sus materias'], 404);
        }
          // Validación
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$alumno->id,
            'estado_id' => 'required|in:1,2',
            'password' => 'nullable|string|min:8',
        ]);
        
        // Actualizar datos
        $alumno->name = $validated['name'];
        $alumno->email = $validated['email'];
        $alumno->estado_id = $validated['estado_id'];
        
        // Actualizar contraseña si se proporcionó una nueva
        if (!empty($validated['password'])) {
            $alumno->password = bcrypt($validated['password']);
        }
        
        $alumno->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Alumno actualizado correctamente'
        ]);
    }
      /**
     * Crear un nuevo alumno
     */
    public function storeAlumno(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            return redirect()->back()->with('error', 'Acceso no autorizado');
        }
          // Validación
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'estado_id' => 'required|in:1,2',
            'password' => 'required|string|min:8|confirmed',
            'materia_id' => 'required|exists:materias,id',
        ]);
        
        // Verificar que la materia pertenece al profesor
        $materia = Materia::where('id', $validated['materia_id'])
            ->where('profesor_id', $user->id)
            ->first();
            
        if (!$materia) {
            return redirect()->back()->with('error', 'La materia seleccionada no es válida');
        }
        
        // Crear usuario nuevo como estudiante
        $alumno = new User();
        $alumno->name = $validated['name'];
        $alumno->email = $validated['email'];
        $alumno->password = bcrypt($validated['password']);
        $alumno->role_id = 3; // Rol estudiante
        $alumno->estado_id = $validated['estado_id'];
        $alumno->save();
        
        // Crear una asistencia inicial para asociar al alumno con la materia
        // Esto establece la relación entre el alumno y la materia del profesor
        $asistencia = new Asistencia();
        $asistencia->materia_id = $materia->id;
        $asistencia->alumno_id = $alumno->id;
        $asistencia->profesor_id = $user->id;
        $asistencia->fecha_hora = now();
        $asistencia->tipo_asistencia_id = 1; // Presente (registro inicial)
        $asistencia->save();
        
        return redirect()->route('profesor.alumnos')->with('success', 'Alumno creado y asociado a la materia correctamente');
    }
    
    /**
     * Eliminar un alumno
     */
    public function deleteAlumno($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            return redirect()->back()->with('error', 'Acceso no autorizado');
        }
        
        // Verificar que el alumno pertenece a una materia del profesor
        $alumno = User::where('id', $id)
            ->where('role_id', 3)
            ->whereHas('asistencias', function ($query) use ($user) {
                $query->whereHas('materia', function ($q) use ($user) {
                    $q->where('profesor_id', $user->id);
                });
            })
            ->first();
        
        if (!$alumno) {
            return redirect()->back()->with('error', 'Alumno no encontrado o no está matriculado en sus materias');
        }
          // En lugar de eliminar el usuario, marcarlo como inactivo
        $alumno->estado_id = 2; // Inactivo
        $alumno->save();
        
        return redirect()->route('profesor.alumnos')->with('success', 'Alumno eliminado correctamente');
    }
    
    /**
     * Obtener detalles de asistencia de un alumno específico
     */
    public function getDetallesAsistencia($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id != 2) {
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }
        
        // Verificar que el alumno pertenece a una materia del profesor
        $alumno = User::where('id', $id)
            ->where('role_id', 3)
            ->whereHas('asistencias', function ($query) use ($user) {
                $query->whereHas('materia', function ($q) use ($user) {
                    $q->where('profesor_id', $user->id);
                });
            })
            ->first();
        
        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado o no está matriculado en sus materias'], 404);
        }
        
        // Obtener asistencias del alumno en materias del profesor
        $asistencias = Asistencia::with(['materia', 'tipoAsistencia'])
            ->where('alumno_id', $id)
            ->whereHas('materia', function ($query) use ($user) {
                $query->where('profesor_id', $user->id);
            })
            ->orderBy('fecha_hora', 'desc')
            ->get();
        
        // Estadísticas generales
        $totalAsistencias = $asistencias->count();
        $totalPresente = $asistencias->where('tipo_asistencia_id', 1)->count();
        $totalAusente = $asistencias->where('tipo_asistencia_id', 2)->count();
        $totalJustificado = $asistencias->where('tipo_asistencia_id', 3)->count();
        $porcentajeAsistencia = $totalAsistencias > 0 ? round(($totalPresente / $totalAsistencias) * 100) : 0;
        
        // Estadísticas por materia
        $estadisticasPorMateria = $asistencias->groupBy('materia_id')->map(function ($asistenciasMateria) {
            $total = $asistenciasMateria->count();
            $presente = $asistenciasMateria->where('tipo_asistencia_id', 1)->count();
            $ausente = $asistenciasMateria->where('tipo_asistencia_id', 2)->count();
            $justificado = $asistenciasMateria->where('tipo_asistencia_id', 3)->count();
            $porcentaje = $total > 0 ? round(($presente / $total) * 100) : 0;
            
            return [
                'materia' => $asistenciasMateria->first()->materia->nombre,
                'total' => $total,
                'presente' => $presente,
                'ausente' => $ausente,
                'justificado' => $justificado,
                'porcentaje' => $porcentaje
            ];
        })->values();
        
        // Datos para gráfico de tendencia (últimos 30 días)
        $fechaInicio = Carbon::now()->subDays(30);
        $tendencia = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $asistenciasDia = $asistencias->filter(function ($asistencia) use ($fecha) {
                return Carbon::parse($asistencia->fecha_hora)->isSameDay($fecha);
            });
            
            $tendencia[] = [
                'fecha' => $fecha->format('Y-m-d'),
                'presente' => $asistenciasDia->where('tipo_asistencia_id', 1)->count(),
                'ausente' => $asistenciasDia->where('tipo_asistencia_id', 2)->count(),
                'justificado' => $asistenciasDia->where('tipo_asistencia_id', 3)->count()
            ];
        }
        
        return response()->json([
            'alumno' => [
                'id' => $alumno->id,
                'name' => $alumno->name,
                'email' => $alumno->email
            ],
            'estadisticas' => [
                'total' => $totalAsistencias,
                'presente' => $totalPresente,
                'ausente' => $totalAusente,
                'justificado' => $totalJustificado,
                'porcentaje' => $porcentajeAsistencia
            ],
            'estadisticasPorMateria' => $estadisticasPorMateria,
            'asistencias' => $asistencias->map(function ($asistencia) {
                return [
                    'id' => $asistencia->id,
                    'fecha_hora' => $asistencia->fecha_hora,
                    'materia' => $asistencia->materia->nombre,
                    'tipo' => $asistencia->tipoAsistencia->descripcion,
                    'justificacion' => $asistencia->justificacion
                ];
            }),
            'tendencia' => $tendencia
        ]);
    }
    
    /**
     * Justificar una asistencia ausente
     */
    public function justificarAsistencia(Request $request, $id)
    {
        try {
            $profesorId = Auth::id();
            
            // Validación de los datos recibidos
            $request->validate([
                'justificacion' => 'required|string|max:1000',
                'categoria_justificacion' => 'required|string|in:medica,personal,academica,deportiva,otra',
                'fecha_justificacion' => 'required|date',
                'documento_justificacion' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png'
            ], [
                'justificacion.required' => 'El motivo de justificación es obligatorio',
                'categoria_justificacion.required' => 'Debe seleccionar una categoría de justificación',
                'documento_justificacion.max' => 'El archivo no puede exceder 2MB',
                'documento_justificacion.mimes' => 'Solo se permiten archivos PDF, JPG, JPEG o PNG'
            ]);
            
            // Verificar que la asistencia existe y pertenece al profesor
            $asistencia = Asistencia::whereHas('materia', function ($query) use ($profesorId) {
                $query->where('profesor_id', $profesorId);
            })->findOrFail($id);
            
            // Verificar que la asistencia está marcada como ausente
            if ($asistencia->tipo_asistencia_id != 2) {
                return response()->json([
                    'success' => false,
                    'error' => 'Solo se pueden justificar asistencias marcadas como ausentes'
                ], 400);
            }
            
            // Procesar el archivo si se subió uno
            $rutaDocumento = null;
            if ($request->hasFile('documento_justificacion')) {
                $archivo = $request->file('documento_justificacion');
                $nombreArchivo = 'justificacion_' . $id . '_' . time() . '.' . $archivo->getClientOriginalExtension();
                $rutaDocumento = $archivo->storeAs('justificaciones', $nombreArchivo, 'public');
            }
            
            // Actualizar la asistencia
            $asistencia->update([
                'tipo_asistencia_id' => 3, // Cambiar a "Justificado"
                'justificacion' => $request->justificacion,
                'categoria_justificacion' => $request->categoria_justificacion,
                'fecha_justificacion' => $request->fecha_justificacion,
                'documento_respaldo' => $rutaDocumento,
                'profesor_justifica_id' => $profesorId,
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Asistencia justificada correctamente'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al justificar asistencia:', [
                'asistencia_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor al procesar la justificación'
            ], 500);
        }
    }

    /**
     * Filtrar asistencias según criterios
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filtrarAsistencias(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user || $user->role_id != 2) {
                return response()->json(['error' => 'Acceso no autorizado'], 403);
            }
            
            Log::info('Filtrar Asistencias - Request iniciado', [
                'user_id' => $user->id,
                'is_ajax' => $request->ajax(),
                'params' => $request->all()
            ]);
            
            // Construir la consulta base con las relaciones necesarias
            $query = Asistencia::with(['alumno', 'materia', 'tipoAsistencia'])
                ->whereHas('materia', function ($q) use ($user) {
                    $q->where('profesor_id', $user->id);
                });
            
            // Aplicar filtro por materia
        if ($request->filled('materia_id')) {
            $query->where('materia_id', $request->materia_id);
        }
        
        // Aplicar filtro por estado (tipo de asistencia)
        if ($request->filled('estado_id')) {
            $query->where('tipo_asistencia_id', $request->estado_id);
        }
        
        // Aplicar filtros de fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }
        
        // Ejecutar la consulta y paginar resultados
        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);
        
        // Si es una solicitud AJAX, devolver vista parcial
        if ($request->ajax()) {
            $html = '';
            
            if ($asistencias->isEmpty()) {
                $html = '<tr><td colspan="7" class="data-table__cell text-center">No hay asistencias que coincidan con los filtros</td></tr>';
            } else {
                foreach ($asistencias as $asistencia) {
                    // Definir clase CSS para el estado
                    $estadoClass = [
                        1 => 'data-table__status--active',   // Presente
                        2 => 'data-table__status--inactive', // Ausente
                        3 => 'data-table__status--pending'   // Justificado
                    ];
                    $class = $estadoClass[$asistencia->tipo_asistencia_id] ?? '';
                    
                    // Construir html para cada fila
                    $html .= '<tr>';
                    $html .= '<td class="data-table__cell">' . $asistencia->id . '</td>';
                    $html .= '<td class="data-table__cell">' . $asistencia->alumno->name . '</td>';
                    $html .= '<td class="data-table__cell">' . $asistencia->materia->nombre . '</td>';
                    $html .= '<td class="data-table__cell">' . Carbon::parse($asistencia->fecha_hora)->format('d/m/Y') . '</td>';
                    $html .= '<td class="data-table__cell">' . Carbon::parse($asistencia->fecha_hora)->format('H:i') . '</td>';
                    $html .= '<td class="data-table__cell"><span class="data-table__status ' . $class . '">' . $asistencia->tipoAsistencia->descripcion . '</span></td>';
                    $html .= '<td class="data-table__cell">';
                    $html .= '<div class="data-table__actions">';
                    $html .= '<button class="data-table__action btn-ver-asistencia" title="Ver detalles" data-id="' . $asistencia->id . '"><i class="fas fa-eye"></i></button>';
                    if ($asistencia->tipo_asistencia_id == 2) { // Solo mostrar botón de justificar para ausentes
                        $html .= '<button class="data-table__action btn-justificar-asistencia" title="Justificar" data-id="' . $asistencia->id . '"><i class="fas fa-check-circle"></i></button>';
                    }
                    $html .= '</div></td>';
                    $html .= '</tr>';
                }
            }
            
            Log::info('Filtrar Asistencias - Respuesta AJAX preparada', [
                'count' => $asistencias->count(),
                'total' => $asistencias->total(),
                'html_length' => strlen($html)
            ]);
                
            return response()->json([
                'html' => $html,
                'pagination' => $asistencias->links()->toHtml(),
                'count' => $asistencias->count(),
                'total' => $asistencias->total()
            ]);
        }
        
        // Si no es AJAX, redirigir a la vista completa de asistencias
        Log::info('Filtrar Asistencias - Redirección no-AJAX');
        return redirect()->route('profesor.asistencias');
        } catch (\Exception $e) {
            Log::error('Error en filtrarAsistencias', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Error al procesar los filtros: ' . $e->getMessage()], 500);
            } else {
                return redirect()->route('profesor.asistencias')
                    ->with('error', 'Error al procesar los filtros. Por favor, inténtelo de nuevo.');
            }
        }
    }

    /**
     * Obtener detalles de una asistencia específica
     */
    public function obtenerDetalleAsistencia($id)
    {
        try {
            Log::info('Obtener Detalle Asistencia - Iniciando', ['asistencia_id' => $id]);
            
            $profesorId = Auth::id();
            
            // Obtener la asistencia con todas las relaciones necesarias
            $asistencia = Asistencia::with([
                'alumno:id,name,email',
                'materia:id,nombre,aula_id,curso_id',
                'tipoAsistencia:id,descripcion',
                'materia.aula:id,nombre',
                'materia.curso:id,nombre'
            ])
            ->whereHas('materia', function ($query) use ($profesorId) {
                $query->where('profesor_id', $profesorId);
            })
            ->findOrFail($id);
            
            Log::info('Obtener Detalle Asistencia - Asistencia encontrada', [
                'asistencia_id' => $asistencia->id,
                'alumno' => $asistencia->alumno->name,
                'materia' => $asistencia->materia->nombre
            ]);
            
            // Calcular estadísticas adicionales del alumno en esta materia
            $estadisticasAlumno = Asistencia::where('alumno_id', $asistencia->alumno_id)
                ->where('materia_id', $asistencia->materia_id)
                ->selectRaw('
                    COUNT(*) as total_asistencias,
                    SUM(CASE WHEN tipo_asistencia_id = 1 THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN tipo_asistencia_id = 2 THEN 1 ELSE 0 END) as ausentes,
                    SUM(CASE WHEN tipo_asistencia_id = 3 THEN 1 ELSE 0 END) as justificadas
                ')
                ->first();
            
            $porcentajeAsistencia = $estadisticasAlumno->total_asistencias > 0 
                ? round(($estadisticasAlumno->presentes / $estadisticasAlumno->total_asistencias) * 100, 2)
                : 0;
            
            // Obtener asistencias recientes del alumno en esta materia
            $asistenciasRecientes = Asistencia::where('alumno_id', $asistencia->alumno_id)
                ->where('materia_id', $asistencia->materia_id)
                ->with('tipoAsistencia:id,descripcion')
                ->orderBy('fecha_hora', 'desc')
                ->limit(5)
                ->get();
            
            // Preparar los datos para la vista
            $detalles = [
                'asistencia' => [
                    'id' => $asistencia->id,
                    'fecha' => \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y'),
                    'hora' => \Carbon\Carbon::parse($asistencia->fecha_hora)->format('H:i:s'),
                    'estado' => $asistencia->tipoAsistencia->descripcion,
                    'justificacion' => $asistencia->justificacion ?? 'Sin justificación'
                ],
                'alumno' => [
                    'id' => $asistencia->alumno->id,
                    'nombre' => $asistencia->alumno->name,
                    'email' => $asistencia->alumno->email
                ],
                'materia' => [
                    'id' => $asistencia->materia->id,
                    'nombre' => $asistencia->materia->nombre,
                    'aula' => $asistencia->materia->aula->nombre ?? 'No asignada',
                    'curso' => $asistencia->materia->curso->nombre ?? 'No asignado'
                ],
                'estadisticas' => [
                    'total_asistencias' => $estadisticasAlumno->total_asistencias,
                    'presentes' => $estadisticasAlumno->presentes,
                    'ausentes' => $estadisticasAlumno->ausentes,
                    'justificadas' => $estadisticasAlumno->justificadas,
                    'porcentaje_asistencia' => $porcentajeAsistencia
                ],
                'asistencias_recientes' => $asistenciasRecientes->map(function ($item) {
                    return [
                        'fecha' => \Carbon\Carbon::parse($item->fecha_hora)->format('d/m/Y H:i'),
                        'estado' => $item->tipoAsistencia->descripcion
                    ];
                })
            ];
            
            Log::info('Obtener Detalle Asistencia - Respuesta preparada', [
                'porcentaje_asistencia' => $porcentajeAsistencia,
                'total_asistencias' => $estadisticasAlumno->total_asistencias
            ]);
            
            return response()->json($detalles);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener detalle de asistencia', [
                'asistencia_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al obtener los detalles: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate QR code for a subject with automatic 6-day regeneration
     */
    public function generateQR($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user || $user->role_id != 2) {
                abort(403, 'Acceso no autorizado');
            }

            $materia = Materia::with(['profesor', 'aula', 'curso'])->findOrFail($id);
            
            // Verificar que la materia pertenece al profesor autenticado
            if ($materia->profesor_id != $user->id) {
                abort(403, 'No tienes permisos para generar QR de esta materia');
            }
            
            // Registrar inicio del proceso
            Log::info('Iniciando generación de QR para materia (Profesor)', [
                'materia_id' => $materia->id,
                'profesor_id' => $user->id,
                'token_qr' => $materia->token_qr
            ]);
              
            // Verificar si el QR necesita regeneración (6 días para profesores)
            $needsRegeneration = false;
            $qrAge = null;
            
            if ($materia->updated_at) {
                $qrAge = Carbon::now()->diffInDays($materia->updated_at);
                $needsRegeneration = $qrAge >= 6; // 6 días para profesores vs 7 para admin
                
                Log::info('Verificando edad del QR', [
                    'materia_id' => $materia->id,
                    'qr_age_days' => $qrAge,
                    'needs_regeneration' => $needsRegeneration,
                    'updated_at' => $materia->updated_at
                ]);
            }
            
            // Si necesita regeneración, crear un nuevo token_qr
            if ($needsRegeneration || empty($materia->token_qr)) {
                $oldToken = $materia->token_qr;
                $materia->token_qr = Str::random(20); // Generar nuevo token de 20 caracteres
                $materia->save(); // Esto actualizará el updated_at automáticamente
                
                Log::info('Token QR regenerado para materia', [
                    'materia_id' => $materia->id,
                    'old_token' => $oldToken,
                    'new_token' => $materia->token_qr,
                    'reason' => empty($oldToken) ? 'no_token' : 'age_expired'
                ]);
            }
              
            // Verificar si ya existe un QR generado y válido
            $hasQrPath = false;
            
            try {
                $hasQrPath = !empty($materia->qr_path);
                
                Log::info('Verificación de qr_path completada', [
                    'materia_id' => $materia->id,
                    'has_qr_path' => $hasQrPath
                ]);
            } catch (\Exception $e) {
                Log::warning('No se pudo acceder a la propiedad qr_path', [
                    'materia_id' => $materia->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Verificar si existe archivo basado en el token_qr actual
            $expectedFilename = 'qrcodes/materia_' . $materia->id . '_' . substr($materia->token_qr, 0, 8) . '.svg';
            $publicQrPath = public_path('storage/' . $expectedFilename);
            $fileExists = file_exists($publicQrPath);
            
            // Si el QR fue regenerado o no existe el archivo, crear uno nuevo
            if ($needsRegeneration || !$fileExists || !$hasQrPath) {
                Log::info('Generando nuevo QR para materia', [
                    'materia_id' => $materia->id,
                    'regenerated' => $needsRegeneration,
                    'file_missing' => !$fileExists,
                    'no_qr_path' => !$hasQrPath
                ]);
                
                // Usar el QrGenerator del profesor si está disponible
                if (function_exists('App\Http\Controllers\Profesor\generate_qr')) {
                    $result = \App\Http\Controllers\Profesor\generate_qr($id);
                    
                    if ($result['success']) {
                        Log::info('QR generado correctamente usando QrGenerator profesor', [
                            'materia_id' => $id,
                            'qr_url' => $result['qr_url'] ?? 'No disponible'
                        ]);
                        
                        if (request()->ajax()) {
                            return response()->json([
                                'success' => true,
                                'existing' => false,
                                'regenerated' => $needsRegeneration,
                                'qr_url' => $result['qr_url'],
                                'qr_data' => [
                                    'token_qr' => Str::limit($materia->token_qr, 15),
                                    'nombre' => $materia->nombre,
                                    'aula' => $materia->aula->nombre ?? 'No especificado',
                                    'curso' => $materia->curso->nombre ?? 'No especificado',
                                    'horario' => $materia->horario_ingreso . ' - ' . $materia->horario_salida
                                ],
                                'updated_at' => $materia->updated_at
                            ]);
                        }
                        
                        return redirect()->route('profesor.materias')->with('success', 'Código QR generado con éxito');
                    }
                }
                
                // Fallback: generar QR directamente aquí
                $this->generateQRFallback($materia);
            } else {
                Log::info('Usando QR existente para materia', [
                    'materia_id' => $materia->id,
                    'qr_path' => $materia->qr_path,
                    'qr_age_days' => $qrAge
                ]);
            }
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'existing' => !$needsRegeneration,
                    'regenerated' => $needsRegeneration,
                    'qr_url' => asset('storage/' . ($materia->qr_path ?: $expectedFilename)),
                    'qr_data' => [
                        'token_qr' => Str::limit($materia->token_qr, 15),
                        'nombre' => $materia->nombre,
                        'aula' => $materia->aula->nombre ?? 'No especificado',
                        'curso' => $materia->curso->nombre ?? 'No especificado',
                        'horario' => $materia->horario_ingreso . ' - ' . $materia->horario_salida
                    ],
                    'updated_at' => $materia->updated_at
                ]);
            }
            
            return redirect()->route('profesor.materias')->with('success', 'Código QR disponible');
            
        } catch (\Exception $e) {
            Log::error('Error al generar QR para materia (Profesor)', [
                'materia_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al generar el código QR: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('profesor.materias')->with('error', 'Error al generar el código QR');
        }
    }
    
    /**
     * Fallback method to generate QR directly
     */
    private function generateQRFallback($materia)
    {
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
        
        // Crear directorio si no existe
        $publicQrDir = public_path('storage/qrcodes');
        if (!file_exists($publicQrDir)) {
            mkdir($publicQrDir, 0755, true);
        }
        
        // Generar el código QR en formato SVG
        $qrcode = QrCode::format('svg')
                        ->size(300)
                        ->errorCorrection('H')
                        ->margin(1)
                        ->generate(json_encode($qrData));
        
        // Generar nombre de archivo del QR
        $qrFilename = 'materia_' . $materia->id . '_' . substr($materia->token_qr, 0, 8) . '.svg';
        $qrPath = $publicQrDir . '/' . $qrFilename;
        $filename = 'qrcodes/' . $qrFilename;
        
        // Guardar el archivo
        file_put_contents($qrPath, $qrcode);
        
        // Actualizar la materia con la ruta del QR
        try {
            $materia->qr_path = $filename;
            $materia->save();
            
            Log::info('QR fallback generado exitosamente', [
                'materia_id' => $materia->id,
                'qr_path' => $filename
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo guardar qr_path en la base de datos (fallback)', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener información básica de una asistencia específica para justificación
     */
    public function obtenerInfoAsistencia($id)
    {
        try {
            $profesorId = Auth::id();
            
            // Obtener la asistencia con las relaciones necesarias
            $asistencia = Asistencia::with([
                'alumno:id,name',
                'materia:id,nombre',
                'tipoAsistencia:id,descripcion'
            ])
            ->whereHas('materia', function ($query) use ($profesorId) {
                $query->where('profesor_id', $profesorId);
            })
            ->findOrFail($id);
            
            // Retornar la información básica necesaria
            return response()->json([
                'alumno' => [
                    'id' => $asistencia->alumno->id,
                    'nombre' => $asistencia->alumno->name
                ],
                'materia' => [
                    'id' => $asistencia->materia->id,
                    'nombre' => $asistencia->materia->nombre
                ],
                'asistencia' => [
                    'id' => $asistencia->id,
                    'fecha' => \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y'),
                    'estado' => $asistencia->tipoAsistencia->descripcion,
                    'justificacion' => $asistencia->justificacion
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo cargar la información de la asistencia',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de asistencias ausentes para justificación
     */
    public function obtenerAsistenciasAusentes(Request $request)
    {
        try {
            $profesorId = Auth::id();
            
            // Filtros
            $materiaId = $request->input('materia_id');
            $fechaDesde = $request->input('fecha_desde');
            $fechaHasta = $request->input('fecha_hasta');
            
            // Construir consulta base
            $query = Asistencia::with(['alumno:id,name', 'materia:id,nombre'])
                ->whereHas('materia', function ($q) use ($profesorId) {
                    $q->where('profesor_id', $profesorId);
                })
                ->where('tipo_asistencia_id', 2); // Ausente
            
            // Aplicar filtros si están presentes
            if ($materiaId) {
                $query->where('materia_id', $materiaId);
            }
            
            if ($fechaDesde) {
                $query->whereDate('fecha_hora', '>=', $fechaDesde);
            }
            
            if ($fechaHasta) {
                $query->whereDate('fecha_hora', '<=', $fechaHasta);
            }
            
            // Obtener resultado y formatear
            $asistenciasAusentes = $query->orderBy('fecha_hora', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'alumno' => $item->alumno->name,
                        'materia' => $item->materia->nombre,
                        'fecha' => \Carbon\Carbon::parse($item->fecha_hora)->format('d/m/Y')
                    ];
                });
                
            return response()->json($asistenciasAusentes);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron cargar las asistencias ausentes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener materias del profesor para AJAX
     */
    public function obtenerMateriasAjax()
    {
        try {
            $profesorId = Auth::id();
            
            $materias = Materia::with(['aula', 'curso'])
                ->where('profesor_id', $profesorId)
                ->select('id', 'nombre', 'aula_id', 'curso_id')
                ->orderBy('nombre')
                ->get();
            
            return response()->json([
                'success' => true,
                'materias' => $materias
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar las materias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estudiantes de una materia específica
     */
    public function obtenerEstudiantesMateria($materiaId)
    {
        try {
            $profesorId = Auth::id();
            
            // Verificar que la materia pertenece al profesor
            $materia = Materia::where('id', $materiaId)
                ->where('profesor_id', $profesorId)
                ->first();
                
            if (!$materia) {
                return response()->json([
                    'success' => false,
                    'error' => 'Materia no encontrada o sin permisos'
                ], 404);
            }
            
            // Obtener estudiantes que tienen asistencias en esta materia
            $estudiantes = User::where('role_id', 3)
                ->whereHas('asistencias', function ($query) use ($materiaId) {
                    $query->where('materia_id', $materiaId);
                })
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'success' => true,
                'estudiantes' => $estudiantes
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar los estudiantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva asistencia justificada
     */
    public function crearAsistenciaJustificada(Request $request)
    {
        try {
            $profesorId = Auth::id();
            
            // Validar los datos de entrada
            $validated = $request->validate([
                'materia_id' => 'required|exists:materias,id',
                'alumno_id' => 'required|exists:users,id',
                'tipo_asistencia_id' => 'required|in:1,2,3',
                'fecha' => 'required|date',
                'hora' => 'required',
                'justificacion' => 'required|string|max:1000'
            ]);
            
            // Verificar que la materia pertenece al profesor
            $materia = Materia::where('id', $validated['materia_id'])
                ->where('profesor_id', $profesorId)
                ->first();
                
            if (!$materia) {
                return response()->json([
                    'success' => false,
                    'error' => 'Materia no válida'
                ], 403);
            }
            
            // Verificar que el alumno existe y tiene role de estudiante
            $alumno = User::where('id', $validated['alumno_id'])
                ->where('role_id', 3)
                ->first();
                
            if (!$alumno) {
                return response()->json([
                    'success' => false,
                    'error' => 'Estudiante no válido'
                ], 403);
            }
            
            // Crear fecha_hora combinando fecha y hora
            $fechaHora = Carbon::createFromFormat('Y-m-d H:i', 
                $validated['fecha'] . ' ' . $validated['hora']);
            
            // Verificar si ya existe una asistencia para este alumno en esta materia en esta fecha
            $asistenciaExistente = Asistencia::where('materia_id', $validated['materia_id'])
                ->where('alumno_id', $validated['alumno_id'])
                ->whereDate('fecha_hora', $validated['fecha'])
                ->first();
                
            if ($asistenciaExistente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ya existe una asistencia registrada para este estudiante en esta fecha'
                ], 422);
            }
            
            // Crear la nueva asistencia
            $asistencia = Asistencia::create([
                'materia_id' => $validated['materia_id'],
                'alumno_id' => $validated['alumno_id'],
                'profesor_id' => $profesorId,
                'fecha_hora' => $fechaHora,
                'tipo_asistencia_id' => $validated['tipo_asistencia_id'],
                'justificacion' => $validated['justificacion']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Asistencia registrada correctamente',
                'asistencia_id' => $asistencia->id
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear asistencia justificada:', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
