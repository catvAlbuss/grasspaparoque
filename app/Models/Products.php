<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_unit',
        'price_higher',
        'expiration_date',
    ];
}
