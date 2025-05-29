<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Materia;
use Illuminate\Support\Facades\DB;

class CheckMateriasData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uniscan:check-materias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los datos de la tabla materias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando datos de materias...');
        
        $materias = DB::table('materias')->get();
        
        if ($materias->isEmpty()) {
            $this->error('No hay materias en la base de datos.');
            return;
        }
        
        $this->info('Total de materias: ' . $materias->count());
        
        $headers = ['ID', 'Profesor ID', 'Nombre', 'Aula', 'Curso', 'Token QR', 'QR Path'];
        $rows = [];
        
        foreach ($materias as $materia) {
            $rows[] = [
                $materia->id,
                $materia->profesor_id,
                $materia->nombre,
                $materia->aula ?? 'NULL',
                $materia->curso ?? 'NULL',
                substr($materia->token_qr, 0, 10) . '...',
                $materia->qr_path ?? 'NULL'
            ];
        }
        
        $this->table($headers, $rows);
        
        // Check for null values in important fields
        $nullAulas = DB::table('materias')->whereNull('aula')->count();
        $nullCursos = DB::table('materias')->whereNull('curso')->count();
        
        if ($nullAulas > 0) {
            $this->warn("¡Alerta! Hay $nullAulas materias sin aula asignada.");
        }
        
        if ($nullCursos > 0) {
            $this->warn("¡Alerta! Hay $nullCursos materias sin curso asignado.");
        }
        
        // Check professor relationships
        $profesorIds = DB::table('materias')->pluck('profesor_id')->unique()->toArray();
        $existingProfesors = DB::table('users')->whereIn('id', $profesorIds)->pluck('id')->toArray();
        
        $missingProfesors = array_diff($profesorIds, $existingProfesors);
        
        if (count($missingProfesors) > 0) {
            $this->error("¡Error! Hay materias con profesores que no existen: " . implode(', ', $missingProfesors));
        }
    }
}
