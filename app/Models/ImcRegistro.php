<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImcRegistro extends Model
{
    protected $table = 'imc_registros';

    protected $fillable = [
        'usuario_id',
        'peso',
        'altura',
        'imc_calculado',
        'clasificacion',
        'recomendaciones',
    ];

    protected $casts = [
        'peso' => 'decimal:2',
        'altura' => 'decimal:2',
        'imc_calculado' => 'decimal:2',
    ];

    // Relación: un registro de IMC pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}