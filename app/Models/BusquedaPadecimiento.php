<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusquedaPadecimiento extends Model
{
    protected $table = 'busqueda_padecimientos';

    protected $fillable = [
        'usuario_id',
        'termino_busqueda',
        'sintomas_seleccionados',
        'resultados',
        'fuente',
    ];

    protected $casts = [
        'sintomas_seleccionados' => 'array',
        'resultados' => 'array',
    ];

    // Relación: una búsqueda pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}