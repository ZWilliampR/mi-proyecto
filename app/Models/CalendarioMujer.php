<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarioMujer extends Model
{
    protected $table = 'calendario_mujer';

    protected $fillable = [
        'usuario_id',
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'duracion_ciclo',
        'duracion_periodo',
        'proxima_fecha_estimada',
        'fecha_ovulacion_estimada',
        'sintomas',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
        'proxima_fecha_estimada' => 'date',
        'fecha_ovulacion_estimada' => 'date',
        'sintomas' => 'array',
    ];

    // Relación: un registro de calendario pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}