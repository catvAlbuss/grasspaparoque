<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eventos extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'precio',
        'descripcion',
        'estado',
        'tipo_ambiente',
        'ambiente_grupo',
    ];

    protected $casts = [
        'ambiente_grupo' => 'integer',
    ];
}
