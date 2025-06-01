<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAsistencia extends Model
{
    use HasFactory;

    protected $table = 'tipo_asistencia';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    // Relación con asistencias
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }
    
    /**
     * Obtiene la clase CSS para este tipo de asistencia
     * 
     * @return string
     */
    public function getCssClass()
    {
        $estadoText = $this->descripcion ?? 'No definido';
        
        // Normalización para clase CSS
        $classNameFromStatus = preg_replace('/\s+/', '-', 
            preg_replace('/[\p{M}]/u', '', 
                normalizer_normalize(mb_strtolower($estadoText), \Normalizer::FORM_D)
            )
        );
        $classNameFromStatus = preg_replace('/[^\w\-]/', '', $classNameFromStatus);
        
        return "attendance-" . $classNameFromStatus;
    }
}
