<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteFalla extends Model
{
    protected $table = 'reportes_fallas';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'descripcion',
        'tipo_falla',
        'prioridad',
        'estado',
        'captura_pantalla',
        'respuesta_soporte',
        'fecha_resolucion',
    ];

    protected $casts = [
        'fecha_resolucion' => 'datetime',
    ];

    // Relación: un reporte pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}