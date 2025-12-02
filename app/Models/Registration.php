<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = ['event_id', 'user_id', 'category_id', 'ids'];

    // V modelu Registration
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function registrationExists($eventId, $userId)
    {
        return self::where(['event_id' => $eventId, 'user_id' => $userId])->first('id');
    }
}