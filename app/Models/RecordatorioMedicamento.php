<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordatorioMedicamento extends Model
{
    protected $table = 'recordatorios_medicamentos';

    protected $fillable = [
        'medicamento_id',
        'fecha_hora_recordatorio',
        'estado',
        'fecha_hora_tomado',
        'notas',
    ];

    protected $casts = [
        'fecha_hora_recordatorio' => 'datetime',
        'fecha_hora_tomado' => 'datetime',
    ];

    // Relación: un recordatorio pertenece a un medicamento
    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }
}