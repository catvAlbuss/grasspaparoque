<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pay extends Model
{
    protected $fillable = [
        'amount',
        'payment_status',
        'payment_method',
        'payment_date',
    ];
}
