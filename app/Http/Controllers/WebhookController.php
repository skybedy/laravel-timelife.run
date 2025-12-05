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

class WebhookController extends Controller
{
    public function autouploadStrava(ResultService $resultService, Registration $registration, TrackPoint $trackPoint, Event $event)
    {

        $url = 'https://www.strava.com/api/v3/activities/10503391125?include_all_efforts=true';
        $token = 'f2c183e83f7e1528e6a135af1520c928cc304b00';
        $response = Http::withToken($token)->get($url);
        $data = $response->json();

        $finishTime = $resultService->dataStravaProcessing($response->json(), $registration);

        $result = new Result();
        $result->registration_id = $finishTime['registration_id'];
        $result->finish_time_date = $finishTime['finish_time_date'];
        $result->finish_time = $finishTime['finish_time'];
        $result->pace = $finishTime['pace'];
        $result->finish_time_sec = $finishTime['finish_time_sec'];
        // $result->duplicity_check = $finishTime['duplicity_check'];
        $result->place = 'Nevim';

        // dd($result);

        DB::beginTransaction();

        try {
            $result->save();
        } catch (QueryException $e) {
            dd($e);

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

    public function getStrava(Request $request)
    {

        \Log::info($request->query());
        $VERIFY_TOKEN = 'STRAVA';

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        //        if ($mode && $token) {
        if ($mode === 'subscribe' && $token === $VERIFY_TOKEN) {
            \Log::info('WEBHOOK_VERIFIED');

            return response()->json(['hub.challenge' => $challenge]);
        } else {

            \Log::info('neco-spatne');

            return response('Forbidden', 403);
        }
        // }
        //   else{
        //     \Log::info('neco-spatne-tu');
        //}
    }

    public function postStrava(Request $request, ResultService $resultService)
    {

        \Log::info('Webhook event received!', [
            'query' => $request->query(),
            'body' => $request->all(),
        ]);

        try {
            // Get activity stream data from Strava
            $activityData = $resultService->getStreamFromStrava($request);

            if (!$activityData) {
                \Log::error('Failed to fetch activity data from Strava', [
                    'activity_id' => $request->input('object_id'),
                    'owner_id' => $request->input('owner_id'),
                ]);
                return response()->json(['error' => 'Failed to fetch activity data'], 500);
            }

            // Check if activity has required stream data
            if (!isset($activityData['latlng']['data']) || empty($activityData['latlng']['data'])) {
                \Log::info('Activity has no GPS data (indoor activity?)', [
                    'activity_id' => $request->input('object_id'),
                    'type' => $activityData['type'] ?? 'unknown',
                ]);
                return response()->json(['message' => 'Activity has no GPS data, skipping'], 200);
            }

            $userId = $activityData['user_id'];

            // Get user's registration (you may need to adjust this logic based on your business rules)
            $registration = Registration::where('user_id', $userId)
                ->whereHas('event', function($query) use ($activityData) {
                    $activityDate = date('Y-m-d', strtotime($activityData['start_date']));
                    $query->where('date_start', '<=', $activityDate)
                          ->where('date_end', '>=', $activityDate);
                })
                ->first();

            if (!$registration) {
                \Log::info('No matching registration found for activity', [
                    'activity_id' => $request->input('object_id'),
                    'user_id' => $userId,
                ]);
                return response()->json(['message' => 'No matching registration found'], 200);
            }

            // Process the activity data
            $finishTime = $resultService->getActivityFinishDataFromStravaWebhook($activityData, $registration, $userId);

            // Save result to database
            $result = new Result();
            $result->registration_id = $finishTime['registration_id'];
            $result->finish_time_date = $finishTime['finish_time_date'];
            $result->finish_time = $finishTime['finish_time'];
            $result->pace = $finishTime['pace'];
            $result->finish_time_sec = $finishTime['finish_time_sec'];
            $result->place = null; // Will be calculated later

            DB::beginTransaction();

            try {
                $result->save();

                // Add result_id to track points
                for ($i = 0; $i < count($finishTime['track_points']); $i++) {
                    $finishTime['track_points'][$i]['result_id'] = $result->id;
                }

                // Insert track points
                TrackPoint::insert($finishTime['track_points']);

                // Update finish_time_order for all results of this registration
                $results = Result::where('registration_id', $finishTime['registration_id'])
                    ->orderBy('finish_time', 'asc')
                    ->get();

                foreach ($results as $key => $value) {
                    Result::where('id', $value->id)->update(['finish_time_order' => $key + 1]);
                }

                DB::commit();

                \Log::info('Activity processed and saved successfully', [
                    'activity_id' => $request->input('object_id'),
                    'user_id' => $userId,
                    'result_id' => $result->id,
                    'finish_time' => $finishTime['finish_time'],
                ]);

                return response()->json(['message' => 'Activity processed successfully'], 200);

            } catch (UniqueConstraintViolationException $e) {
                DB::rollback();
                \Log::warning('Duplicate track point time detected', [
                    'activity_id' => $request->input('object_id'),
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['error' => 'Duplicate time data in activity'], 409);
            } catch (QueryException $e) {
                DB::rollback();
                \Log::error('Database error while saving result', [
                    'activity_id' => $request->input('object_id'),
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['error' => 'Database error'], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error processing Strava webhook', [
                'activity_id' => $request->input('object_id'),
                'owner_id' => $request->input('owner_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /*

{"token_type":"Bearer","access_token":"f2c183e83f7e1528e6a135af1520c928cc304b00","expires_at":1704744984,"expires_in":21600,"refresh_token":"5a0de4bd8330b7b31e58c1895fd49d44ce111fc0"}skybedy@skybedy-Latitude-E6520:~$ ^C
*/

}
