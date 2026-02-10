<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected $fillable = [
        'chat_id',
        'usuario_id',
        'mensaje',
        'archivo_adjunto',
        'leido',
        'fecha_leido',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'fecha_leido' => 'datetime',
    ];

    // Relación: un mensaje pertenece a un chat
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    // Relación: un mensaje pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}