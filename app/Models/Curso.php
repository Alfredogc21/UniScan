<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'cursos';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}
