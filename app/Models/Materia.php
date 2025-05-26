<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

   protected $fillable = ['profesor_id', 'nombre', 'aula', 'horario_ingreso', 'horario_salida', 'curso', 'token_qr'];

public function asistencias() {
    return $this->hasMany(Asistencia::class);
}

public function profesor() {
    return $this->belongsTo(User::class, 'profesor_id');
}
}