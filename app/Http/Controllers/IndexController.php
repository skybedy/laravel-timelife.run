<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateFileException;
use App\Exceptions\SmallDistanceException;
use App\Exceptions\TimeIsOutOfRangeException;
use App\Exceptions\TimeMissingException;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Result;
use App\Models\TrackPoint;
use App\Services\ResultService;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request, Event $event)
    {
        // Statistiky pro registration_id 131
        $stats = DB::table('results')
            ->where('registration_id', 131)
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(finish_time_sec) as total_seconds,
                AVG(TIME_TO_SEC(STR_TO_DATE(pace_km, "%i:%s"))) as avg_pace_seconds
            ')
            ->first();

        // Převod celkového času ze sekund na formát HH:MM:SS
        $totalTime = null;
        if ($stats->total_seconds) {
            $hours = floor($stats->total_seconds / 3600);
            $minutes = floor(($stats->total_seconds % 3600) / 60);
            $seconds = $stats->total_seconds % 60;
            $totalTime = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        // Převod průměrného tempa ze sekund na formát MM:SS
        $avgPace = null;
        if ($stats->avg_pace_seconds) {
            $minutes = floor($stats->avg_pace_seconds / 60);
            $seconds = round($stats->avg_pace_seconds % 60);
            $avgPace = sprintf('%d:%02d', $minutes, $seconds);
        }

        // Celkový počet km (počet půlmaratonů × 21.0975)
        $totalKm = number_format(($stats->total_count ?? 0) * 21.0975, 2, ',', ' ');

        if ($request->user() == null) {
            return view('index.index', [
                'events' => $event::where('platform_id', env('PLATFORM',3))->where('display', 1)->get(),
                'totalRaces' => $stats->total_count ?? 0,
                'totalKm' => $totalKm,
                'totalTime' => $totalTime ?? '0:00:00',
                'avgPace' => $avgPace ?? '0:00'
            ]);
        }
        else
        {
            return view('index/index', [
                //'events' => $event->eventList($request->user()->id),
                'events' => $event::where('platform_id', env('PLATFORM',3))->where('display', 1)->get(), // pri pouziti predchoziho prikazu se to vypisovalo trikrat, ted to neresim
                'totalRaces' => $stats->total_count ?? 0,
                'totalKm' => $totalKm,
                'totalTime' => $totalTime ?? '0:00:00',
                'avgPace' => $avgPace ?? '0:00'
            ]);
        }

    }

    public function story()
    {
        return view('index.story');
    }

    public function autodistanceUpload(Request $request, ResultService $resultService, Registration $registration, TrackPoint $trackPoint, Event $event)
    {
        $request->validate([
            'place' => 'required|string|max:100',
        ]);

        /*
       try
       {
           $finishTime = $resultService->overallDistance($request,$registration);
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
       catch (UniqueConstraintViolationException $e) {
           //dd('tu');
           $errorCode = $e->errorInfo[1];
           if($errorCode == 1062){
               // Duplicitní záznam byl nalezen, zde můžete provést potřebné akce
               // Například můžete záznam přeskočit, aktualizovat nebo vrátit chybovou zprávu uživateli
             //  dd($errorCode);
           }
       }*/

        $finishTime = $resultService->overallDistance($request, $registration);

        $result = new Result();
        $result->registration_id = $finishTime['registration_id'];
        $result->finish_time_date = $finishTime['finish_time_date'];
        $result->finish_time = $finishTime['finish_time'];
        $result->pace_km = $finishTime['pace'];

        // Calculate pace per mile (1 mile = 1.60934 km)
        $paceParts = explode(':', $finishTime['pace']);
        $paceKmSeconds = ($paceParts[0] * 60) + $paceParts[1];
        $paceMileSeconds = round($paceKmSeconds * 1.60934);
        $paceMileMinutes = floor($paceMileSeconds / 60);
        $paceMileSecondsRemainder = $paceMileSeconds % 60;
        $result->pace_mile = $paceMileMinutes . ':' . str_pad($paceMileSecondsRemainder, 2, '0', STR_PAD_LEFT);

        $result->finish_time_sec = $finishTime['finish_time_sec'];

        DB::beginTransaction();

        try {
            $result->save();
        } catch (UniqueConstraintViolationException $e) {
            DB::rollback();
            return back()->withError('Tento výsledek (se stejným časem a datem) už byl nahrán.')->withInput();
        } catch (QueryException $e) {
            DB::rollback();
            return back()->withError('Došlo k problému s nahráním souboru, kontaktujte timechip.cz@gmail.com')->withInput();
        }

        for ($i = 0; $i < count($finishTime['track_points']); $i++) {
            $finishTime['track_points'][$i]['result_id'] = $result->id;
        }

      // $trackPoint::insert($finishTime['track_points']);

        try {
            $trackPoint::insert($finishTime['track_points']);
            DB::commit();
        } catch (UniqueConstraintViolationException $e) {

            dd($e);
            if ($e->errorInfo[1] == 1062) {
                DB::rollback();

                return back()->withError('Soubor obsahuje duplicitní časové údaje')->withInput();
            }
        }

        $r = Result::where('registration_id', $finishTime['registration_id'])
            ->orderBy('finish_time', 'asc')
            ->get();

        $lastId = $result->id;
        foreach ($r as $key => $value) {
            if ($value->id == $lastId) {
                $rank = $key + 1;
            }

            Result::where('id', $value->id)->update(['finish_time_order' => $key + 1]);
        }

    }
}
