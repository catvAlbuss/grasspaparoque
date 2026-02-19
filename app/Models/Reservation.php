<?php

namespace App\Models;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\UserController;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'id_evento',
        'id_user',
        'id_pay',
        'id_customer',
        'start_time',
        'end_time',
        'date',
        'reservation_status',
    ];

    public function eventos()
    {
        return $this->belongsTo(Eventos::class, 'id_evento');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
    
    public function pays()
    {
        return $this->belongsTo(Pay::class, 'id_pay');
    }
    
    public function customers()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

}
