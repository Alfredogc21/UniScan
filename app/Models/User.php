<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'estado_id'
    ];

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the asistencias where the user is the alumno.
     */
    public function asistenciasComoAlumno()
    {
        return $this->hasMany(Asistencia::class, 'alumno_id');
    }

    /**
     * Get the asistencias where the user is the profesor.
     */
    public function asistenciasComoProfesor()
    {
        return $this->hasMany(Asistencia::class, 'profesor_id');
    }

    /**
     * Get all asistencias related to the user (as alumno).
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'alumno_id');
    }

    /**
     * Check if the user is a profesor.
     */
    public function isProfesor()
    {
        return $this->role_id === 2; // Asumiendo que 2 es el ID para el rol de profesor
    }

    /**
     * Check if the user is an alumno.
     */
    public function isAlumno()
    {
        return $this->role_id === 3; // Asumiendo que 3 es el ID para el rol de alumno
    }
}
