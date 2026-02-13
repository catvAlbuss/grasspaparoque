<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class eventos extends Model
{
    protected $fillable = [
        // 'id',
        'nombre',
        'precio',
        'descripcion',
        'estado',
    ];
}
