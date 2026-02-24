<?php

namespace App\Models;

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
        'payment_status',
        'payment_reviewed_at',
        'payment_proof_name',
        'payment_proof_number',
    ];

    public function evento()
    {
        return $this->belongsTo(Eventos::class, 'id_evento');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
    
    public function pay()
    {
        return $this->belongsTo(Pay::class, 'id_pay');
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    // Backward-compatible relation aliases used in existing code.
    public function eventos()
    {
        return $this->evento();
    }

    public function users()
    {
        return $this->user();
    }

    public function pays()
    {
        return $this->pay();
    }

    public function customers()
    {
        return $this->customer();
    }
}
