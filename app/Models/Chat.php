<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';

    protected $fillable = [
        'usuario_id',
        'medico_id',
        'tipo_chat',
        'estado',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    // Relación: un chat pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación: un chat puede tener un médico asignado
    public function medico()
    {
        return $this->belongsTo(Usuario::class, 'medico_id');
    }

    // Relación: un chat tiene muchos mensajes
    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'chat_id');
    }
}