<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Clase extends Model
{
    use HasFactory;

    protected $table = 'clases';

    protected $fillable = [
        'nombre',
        'descripcion',
        'profesor_id', // FK con tabla users o tabla profesores
        'hora_inicio',
        'hora_fin',
        'aula',
    ];

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }
}
