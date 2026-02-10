<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'telefono',
        'fecha_nacimiento',
        'genero',
        'role_id',
        'id_socio_costamed',
        'titular_id',
        'membresia_activa',
        'google_access_token',          
        'google_refresh_token',         
        'google_token_expires_at',      
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_nacimiento' => 'date',
        'membresia_activa' => 'boolean',
    ];

    // Relación: un usuario pertenece a un rol
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relación: un titular puede tener muchos miembros familiares
    public function miembrosFamiliares()
    {
        return $this->hasMany(Usuario::class, 'titular_id');
    }

    // Relación: un miembro familiar pertenece a un titular
    public function titular()
    {
        return $this->belongsTo(Usuario::class, 'titular_id');
    }

    // Relación: un usuario tiene muchos medicamentos
    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class, 'usuario_id');
    }

    // Relación: un usuario tiene muchos registros de calendario de mujer
    public function calendarioMujer()
    {
        return $this->hasMany(CalendarioMujer::class, 'usuario_id');
    }

    // Relación: un usuario tiene muchos registros de IMC
    public function imcRegistros()
    {
        return $this->hasMany(ImcRegistro::class, 'usuario_id');
    }

    // Relación: un usuario tiene muchos tests de salud
    public function testsSalud()
    {
        return $this->hasMany(TestSalud::class, 'usuario_id');
    }

    // Relación: un usuario tiene muchos chats
    public function chats()
    {
        return $this->hasMany(Chat::class, 'usuario_id');
    }

    // Relación: un usuario tiene muchas notificaciones
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }
}