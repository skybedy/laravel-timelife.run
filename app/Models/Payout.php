<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'stripe_payout_id',
        'payment_recipient_id',
        'amount',
        'currency',
        'arrival_date',
        'status',
        'type',
        'description',
        'stripe_data',
    ];

    protected $casts = [
        'arrival_date' => 'datetime',
        'stripe_data' => 'array',
        'amount' => 'integer',
    ];

    /**
     * Vztah k payment recipient
     */
    public function paymentRecipient()
    {
        return $this->belongsTo(PaymentRecepient::class, 'payment_recipient_id');
    }

    /**
     * Získat částku v Kč (z haléřů)
     */
    public function getAmountInCzkAttribute()
    {
        return $this->amount / 100;
    }
}
