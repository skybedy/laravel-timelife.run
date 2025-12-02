<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Registration extends Model
{
    protected $fillable = ['event_id', 'user_id', 'category_id','ids'];

    // V modelu Registration
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /*
    * Jestli uz existuje nejaka registrace pro platformu a serii, at vime, jestli se ma platit startovne
    */
    public function someRegistrationExists($platform_id,$event_id,$user_id)
    {
        $sql = "SELECT EXISTS(
                    SELECT 1 from registrations r, events e WHERE
                    e.platform_id = ? AND
                    e.serie_id = (select serie_id from events e where id = ?) AND
                    e.id = r.event_id AND
                    r.user_id = ?) as result";

        $dbData = DB::select($sql,[$platform_id,$event_id,$user_id]);

        return (bool)$dbData[0]->result;
    }


    public function startNumber($platform_id,$event_id,$user_id)
    {
        $sql = "SELECT r.ids FROM registrations r, events e
                WHERE
                    e.platform_id = ? AND
                    e.serie_id = (SELECT serie_id FROM events e WHERE id = ?) AND
                    e.id = r.event_id AND
                    r.user_id = ? LIMIT 1";

        $dbData = DB::select($sql,[$platform_id,$event_id,$user_id]);

        if(isset($dbData[0]->ids))
        {
            return $dbData[0]->ids;
        }
        else
        {
            $max_ids = DB::select("SELECT MAX(ids) as max_ids FROM registrations r, events e
                            WHERE
                                e.platform_id = ? AND
                                e.serie_id = (SELECT serie_id FROM events e WHERE id = ?) AND
                                e.id = r.event_id",[$platform_id,$event_id]);

            return $max_ids[0]->max_ids + 1;
        }

    }


    public function startNumberZal($event_id,$user_id)
    {

    $user_exists_ids = self::select('ids')
    ->where('user_id', $user_id)
    ->whereIn('event_id', function($query) {
        $query->select('id')
            ->from('events')
            ->where('serie_id', function($subQuery) {
                $subQuery->select('serie_id')
                    ->from('events')
                    ->where('id', 5);
            });
    })
    ->pluck('ids')
    ->first();

    if(!is_null($user_exists_ids))
    {
        return $user_exists_ids;
    }
    else
    {
        $max_ids =  self::whereHas('event', function ($query) use ($event_id) {
            $query->where('serie_id', function ($subquery) use ($event_id) {
                $subquery->select('serie_id')
                         ->from('events')
                         ->where('id', $event_id)
                         ->limit(1);
            });
        })
        ->max("ids");

        if($max_ids == null)
        {
            return 1;
        }
        else
        {
            return $max_ids + 1;
        }

    }





    }




    public function eventRegistrationExists($userId, $eventId)
    {
        return self::where(['user_id' => $userId, 'event_id' => $eventId])->exists();
    }

   //na kazdy zavod zvlast
    public function registrationExists($userId, $eventId, $platformId, $serieId)
    {

        if(is_null($serieId))
        {
            $return =  self::where(['user_id' => $userId,'event_id' => $eventId])->first('id');
        }
        else
        {
            $return = self::join('events as e', 'e.id', '=', 'registrations.event_id')
                ->where('e.serie_id', $serieId)
                ->where('e.platform_id', $platformId)
                ->where('registrations.user_id', $userId)
                ->select('registrations.event_id')
                ->get();


        }

        return $return;
    }

    public function bulkInsert(array $registrations)
    {
        return self::insert($registrations);
    }



    function generateReferenceNumber(): int
{
    $year = date('Y'); // 2025

    return DB::transaction(function () use ($year) {
        // 1) Zamkneme a najdeme největší reference_number pro aktuální rok
        $row = DB::selectOne("
            SELECT reference_number 
            FROM payments 
            WHERE reference_number >= ? AND reference_number < ?
            ORDER BY reference_number DESC 
            LIMIT 1 
            FOR UPDATE
        ", [$year.'000000', $year.'999999']);

        if ($row) {
            $next = $row->reference_number + 1;
        } else {
            // První platba v roce
            $next = (int)($year . '000001');
        }

        return $next;
        // → 2025000001, 2025000002, …
    });
}



}
