<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateFileException;
use App\Exceptions\SmallDistanceException;
use App\Exceptions\TimeIsOutOfRangeException;
use App\Exceptions\TimeMissingException;
use App\Exceptions\DuplicityTimeException;
use App\Exceptions\NoStravaAuthorizeException;
use App\Exceptions\NoRaceRegistrationException;
use App\Exceptions\StravaPrivateException;
use Exception;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Result;
use App\Models\Startlist;
use App\Models\TrackPoint;
use App\Models\User;
use App\Services\ResultService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Event $event)
    {
        if ($request->user() == null)
        {
            return view('events.index', [
                'events' => $event::All(),
            ]);
        }
        else
        {
            return view('events.index', [
                'events' => $event->eventList($request->user()->id),
            ]);
        }

    }

    public function show(Request $request, Event $event)
    {
        return view('events.show', [
            'event' => $event::find($request->eventId),
            'all_same_serie_events' => $event->allSameSeriesEvents($request->eventId),
        ]);
    }

    public function uploadUrlCreate(Request $request, Event $event)
    {
        return view('events.results.upload-url-create', [
            'event' => $event::find($request->eventId),
            'all_same_serie_events' => $event->allSameSeriesEvents($request->eventId),
        ]);
    }

    public function uploadFileCreate(Request $request, Event $event)
    {
        return view('events.results.upload-file-create', [
            'event' => $event::find($request->eventId),
            'all_same_serie_events' => $event->allSameSeriesEvents($request->eventId),

        ]);
    }



    public function uploadStoreFromUrl(Request $request, ResultService $resultService, Registration $registration, TrackPoint $trackPoint, Event $event)
    {
        //nejprve validace
        $request->validate(
            [
                'strava_url' => 'required',
            ],
            [
                'strava_url.required' => 'Je nutné vyplnit odkaz ze Stravy.',
            ]
        );

        try // kontrola, zda uzivatel je registrovan na zavod
        {
            if (isset($registration->registrationExists($request->user()->id,$request->eventId, NULL,NULL)->id))
            {
                $registrationId = $registration->registrationExists($request->user()->id,$request->eventId, NULL,NULL)->id;
            }
            else
            {
                throw new NoRaceRegistrationException($request->eventId);
            }
        }
        catch(NoRaceRegistrationException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }

        //urceni, zda jde o link z prohlizece nebo z apky
        $subdomain = $resultService->getSubdomain($request['strava_url']);
        // ziskani id aktivity
        if ($subdomain == 'www')
        {
            $activityId = $resultService->getActivityId($request['strava_url']);
        }
        elseif ($subdomain == 'strava')
        {
            $activityId = $resultService->getActivityIdFromStravaShareLink($request['strava_url']);
        }
        else
        {
            //'nejaky problem s url');
        }

        try{
            $activityData = $resultService->getStreamFromStrava($request, $activityId);
        }
        catch (NoStravaAuthorizeException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }


        try
        {
            $finishTime = $this->activityFinishTime($resultService,'getActivityFinishDataFromStravaStream',['activity_data' => $activityData,'request' => $request,'registrationId' => $registrationId]);
        }
        catch (SmallDistanceException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (TimeIsOutOfRangeException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (DuplicateFileException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (TimeMissingException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (StravaPrivateException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (UniqueConstraintViolationException $e)
        {
            $errorCode = $e->errorInfo[1];

            if ($errorCode == 1062)
            {
                // Duplicitní záznam byl nalezen, zde můžete provést potřebné akce
                // Například můžete záznam přeskočit, aktualizovat nebo vrátit chybovou zprávu uživateli
            }
        }

        try
        {
            $resultSave = $resultService->resultSave($request, $registrationId, $finishTime);
        }
        catch (DuplicityTimeException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }


        if (isset($resultSave['error']))
        {
            if ($resultSave['error'] == 'ERROR_DB')
            {
                return back()->withError('Došlo k problému s nahráním souboru, kontaktujte timechip.cz@gmail.com')->withInput();
            }
            elseif ($resultSave['error'] == 'DUPLICATE_RESULT')
            {
                return back()->withError($resultSave['error_message'])->withInput();
            }
        }


        return view('events.results.post-upload', [
            'results' => $resultSave['results'],
            'event' => $resultSave['event'],
            'last_id' => $resultSave['last_id'],
            'rank' => $resultSave['rank'],
            'all_same_serie_events' => $event->allSameSeriesEvents($request->eventId),
        ]);


    }



    private function activityFinishTime($resultService,$methodName,$args)
    {
        //try
        //{

            if (method_exists($resultService, $methodName))
            {
                $finishTime = call_user_func([$resultService, $methodName], $args);
            }
            else
            {
                throw new Exception("Metoda neexistuje");
            }


       /* }
        catch (SmallDistanceException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (TimeIsOutOfRangeException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (DuplicateFileException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (TimeMissingException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (UniqueConstraintViolationException $e)
        {
            $errorCode = $e->errorInfo[1];

            if ($errorCode == 1062)
            {
                // Duplicitní záznam byl nalezen, zde můžete provést potřebné akce
                // Například můžete záznam přeskočit, aktualizovat nebo vrátit chybovou zprávu uživateli
            }
        }*/

        return $finishTime;

    }


    public function uploadStore(Request $request, ResultService $resultService, Registration $registration, TrackPoint $trackPoint, Event $event)
    {

        $request->validate(
            [
                'gpx_file' => 'required|max:10000',
            ],
            [
                'gpx_file.required' => 'Nebyl vybrán žádný soubor.',
            ]);


        try // kontrola, zda uzivatel je registrovan na zavod
        {
            if (isset($registration->registrationExists($request->user()->id,$request->eventId,NULL, NULL)->id))
            {
                $registration_id = $registration->registrationExists($request->user()->id,$request->eventId,NULL,NULL)->id;
            }
                    else
            {
                throw new NoRaceRegistrationException($request->eventId);
            }
        }
        catch(NoRaceRegistrationException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }

        try
        {
            $finishTime = $this->activityFinishTime($resultService,'getActivityFinishDataFromGpx',['request' => $request,'registration_id' => $registration_id]);
        }
        catch (SmallDistanceException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (TimeIsOutOfRangeException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (DuplicateFileException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }
        catch (TimeMissingException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }

        try
        {
            $resultSave = $resultService->resultSave($request, $registration_id, $finishTime);
        }
        catch (DuplicityTimeException $e)
        {
            return back()->withError($e->getMessage())->withInput();
        }





        if (isset($resultSave['error']))
        {
            if ($resultSave['error'] == 'ERROR_DB')
            {
                return back()->withError('Došlo k problému s nahráním souboru, kontaktujte timechip.cz@gmail.com')->withInput();
            }
            elseif ($resultSave['error'] == 'DUPLICATE_RESULT')
            {
                return back()->withError($resultSave['error_message'])->withInput();
            }
        }


      return view('events.results.post-upload', [
            'results' => $resultSave['results'],
            'event' => $resultSave['event'],
            'last_id' => $resultSave['last_id'],
            'rank' => $resultSave['rank'],
            'all_same_serie_events' => $event->allSameSeriesEvents($request->eventId),

        ]);

    }

    public function resultIndex(Request $request, Result $result, Event $event)
    {

        $eventType = $result->resultsOverall($request->eventId)['event_type'];

        switch($eventType)
        {
            case 1:
                return view('events.results.result-index-1', [
                    'results' => $result->resultsOverall($request->eventId)['results'],
                    'eventType' => $eventType,
                    'event' => $event::select("id","name","event_type_id")->find($request->eventId),
                    'all_same_serie_events' => $event->allSameSeriesEvents($request->eventId),
                ]);

            case 2:
                return view('events.results.result-index-2', [
                    'results' => $result->resultsOverall($request->eventId)['results'],
                    'eventType' => $eventType,
                    'event' => $event::select("id","name")->find($request->eventId),
                    'all_same_serie_events' => $event->allSameSeriesEvents($request->eventId),
                ]);
            }
    }




    public function startlistIndex(Request $request, Registration $registration, Event $event, Startlist $startlist)
    {
        return view('events.start-list', [
            'startlists' => $startlist->startlist($request->eventId),
            'event' => $event::find($request->eventId),
            'all_same_serie_events' => $event->allSameSeriesEvents($request->eventId),

        ]);
    }
}


