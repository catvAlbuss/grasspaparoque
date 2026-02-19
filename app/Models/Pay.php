<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pay extends Model
{
    protected $fillable = [
        'id_products',
        'amount',
        'payment_status',
        'payment_method',
        'payment_date',
    ];
    public function products() {
        return $this->belongsTo(Products::class,'id_products');
    }
}
