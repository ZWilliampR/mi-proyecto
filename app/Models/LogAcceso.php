<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAcceso extends Model
{
    protected $table = 'logs_acceso';

    public $timestamps = false; // Esta tabla usa solo 'fecha_hora'

    protected $fillable = [
        'usuario_id',
        'email',
        'ip_address',
        'dispositivo',
        'accion',
        'endpoint',
        'resultado',
        'detalles',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    // Relación: un log pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}