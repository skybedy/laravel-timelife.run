<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'registrations', 'event_id', 'user_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'event_id');
    }

    public function eventList($userId)
    {
        
        return self::select('events.id', 'events.name', 'events.date_start', 'events.date_end', 'events.second_name')
            ->leftJoin('registrations', function ($join) use ($userId) {
                $join->on('events.id', '=', 'registrations.event_id')
                    ->where('registrations.user_id', '=', $userId);
            })
            ->where('events.platform_id', '=', env('PLATFORM'))
            ->where('events.display', '=', 1)
            ->selectRaw('CASE WHEN registrations.user_id IS NULL THEN null ELSE 1 END as registration_status')
            ->orderBy('events.id', 'asc')
            ->get();
    }

    public function eventShow()
    {

        $eventId = 1;
        $userId = 1;

        $event = Event::with(['registrations' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->where('id', $eventId)
            ->first();

        return $event;

    }
}