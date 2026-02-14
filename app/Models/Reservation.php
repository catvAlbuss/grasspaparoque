<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'id_event',
        'id_user',
        'id_pay',
        'amount',
        'payment_status',
        'payment_method',
        'payment_date',
    ];
}
