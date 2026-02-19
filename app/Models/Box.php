<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    protected $fillable = [
        'id_pay',
        'id_reservations',
        'amount',
        'date_time'
    ];

    public function pays(){
        return $this->belongsTo(Pay::class,'id_pay');
    }

    public function reservations(){
        return $this->belongsTo(Reservation::class, 'id_reservations');
    }
}
