<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Result;
use App\Models\TrackPoint;
use App\Models\User;
use App\Services\ResultService;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\DuplicateFileException;
use App\Exceptions\SmallDistanceException;
use App\Exceptions\TimeIsOutOfRangeException;
use App\Exceptions\TimeMissingException;
use App\Exceptions\DuplicityTimeException;
use Exception;


class StravaController extends Controller
{

    /**
     *   zpracování webhook  ze Stravy
    */
    public function webhookPostStrava(Request $request, ResultService $resultService, Registration $registration, TrackPoint $trackPoint, Event $event)
    {


        //pokud to neni 'create'tak to nechcem
        if ($request->input('aspect_type') != 'create')
        {
            exit();
        }

        // zaloguje se prijem dat ze Stravy a budeme logovat jen 'create'
        Log::info('Webhook event received!', ['query' => $request->query(),'body' => $request->all()]);
        //z pozadavku si vezmeme id uzivatele Stravy a podle nej najdeme uzivatele v nasi databazi
        $stravaId = $request->input('owner_id');
        //ziskani Usera 
        $user = User::select('id', 'strava_access_token', 'strava_refresh_token', 'strava_expires_at')->where('strava_id', $stravaId)->first();
        //ted musime zjistit, jestli token pro REST API jeste plati
        if ($user->strava_expires_at > time())
        {
            //pokud token platí, vytahneme stream
            $url = config('strava.stream.url').$request->input('object_id').config('strava.stream.params');

            $token = $user->strava_access_token;

            $response = Http::withToken($token)->get($url)->json();
            //pokud dostaneme v poradku stream, tak vytahneme i detail aktivity
            if ($response)
            {
                $url = config('strava.activity.url').$request->input('object_id').config('strava.activity.params');
                // k predchozimu streamu pridame detail aktivity
                $response += Http::withToken($token)->get($url)->json();

                $this->dataProcessing($resultService, $registration, $trackPoint, $event, $response, $user->id);
            }
        }
        else
        {

            $response = Http::post('https://www.strava.com/oauth/token', [
                'client_id' => '117954',
                'client_secret' => 'a56df3b8bb06067ebe76c7d23af8ee8211d11381',
                'refresh_token' => $user->strava_refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            $body = $response->body();
            
            $content = json_decode($body, true);

            $user1 = User::where('id', $user->id)->first();
            
            $user1->strava_access_token = $content['access_token'];
            
            $user1->strava_refresh_token = $content['refresh_token'];
            
            $user1->strava_expires_at = $content['expires_at'];
            
            $user1->save();

            $url = config('strava.stream.url').$request->input('object_id').config('strava.stream.params');

            $token = $user1->strava_access_token;

            $response = Http::withToken($token)->get($url)->json();
            //pokud dostaneme v poradku stream, tak vytahneme i detail aktivity
            if ($response)
            {
                $url = config('strava.activity.url').$request->input('object_id').config('strava.activity.params');
                // k predchozimu streamu pridame detail aktivity
                $response += Http::withToken($token)->get($url)->json();

                $this->dataProcessing($resultService, $registration, $trackPoint, $event, $response, $user->id);
            }



        }

        return response('OK', 200);
    }
    









    //  public function dataProcessing(ResultService $resultService,Registration $registration,TrackPoint $trackPoint,Event $event)

    public function dataProcessing($resultService, $registration, $trackPoint, $event, $dataStream, $userId)
    {

        //return $dataStream;
        $finishTime = $resultService->getActivityFinishDataFromStravaWebhook($dataStream, $registration, $userId);

        $result = new Result();

        $result->registration_id = $finishTime['registration_id'];

        $result->finish_time_date = $finishTime['finish_time_date'];

        $result->finish_time = $finishTime['finish_time'];

        $result->pace = $finishTime['pace'];

        $result->finish_time_sec = $finishTime['finish_time_sec'];

        DB::beginTransaction();

        try
        {
            $result->save();
        }
        catch (QueryException $e)
        {
            Log::alert('Došlo k problému s nahráním dat', ['error' => $e->getMessage()]);

            exit();
        }

        for ($i = 0; $i < count($finishTime['track_points']); $i++)
        {
            $finishTime['track_points'][$i]['result_id'] = $result->id;
        }

        try {
            $trackPoint::insert($finishTime['track_points']);

            DB::commit();
        }
        catch (UniqueConstraintViolationException $e)
        {
            if ($e->errorInfo[1] == 1062)
            {
                DB::rollback();

                Log::alert('Uzivatel '.$userId.' se pokusil nahrál aktivitu, ale ta už v databazi je.');

                exit();
            }
        }
        
        Log::info('Uzivatel '.$userId.' nahral aktivitu.');
    }

    public function getStrava(Request $request)
    {

        Log::info($request->query());
        $VERIFY_TOKEN = 'STRAVA';

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        //        if ($mode && $token) {
        if ($mode === 'subscribe' && $token === $VERIFY_TOKEN) {
            Log::info('WEBHOOK_VERIFIED');

            return response()->json(['hub.challenge' => $challenge]);
        } else {

            Log::info('neco-spatne');

            return response('Forbidden', 403);
        }
        // }
        //   else{
        //     \Log::info('neco-spatne-tu');
        //}
    }










    public function autouploadStrava(ResultService $resultService, Registration $registration, TrackPoint $trackPoint, Event $event)
    {

        $url = 'https://www.strava.com/api/v3/activities/10531467027/streams?keys=time,latlng,altitude&key_by_type=true';
        $token = 'd0bc94aeba4a6d704c3620ce286b1a3530b78f9b';
        $response = Http::withToken($token)->get($url)->json();
        //dd($response);
        if ($response) {
            $url = 'https://www.strava.com/api/v3/activities/10531467027?include_all_efforts=false';
            $token = 'd0bc94aeba4a6d704c3620ce286b1a3530b78f9b';
            $response += Http::withToken($token)->get($url)->json();
            // dd($response);

            $user = $this->getUserByStravaId(100148951);

            $finishTime = $resultService->getActivityFinishDataFromStravaWebhook($response, $registration, $user->id);

            $result = new Result();

            $result->registration_id = $finishTime['registration_id'];

            $result->finish_time_date = $finishTime['finish_time_date'];

            $result->finish_time = $finishTime['finish_time'];

            $result->pace = $finishTime['pace'];

            $result->finish_time_sec = $finishTime['finish_time_sec'];

            DB::beginTransaction();

            try
            {
                $result->save();
            }
            catch (QueryException $e)
            {
                Log::alert('Došlo k problému s nahráním dat', ['error' => $e->getMessage()]);
            }

            for ($i = 0; $i < count($finishTime['track_points']); $i++)
            {
                $finishTime['track_points'][$i]['result_id'] = $result->id;
            }

            try {
                $trackPoint::insert($finishTime['track_points']);

                DB::commit();
            }
            catch (UniqueConstraintViolationException $e)
            {
                if ($e->errorInfo[1] == 1062)
                {
                    DB::rollback();

                    Log::alert('Uzivatel '.$user->id.' se pokusil nahrál aktivitu, ale ta už v databazi je.');
                }
            }

        }


        Log::info('Uzivatel '.$user->id.' nahral aktivitu.');


    }


    private function getUserByStravaId($stravaId)
    {
        return User::select('id', 'strava_access_token', 'strava_refresh_token', 'strava_expires_at')->where('strava_id', $stravaId)->first();
    }

    public function redirectStrava(Request $request)
    {

        $response = Http::post('https://www.strava.com/oauth/token', [
            'client_id' => '117954',
            'client_secret' => 'a56df3b8bb06067ebe76c7d23af8ee8211d11381',
            'code' => $request->query('code'),
            'grant_type' => 'authorization_code',
        ]);

        $body = $response->body();
        $content = json_decode($body, true);
        //dd($content);

        $user = User::find($request->userId);
        $user->strava_id = $content['athlete']['id'];
        $user->strava_access_token = $content['access_token'];
        $user->strava_refresh_token = $content['refresh_token'];
        $user->strava_expires_at = $content['expires_at'];
        $user->strava_scope = $request->query('scope');
        $user->save();

        return redirect('/');

    }

    //simulace autonahrani ze Stravy

    public function authorizeStrava(Request $request)
    {
        return redirect('https://www.strava.com/oauth/authorize?client_id=117954&response_type=code&redirect_uri='.env('APP_URL').'/redirect-strava/'.$request->user()->id.'&approval_prompt=force&scope=activity:read_all');
    }
}