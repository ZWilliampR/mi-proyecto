<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestSalud extends Model
{
    protected $table = 'tests_salud';

    protected $fillable = [
        'usuario_id',
        'tipo_test',
        'respuestas',
        'puntuacion',
        'nivel_resultado',
        'interpretacion',
        'recomendaciones',
    ];

    protected $casts = [
        'respuestas' => 'array',
    ];

    // Relación: un test de salud pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}