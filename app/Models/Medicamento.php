<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $table = 'medicamentos';

    protected $fillable = [
        'usuario_id',
        'nombre_medicamento',
        'dosis',
        'frecuencia',
        'hora_inicio',
        'fecha_inicio',
        'fecha_fin',
        'notas',
        'notificacion_activa',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'notificacion_activa' => 'boolean',
    ];

    // Relación: un medicamento pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación: un medicamento tiene muchos recordatorios
    public function recordatorios()
    {
        return $this->hasMany(RecordatorioMedicamento::class, 'medicamento_id');
    }
}