<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asistencia;
use App\Models\User;
use App\Models\Materia;
use App\Models\TipoAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;

class AdminAsistenciaController extends Controller
{    /**
     * Muestra la lista de asistencias/ausencias para el administrador
     */    
    public function index(Request $request)
    {
        // Obtener los profesores (role_id = 2 suponiendo que ese es el ID de rol para profesores)
        $profesores = User::where('role_id', 2)->orderBy('name')->get();

        // Obtener todas las materias ordenadas por nombre
        $materias = Materia::orderBy('nombre')->get();

        // Obtener todos los tipos de asistencia para filtros y visualización
        $tiposAsistencia = TipoAsistencia::all();
        
        // Obtener lista de alumnos para filtros
        $alumnos = User::where('role_id', 3)->orderBy('name')->get();
        
        // Aplicar los mismos filtros pero con paginación
        $query = Asistencia::with(['materia', 'alumno', 'profesor', 'tipoAsistencia']);

        // Reutilizamos la lógica de filtrado
        if ($request->filled('materia')) {
            $query->where('materia_id', $request->materia);
        }
        if ($request->filled('profesor')) {
            $query->where('profesor_id', $request->profesor);
        }
        if ($request->filled('alumno')) {
            $query->where('alumno_id', $request->alumno);
        }
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_inicio)
                ->whereDate('fecha_hora', '<=', $request->fecha_fin);
        } else if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_inicio);
        } else if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_fin);
        }
        if ($request->filled('estado')) {
            $query->where('tipo_asistencia_id', $request->estado);
        }
        
        // Obtener resultados paginados
        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);        // Estadísticas para el resumen (optimizadas en una sola consulta)
        $estadisticas = DB::table('asistencias')
            ->selectRaw('COUNT(*) as totalAsistencias')
            ->selectRaw('SUM(CASE WHEN tipo_asistencia_id = 1 THEN 1 ELSE 0 END) as totalPresentes')
            ->selectRaw('SUM(CASE WHEN tipo_asistencia_id = 2 THEN 1 ELSE 0 END) as totalAusentes')
            ->selectRaw('SUM(CASE WHEN tipo_asistencia_id = 4 THEN 1 ELSE 0 END) as totalJustificados')
            ->first();
            
        $totalAsistencias = $estadisticas->totalAsistencias;
        $totalPresentes = $estadisticas->totalPresentes;
        $totalAusentes = $estadisticas->totalAusentes;
        $totalJustificados = $estadisticas->totalJustificados;

        // Alumnos con más asistencias (top 5)
        $alumnosConMasAsistencias = DB::table('asistencias')
            ->select('asistencias.alumno_id', 'users.name as nombre', DB::raw('count(*) as total'))
            ->join('users', 'asistencias.alumno_id', '=', 'users.id')
            ->where('tipo_asistencia_id', 1) // Solo contar asistencias presentes
            ->groupBy('asistencias.alumno_id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Pasar variables a la vista
        return view('admin.asistenciasAdmin', compact(
            'asistencias',
            'profesores',
            'materias',
            'alumnos',
            'tiposAsistencia',
            'totalAsistencias',
            'totalPresentes',
            'totalAusentes',
            'totalJustificados',
            'alumnosConMasAsistencias'
        ));
    }    /**
     * Justifica una asistencia
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function justificarAsistencia(Request $request, $id)
    {
        // Validar datos de entrada
        $validatedData = $request->validate([
            'justificacion' => 'required|string|max:500',
            'evidencia' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048' // Validación de archivos
        ]);

        try {
            // Buscar la asistencia
            $asistencia = Asistencia::findOrFail($id);
            
            // Aplicar justificación
            $asistencia->justificacion = $validatedData['justificacion'];

            // Si se sube un archivo de evidencia
            if ($request->hasFile('evidencia')) {
                $file = $request->file('evidencia');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Crear directorio si no existe
                $evidenciasDir = public_path('evidencias');
                if (!file_exists($evidenciasDir)) {
                    mkdir($evidenciasDir, 0755, true);
                }
                
                // Mover archivo
                $file->move($evidenciasDir, $fileName);
                $asistencia->evidencia = 'evidencias/' . $fileName;
            }

            // Guardar cambios
            $asistencia->save();

            return redirect()->back()->with('success', 'Asistencia justificada correctamente');
        } catch (\Exception $e) {
            // Registrar el error y mostrar mensaje amigable
            Log::error('Error al justificar asistencia: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al justificar la asistencia. Inténtelo nuevamente.');
        }
    }/**
     * Obtiene los detalles de una asistencia para mostrar en modal
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */    public function getAsistenciaDetails($id)
    {
        try {
            // Registrar la solicitud para depuración
            Log::info('Solicitud de detalles de asistencia recibida', ['id' => $id]);
            
            // Cargar las relaciones necesarias y encontrar el registro
            $asistencia = Asistencia::with(['alumno', 'materia', 'profesor', 'tipoAsistencia'])
                ->findOrFail($id);

            // Determinar clases CSS para el frontend basadas en el estado
            $estadoCssClass = $asistencia->estado_css_class;
                
            // Registrar éxito para depuración
            Log::info('Asistencia encontrada correctamente', ['id' => $id]);
            
            // Devolver información formateada usando los accesorios del modelo
            return response()->json([
                'id' => $asistencia->id,
                'alumno' => $asistencia->alumno->name ?? 'No disponible',
                'materia' => $asistencia->materia->nombre ?? 'No disponible',
                'profesor' => $asistencia->nombre_profesor,
                'fecha' => Carbon::parse($asistencia->fecha_hora)->format('d/m/Y'),
                'hora' => Carbon::parse($asistencia->fecha_hora)->format('H:i'),
                'estado' => $asistencia->estado_texto,
                'estado_descripcion' => $asistencia->estado_texto,
                'estado_css_class' => $estadoCssClass,
                'tipo_asistencia_id' => $asistencia->tipo_asistencia_id,
                'justificacion' => $asistencia->justificacion ?? 'Sin justificación',
                'evidencia' => $asistencia->evidencia
            ]);
        } catch (\Exception $e) {
            // Registrar el error para depuración
            Log::error('Error al obtener detalles de asistencia', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Devolver respuesta de error
            return response()->json([
                'error' => 'Error al obtener detalles de la asistencia',
                'message' => $e->getMessage()
            ], 500);
        }
    }/**
     * Obtener asistencias filtradas según request
     * 
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getAsistenciasFiltradas(Request $request)
    {
        $query = Asistencia::with(['materia', 'alumno', 'profesor', 'tipoAsistencia']);

        // Aplicar filtros
        if ($request->filled('materia')) {
            $query->where('materia_id', $request->materia);
        }
        if ($request->filled('profesor')) {
            $query->where('profesor_id', $request->profesor);
        }
        if ($request->filled('alumno')) {
            $query->where('alumno_id', $request->alumno);
        }
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_inicio)
                ->whereDate('fecha_hora', '<=', $request->fecha_fin);
        } else if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_inicio);
        } else if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_fin);
        }
        if ($request->filled('estado')) {
            $query->where('tipo_asistencia_id', $request->estado);
        }

        return $query->orderBy('fecha_hora', 'desc')->get();
    }

    /**
     * Generar PDF con DOMPDF
     * 
     * @param string $html
     * @param string $filename
     * @param bool $attachment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function generarPdf($html, $filename, $attachment = true)
    {
        // Usar DOMPDF
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        // Devolver el PDF (como descarga o para visualizar)
        return $dompdf->stream($filename, ['Attachment' => $attachment]);
    }

    /**
     * Exportar asistencias a PDF (descarga)
     */    
    public function exportarPdf(Request $request)
    {
        // Obtener asistencias filtradas
        $asistencias = $this->getAsistenciasFiltradas($request);

        // Generar el HTML para PDF
        $html = $this->generarHtmlPdf($asistencias);
        
        // Nombre del archivo
        $fecha = date('Y-m-d');
        $filename = "registro_asistencias_{$fecha}.pdf";

        // Generar y devolver PDF (con opción de descarga)
        return $this->generarPdf($html, $filename, true);
    }

    /**
     * Previsualizar PDF en nueva ventana
     */    
    public function previsualizarPdf(Request $request)
    {
        // Obtener asistencias filtradas
        $asistencias = $this->getAsistenciasFiltradas($request);

        // Generar el HTML para previsualización
        $html = $this->generarHtmlPdf($asistencias, true);
        
        // Generar y devolver PDF (sin opción de descarga)
        return $this->generarPdf($html, 'previsualizar_asistencias.pdf', false);
    }    /**
     * Obtener estilos CSS para PDF
     * 
     * @param bool $preview
     * @return string
     */
    private function getPdfStyles($preview = false)
    {
        $styles = '
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .container {
                background-color: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .header {
                background-color: #17295B;
                color: white;
                padding: 20px;
                text-align: center;
                margin: -30px -30px 30px -30px;
                border-radius: 10px 10px 0 0;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .info {
                text-align: right;
                margin-bottom: 30px;
                color: #666;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th {
                background-color: #1A759F;
                color: white;
                padding: 12px 8px;
                text-align: left;
                font-weight: bold;
                border: 1px solid #ddd;
            }
            td {
                padding: 10px 8px;
                border: 1px solid #ddd;
                vertical-align: top;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            tr:nth-child(odd) {
                background-color: white;
            }
            .status-presente {
                background-color: #d4edda;
                color: #155724;
                padding: 4px 8px;
                border-radius: 4px;
                font-weight: bold;
            }
            .status-ausente {
                background-color: #f8d7da;
                color: #721c24;
                padding: 4px 8px;
                border-radius: 4px;
                font-weight: bold;
            }
            .status-tardanza, .status-tarde {
                background-color: #fff3cd;
                color: #856404;
                padding: 4px 8px;
                border-radius: 4px;
                font-weight: bold;
            }
            .status-justificado {
                background-color: #d1ecf1;
                color: #0c5460;
                padding: 4px 8px;
                border-radius: 4px;
                font-weight: bold;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .no-data {
                text-align: center;
                color: #666;
                font-style: italic;
                padding: 40px;
            }';
        
        if ($preview) {
            $styles .= '
                .print-buttons {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .print-btn {
                    background-color: #17295B;
                    color: white;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    margin: 0 10px;
                    font-size: 14px;
                }
                .print-btn:hover {
                    background-color: #1A759F;
                }
                @media print {
                    .print-buttons { display: none; }
                    body { background-color: white; }
                    .container { box-shadow: none; }
                }';
        }
        
        return $styles;
    }
    
    /**
     * Generar HTML para PDF
     */
    private function generarHtmlPdf($asistencias, $preview = false)
    {
        $fecha = date('d/m/Y');
        $hora = date('H:i');
        
        // Obtenemos los estilos CSS
        $styles = $this->getPdfStyles($preview);
          // Construimos la estructura del documento HTML
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>UniScan - Registro de Asistencias</title>
            <style>' . $styles . '</style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>UniScan - Registro de Asistencias</h1>
                </div>
                
                <div class="info">
                    <strong>Fecha de generación:</strong> ' . $fecha . ' - ' . $hora . '<br>
                    <strong>Total de registros:</strong> ' . $asistencias->count() . '
                </div>';
        
        // Si hay registros, generamos la tabla
        if ($asistencias->count() > 0) {
            $html .= '
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Estudiante</th>
                            <th>Materia</th>
                            <th>Profesor</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Justificación</th>
                        </tr>
                    </thead>
                    <tbody>';            // Iteramos sobre las asistencias y construimos las filas de la tabla
            foreach ($asistencias as $asistencia) {
                // Convertimos la clase de asistencia a clase de status para el PDF
                $baseClass = preg_replace('/attendance-/', '', $asistencia->estado_css_class);
                
                // Determinamos la clase de visualización basada en el tipo de asistencia
                $estadoClases = ['status-' . $baseClass];
                $estadoTexto = $asistencia->estado_texto;
                
                // Asignamos clases adicionales según el contenido para retrocompatibilidad visual
                $estadoLower = strtolower($estadoTexto);
                if (strpos($estadoLower, 'presente') !== false) {
                    $estadoClases[] = 'status-presente';
                } elseif (strpos($estadoLower, 'ausente') !== false) {
                    $estadoClases[] = 'status-ausente';
                } elseif (strpos($estadoLower, 'tardanza') !== false || strpos($estadoLower, 'tarde') !== false) {
                    $estadoClases[] = 'status-tardanza';
                } elseif (strpos($estadoLower, 'justifica') !== false) {
                    $estadoClases[] = 'status-justificado';
                }
                
                // Formateamos las fechas
                $fecha = Carbon::parse($asistencia->fecha_hora)->format('d/m/Y');
                $hora = Carbon::parse($asistencia->fecha_hora)->format('H:i');
                
                // Añadimos la fila a la tabla
                $html .= '
                    <tr>
                        <td>' . $asistencia->id . '</td>
                        <td>' . ($asistencia->alumno->name ?? 'No disponible') . '</td>
                        <td>' . ($asistencia->materia->nombre ?? 'No disponible') . '</td>
                        <td>' . $asistencia->nombre_profesor . '</td>
                        <td>' . $fecha . '</td>
                        <td>' . $hora . '</td>
                        <td><span class="' . implode(' ', $estadoClases) . '">' . $estadoTexto . '</span></td>
                        <td>' . ($asistencia->justificacion ?? 'Sin justificación') . '</td>
                    </tr>';
            }
            
            $html .= '
                    </tbody>
                </table>';
        } else {
            $html .= '<div class="no-data">📋 No hay registros de asistencia para mostrar</div>';
        }
        
        $html .= '
                <div class="footer">
                    © UniScan ' . date('Y') . ' - Sistema de Gestión de Asistencias<br>
                    Generado automáticamente el ' . $fecha . ' a las ' . $hora . '
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}
