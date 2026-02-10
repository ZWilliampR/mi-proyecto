<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'titulo',
        'mensaje',
        'datos_extra',
        'leida',
        'fecha_leida',
        'enviada',
        'fecha_envio',
    ];

    protected $casts = [
        'datos_extra' => 'array',
        'leida' => 'boolean',
        'fecha_leida' => 'datetime',
        'enviada' => 'boolean',
        'fecha_envio' => 'datetime',
    ];

    // Relación: una notificación pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}