<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'materia_id',
        'alumno_id',
        'profesor_id',
        'fecha_hora',
        'tipo_asistencia_id',
        'justificacion'
    ];

    // Relaciones
    public function alumno()
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function tipoAsistencia()
    {
        return $this->belongsTo(TipoAsistencia::class);
    }
    
    /**
     * Obtiene el nombre o descripción del profesor
     *
     * @return string
     */
    public function getNombreProfesorAttribute()
    {
        if ($this->profesor) {
            return $this->profesor->name;
        }
        
        if ($this->profesor_id) {
            $profesor = User::find($this->profesor_id);
            return $profesor ? $profesor->name : 'No disponible';
        }
        
        return 'No disponible';
    }
    
    /**
     * Obtiene el texto del estado de la asistencia
     *
     * @return string
     */
    public function getEstadoTextoAttribute()
    {
        if ($this->tipoAsistencia) {
            return $this->tipoAsistencia->descripcion;
        }
        
        if ($this->tipo_asistencia_id) {
            $tipoAsistencia = TipoAsistencia::find($this->tipo_asistencia_id);
            return $tipoAsistencia ? $tipoAsistencia->descripcion : 'No definido';
        }
        
        return 'No definido';
    }
    
    /**
     * Obtiene la clase CSS para el estado de la asistencia
     *
     * @return string
     */
    public function getEstadoCssClassAttribute()
    {
        if ($this->tipoAsistencia) {
            return $this->tipoAsistencia->getCssClass();
        }
        
        if ($this->tipo_asistencia_id) {
            $tipoAsistencia = TipoAsistencia::find($this->tipo_asistencia_id);
            return $tipoAsistencia ? $tipoAsistencia->getCssClass() : 'attendance-no-definido';
        }
        
        return 'attendance-no-definido';
    }
}
