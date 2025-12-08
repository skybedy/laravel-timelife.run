<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'donor_email',
        'donor_name',
        'total_amount',
        'payout_amount',
        'fee_amount',
        'event_id',
        'payment_recipient_id',
        'stripe_session_id',
        'payment_reference_id',
        'is_live',
    ];

    /**
     * Vztah k uživateli (nullable pro anonymní dárce)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vztah k příjemci platby
     */
    public function paymentRecipient()
    {
        return $this->belongsTo(PaymentRecepient::class);
    }

    /**
     * Vztah k eventu
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Získat email dárce (z users nebo donor_email)
     */
    public function getEffectiveDonorEmail()
    {
        return $this->user_id
            ? $this->user->email
            : $this->donor_email;
    }

    /**
     * Získat jméno dárce
     */
    public function getEffectiveDonorName()
    {
        return $this->user_id
            ? $this->user->firstname . ' ' . $this->user->lastname
            : $this->donor_name;
    }

    /**
     * Scope - jen platby od anonymních dárců
     */
    public function scopeAnonymous($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope - jen platby od registrovaných uživatelů
     */
    public function scopeRegistered($query)
    {
        return $query->whereNotNull('user_id');
    }
}
