<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitaDomiciliaria extends Model
{
    protected $table = 'visitas_domiciliarias';

    protected $fillable = [
        'usuario_id',
        'medico_id',
        'fecha_solicitada',
        'hora_solicitada',
        'direccion',
        'ciudad',
        'estado',
        'codigo_postal',
        'motivo_visita',
        'tipo_servicio',
        'estado_solicitud',
        'notas_medico',
        'fecha_confirmacion',
        'fecha_completada',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha_solicitada' => 'date',
        'hora_solicitada' => 'datetime:H:i',
        'fecha_confirmacion' => 'datetime',
        'fecha_completada' => 'datetime',
    ];

    // Relación: una visita pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación: una visita puede tener un médico asignado
    public function medico()
    {
        return $this->belongsTo(Usuario::class, 'medico_id');
    }
}