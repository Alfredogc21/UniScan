<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

   protected $fillable = ['profesor_id', 'nombre', 'aula_id', 'horario_ingreso', 'horario_salida', 'curso_id', 'token_qr', 'qr_path'];

public function asistencias() {
    return $this->hasMany(Asistencia::class);
}

public function profesor() {
    return $this->belongsTo(User::class, 'profesor_id');
}

    public function aula() {
        return $this->belongsTo(\App\Models\Aula::class, 'aula_id');
    }
    public function curso() {
        return $this->belongsTo(\App\Models\Curso::class, 'curso_id');
    }
}