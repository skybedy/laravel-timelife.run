<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Result extends Model
{
    protected $casts = [
        'duplicity_check' => 'array',
    ];

    public function trackPoints()
    {
        return $this->hasMany(TrackPoint::class);
    }

    public function resultsOverallZal($eventId)
    {
        return self::join('registrations', 'results.registration_id', '=', 'registrations.id')
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->join('categories', 'registrations.category_id', '=', 'categories.id')
            ->selectRaw('results.id,MIN(SUBSTRING(results.finish_time,2)) AS best_finish_time,results.finish_time_sec as best_finish_time_sec,DATE_FORMAT(results.finish_time_date,"%e.%c.") AS date, registrations.id AS registration_id, users.firstname, users.lastname, users.team, users.id AS user_id,categories.name AS category_name,results.pace_km,COUNT(results.id) AS count')
            ->where('registrations.event_id', $eventId)
            ->groupBy('registrations.id')
            ->orderBy('best_finish_time')
            ->get();
    }


    public function resultsOverall($eventId)
    {
        $sql = "SELECT
        r1.registration_id,
        SUBSTRING(r1.finish_time,2) AS best_finish_time,
        r1.finish_time_sec as best_finish_time_sec,
        DATE_FORMAT(r1.finish_time_date,'%e.%c.') AS date,
        r1.pace_km,
        r1.id,
        r.category_id,
        u.lastname,
        u.firstname,
        u.team,
        c.name AS category_name,
        counts.count
    FROM results r1
    JOIN registrations r ON r1.registration_id = r.id
    JOIN users u ON r.user_id = u.id
    JOIN categories c ON r.category_id = c.id
    JOIN (
        SELECT registration_id, COUNT(*) AS count
        FROM results
        GROUP BY registration_id
    ) counts ON r1.registration_id = counts.registration_id
    WHERE
        r1.finish_time = (
            SELECT MIN(r2.finish_time)
            FROM results r2
            WHERE r1.registration_id = r2.registration_id
        ) AND r.event_id = ?
    ORDER BY best_finish_time asc";

      $result = self::hydrate(DB::select($sql, [$eventId]));

      return $result;
    }





    public function resultsIndividual($registrationId)
    {
        return self::selectRaw('id,SUBSTRING(finish_time,2) AS finish_time,pace_km,DATE_FORMAT(results.finish_time_date,"%e.%c.") AS date')
            ->where('registration_id', $registrationId)
            ->orderBy('finish_time_date','DESC')
            //->skip(1)
            ->take(PHP_INT_MAX)
            ->get();
    }


    public function finishTimeOrder($registrationId)
    {
        return self::where(['registration_id' => $registrationId])->first('id') + 1;
    }


   public function getAllUserResults($userId)
    {

     //   $events = Event::select('id', 'name')->where('platform_id' , env('PLATFORM_ID'))->orderBy('id','ASC')->get();

      //  dd($events);

      /*
      foreach($events as $event){

            return self::select('results.id', 'results.finish_time', 'results.finish_time_date', 'results.pace_km','registrations.event_id')
                ->where('registrations.user_id', $userId)
                ->where('registrations.event_id', $event->id)
                ->join('registrations', 'results.registration_id', '=', 'registrations.id')
                ->join('events', 'registrations.event_id', '=', 'events.id')
                ->join('categories', 'registrations.category_id', '=', 'categories.id')
                ->orderBy('results.finish_time_date', 'ASC')
                ->get();
        }*/

        return  DB::table('results as r')
        ->join('registrations as r2', 'r.registration_id', '=', 'r2.id')
        ->join('events as e', 'e.id', '=', 'r2.event_id')
        ->join('users as u', 'u.id', '=', 'r2.user_id')
        ->where('e.platform_id', env('PLATFORM'))
        ->where('u.id', $userId)
      //  ->orderBy('e.id','ASC')
        //->orderBy('r.finish_time_sec', 'ASC')
        ->orderBy('r.finish_time_date', 'ASC')
        ->select('r.*', 'e.name as race_name')
        ->get();

    }




}