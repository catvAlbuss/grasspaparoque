<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eventos extends Model
{
    protected $fillable = [
        'nombre',
        'precio',
        'descripcion',
        'estado',
    ];
}
