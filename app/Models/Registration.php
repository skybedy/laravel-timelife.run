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

    public function registrationExists($userId, $eventId, $platformId, $serieId)
    {
        if (is_null($serieId)) {
            $return = self::where(['user_id' => $userId, 'event_id' => $eventId])->first('id');
        } else {
            $return = self::join('events as e', 'e.id', '=', 'registrations.event_id')
                ->where('e.serie_id', $serieId)
                ->where('e.platform_id', $platformId)
                ->where('registrations.user_id', $userId)
                ->select('registrations.event_id')
                ->get();
        }

        return $return;
    }
}