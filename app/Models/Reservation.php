<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    public const PENDING_TIMEOUT_MINUTES = 10;

    protected $fillable = [
        'id_evento',
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

    public function pays()
    {
        return $this->pay();
    }

    public function customers()
    {
        return $this->customer();
    }

    public function scopeBlocking(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('reservation_status', 'busy')
                ->orWhere(function (Builder $query) {
                    $query->where('payment_status', 'pending')
                        ->where('created_at', '>', now()->subMinutes(self::PENDING_TIMEOUT_MINUTES));
                });
        });
    }

    public static function expirePending(): int
    {
        return static::query()
            ->where('payment_status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(self::PENDING_TIMEOUT_MINUTES))
            ->update([
                'payment_status' => 'rejected',
                'reservation_status' => 'free',
                'payment_reviewed_at' => now(),
            ]);
    }
}
