<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Materia;
use App\Models\Asistencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{    /**
     * Muestra el dashboard del administrador con estadísticas en tiempo real
     */
    public function index()
    {
        // Contar usuarios totales
        $totalUsuarios = User::count();
        
        // Contar asistencias de hoy
        $asistenciasHoy = Asistencia::whereDate('fecha_hora', Carbon::today())->count();
        
        // Contar materias activas
        $materiasActivas = Materia::count();
          // Calcular porcentaje de asistencia (Presentes / Total de registros de asistencia * 100)
        // Obtenemos el ID del tipo "Presente" directamente de la base de datos
        $tipoPresente = DB::table('tipo_asistencia')
            ->where('descripcion', 'Presente')
            ->orWhere('descripcion', 'A tiempo') // Por si acaso tiene otro nombre
            ->first();
            
        $tipoPresente_id = $tipoPresente ? $tipoPresente->id : 1; // Usar 1 como valor por defecto si no se encuentra
        
        $estadisticas = DB::table('asistencias')
            ->selectRaw('COUNT(*) as totalAsistencias')
            ->selectRaw('SUM(CASE WHEN tipo_asistencia_id = ? THEN 1 ELSE 0 END) as totalPresentes', [$tipoPresente_id])
            ->first();
            
        $porcentajeAsistencia = 0;
        if ($estadisticas->totalAsistencias > 0) {
            $porcentajeAsistencia = round(($estadisticas->totalPresentes / $estadisticas->totalAsistencias) * 100);
        }
        
        // Obtener asistencias recientes para la tabla
        $asistenciasRecientes = Asistencia::with(['materia', 'alumno', 'profesor', 'tipoAsistencia'])
            ->orderBy('fecha_hora', 'desc')
            ->limit(5)
            ->get();
        
        // Obtener datos para los gráficos
        $datosGraficoSemanal = $this->obtenerDatosAsistenciaSemanal();
        $datosGraficoDistribucion = $this->obtenerDatosDistribucionAsistencias();
        
        return view('admin.dashboard', compact(
            'totalUsuarios',
            'asistenciasHoy',
            'materiasActivas',
            'porcentajeAsistencia',
            'asistenciasRecientes',
            'datosGraficoSemanal',
            'datosGraficoDistribucion'
        ));    }    /**
     * Obtiene datos de asistencia para el gráfico semanal
     * 
     * @return array
     */
    private function obtenerDatosAsistenciaSemanal()
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
                
                // Contar registros de asistencia para este día y tipo
                $conteo = Asistencia::whereDate('fecha_hora', $fecha)
                    ->where('tipo_asistencia_id', $tipo->id)
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
    }    /**
     * Obtiene datos para el gráfico de distribución de asistencias
     * 
     * @return array
     */
    private function obtenerDatosDistribucionAsistencias()
    {
        // Obtener todos los tipos de asistencia directamente de la base de datos
        $tiposAsistencia = DB::table('tipo_asistencia')
            ->select('id', 'descripcion')
            ->get();
        
        // Mapear los colores según el tipo de asistencia
        $coloresPorTipo = [
            1 => '#4caf50', // Verde para Presente
            2 => '#f44336', // Rojo para Ausente
            3 => '#ff9800', // Naranja para Justificado (o cualquier otro tipo)
            4 => '#2196f3'  // Azul (por si existe otro tipo)
        ];
        
        $labels = [];
        $datos = [];
        $colores = [];
        
        foreach ($tiposAsistencia as $tipo) {
            // Obtener el conteo para este tipo de asistencia
            $conteo = DB::table('asistencias')
                ->where('tipo_asistencia_id', $tipo->id)
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
     * API para obtener los datos de asistencia semanal en formato JSON
     */
    public function getDatosSemanal()
    {
        return response()->json($this->obtenerDatosAsistenciaSemanal());
    }
      /**
     * API para obtener los datos de distribución de asistencias en formato JSON
     */
    public function getDatosDistribucion()
    {
        return response()->json($this->obtenerDatosDistribucionAsistencias());
    }
    
    /**
     * API para obtener los tipos de asistencia disponibles en el sistema
     */
    public function getTiposAsistencia()
    {
        $tiposAsistencia = DB::table('tipo_asistencia')
            ->select('id', 'nombre', 'descripcion')
            ->get();
            
        return response()->json([
            'tiposAsistencia' => $tiposAsistencia
        ]);
    }
}
