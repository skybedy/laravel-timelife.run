<?php

namespace App\Services;

use App\Exceptions\DuplicateFileException;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Result;
use App\Exceptions\SmallDistanceException;
use App\Exceptions\TimeIsOutOfRangeException;
use App\Exceptions\TimeMissingException;
use App\Exceptions\NoStravaAuthorizeException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use App\Exceptions\DuplicityException;
use App\Exceptions\DuplicityTimeException;
use App\Models\TrackPoint;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;



class ResultService
{

    protected $stravaService;
    protected $gpxService;

    public function __construct(StravaService $stravaService, GpxService $gpxService)
    {
        $this->stravaService = $stravaService;
        $this->gpxService = $gpxService;
    }

    public function getStreamFromStrava($request, $activityId = null)
    {
        return $this->stravaService->getStreamFromStrava($request, $activityId);
    }

    public function getActivityFinishDataFromGpx($args)
    {
        $request = $args['request'];
        $userId = $request->user()->id;
        $event = Event::where('id', $request->eventId)->first();
        $file = $request->file('gpx_file');

        return $this->gpxService->parseGpx($file->get(), $event, $userId);
    }

    public function getActivityIdFromStravaShareLink($shareLink)
    {
        return $this->stravaService->getActivityIdFromStravaShareLink($shareLink);
    }

    //otazka zda spis nevyvolat vyjimky a logovat v controlleru, asi predelat
    public function getActivityFinishDataFromStravaWebhook($activityData, $registration, $userId)
    {
        //pocatecni cas aktivity v UNIX sekundach
        $startDayTimestamp = strtotime($activityData['start_date_local']);
        //datum aktivity pro dotaz do DB
        $activityDate = date("Y-m-d", $startDayTimestamp);
        //pole pro ulozeni bodu trasy
        $trackPointArray = [];
        //vytvoreni noveho pole se stejnymi paramatry jak GPX soubor
        $activityDataArray = [];

        // vytvoreni pole ve stejne strukture jak GPX soubor
        foreach ($activityData['latlng']['data'] as $key => $val)
        {
            $activityDataArray[] = [
                    'latlng' => $val,
                    'time' => $activityData['time']['data'][$key] + $startDayTimestamp,
                    'distance' => $activityData['distance']['data'][$key],
                    'altitude' => $activityData['altitude']['data'][$key],
                    'cadence' => $activityData['cadence']['data'][$key]
                ];
        }
        //výpočet celkové vzdálenosti aktivity
        $activityDistance = $this->activityDistanceCalculation($activityDataArray);

        $events = Event::where('date_start', '<=',$activityDate)
                        ->where('date_end', '>=', $activityDate)
                        ->where('distance', '<=', $activityDistance)
                        ->orderBy('distance', 'DESC')
                        ->get(['id', 'distance']);
        //kontrola, jestli v daném časovém období existuje nějaký závod
        if (count($events) == 0)
        {
            Log::alert('Uzivatel '.$userId.' nahrál aktivitu, ale v daném časovém období a v patřičné délce neexistuje žádný závod.');

            exit();
        }
        //procházení závodů, jestli délkově odpovídají a jestli je k nim uzivatel prihlasen
        foreach ($events as $key => $event)
        {
            //kontrola, jestli je uzivatel k nemu prihlasen
            if (isset($registration->registrationExists($userId, $event['id'], NULL, NULL)->id))
            {
                //pokud ano, tak si vezmeme id registrace uzivatele k zavodu
                $registrationId = $registration->registrationExists($userId, $event['id'], NULL, NULL)->id;
                //prochazeni pole s daty aktivity
                foreach($activityDataArray as $activityData)
                {
                    //vytvorime TrackPointArray pro ulozeni do DB
                    $trackPointArray[] = [
                        'latitude' => $activityData['latlng'][0],
                        'longitude' => $activityData['latlng'][1],
                        'time' => $activityData['time'],
                        'altitude' => $activityData['altitude'],
                        'user_id' => $userId,
                        'cadence' => $activityData['cadence'],

                    ];
                    //pokud je vzdálenost větší než délka závodu, tak se vypocita cas a dal se v cyklu, ktery prochazi polem, nepokracuje
                    if($activityData['distance'] >= $event['distance'])
                    {
                        $finishTime = $this->gpxService->finishTimeCalculation($event['distance'],$activityData['distance'],$activityData['time'],$startDayTimestamp);

                        return [
                            'finish_time' => $finishTime['finish_time'],
                            'finish_time_sec' => $finishTime['finish_time_sec'],
                            'pace' => $finishTime['pace'],
                            'track_points' => $trackPointArray,
                            'registration_id' => $registrationId,
                            'finish_time_date' => $activityDate,
                        ];
                    }
                }
            }
            Log::alert('Uživatel '.$userId.' není prihlaseny k zadnemu zavodu v daném časovém období a odpovídající délce.');

            exit();
        }
    }

    private function activityDistanceCalculation($activityDataArray)
    {
        $lastPointLat = null;
        $lastPointLon = null;
        $currentPointLat = null;
        $currentPointLon = null;
        $distance = 0;

        foreach ($activityDataArray as $point) {
            $lastPointLat = $currentPointLat;
            $lastPointLon = $currentPointLon;
            $currentPointLat = floatval($point['latlng'][0]);
            $currentPointLon = floatval($point['latlng'][1]);

            if ($lastPointLat != null) {
                $pointDistance = round($this->gpxService->haversineGreatCircleDistance($lastPointLat, $lastPointLon, $currentPointLat, $currentPointLon), 1);
                $distance += $pointDistance;
            }
        }

        return $distance;
    }























    public function resultSave($request,$registrationId,$finishTime)
    {
        $result = new Result();

        $result->registration_id = $registrationId;

        $result->finish_time_date = $finishTime['finish_time_date'];

        $result->finish_time = $finishTime['finish_time'];

        $result->pace_km = $finishTime['pace'];

        // Added: Calculate pace per mile (1 mile = 1.60934 km)
        $paceParts = explode(':', $finishTime['pace']);
        $paceKmSeconds = ($paceParts[0] * 60) + $paceParts[1];

        $paceMileSeconds = round($paceKmSeconds * 1.60934);
        $paceMileMinutes = floor($paceMileSeconds / 60);
        $paceMileSecondsRemainder = $paceMileSeconds % 60;
        $result->pace_mile = $paceMileMinutes . ':' . str_pad($paceMileSecondsRemainder, 2, '0', STR_PAD_LEFT);

        $result->finish_time_sec = $finishTime['finish_time_sec'];

        DB::beginTransaction();

        try
        {
            $result->save();
        }
        catch(UniqueConstraintViolationException $e)
        {
            DB::rollback();
            // Duplicate result - user tried to upload the same activity twice
            return [
                'error' => 'DUPLICATE_RESULT',
                'error_message' => 'Tento výsledek (se stejným časem a datem) už byl nahrán.',
            ];
        }
        catch(QueryException $e)
        {
            DB::rollback();
            return [
                'error' => 'ERROR_DB',
                'error_message' => $e->getMessage(),
            ];
        }

        for($i = 0; $i < count($finishTime['track_points']); $i++)
        {
            $finishTime['track_points'][$i]['result_id'] = $result->id;
        }

        $trackPoint = new TrackPoint();

        try{
            $trackPoint::insert($finishTime['track_points']);

            DB::commit();
        }
        catch (UniqueConstraintViolationException $e)
        {
            if($e->errorInfo[1] == 1062)
            {
                DB::rollback();
                throw new DuplicityTimeException();
            }
        }

        $r = Result::where('registration_id', $registrationId)
        ->orderBy('finish_time', 'asc')
        ->get();

        $lastId = $result->id;
        foreach($r as $key => $value)
        {
            if($value->id == $lastId)
             {
                $rank = $key + 1;
            }

            Result::where('id', $value->id)->update(['finish_time_order' => $key + 1]);
        }

        $event = new Event();

        return [
            'results' =>  Result::selectRaw('id,DATE_FORMAT(finish_time_date,"%e.%c") AS date,finish_time')
            ->where('registration_id', $registrationId)
            ->orderBy('finish_time', 'asc')
            ->get(),
            'event' => $event::find($request->eventId),
            'last_id' => $lastId,
            'rank' => $rank
        ];
    }

    public function getSubdomain($url)
    {
        return $this->stravaService->getSubdomain($url);
    }

    public function getActivityId($string)
    {
        return $this->stravaService->getActivityId($string);
    }









    }