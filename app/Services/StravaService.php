<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Exceptions\NoStravaAuthorizeException;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class StravaService
{
    /**
     * Získá stream dat z aktivity na Stravě.
     * Automaticky obnovuje token, pokud vypršel.
     */
    public function getStreamFromStrava($request, $activityId = null)
    {
        if ($activityId == null) {
            $activityId = $request->input('object_id');
            $stravaId = $request->input('owner_id');

            $user = User::select('id', 'strava_access_token', 'strava_refresh_token', 'strava_expires_at')
                ->where('strava_id', $stravaId)
                ->first();

            $userId = $user->id;
        } else {
            $userId = $request->user()->id;

            $user = User::select('id', 'strava_access_token', 'strava_refresh_token', 'strava_expires_at')
                ->where('id', $userId)
                ->first();
        }

        // Kontrola, jestli uživatel má autorizovanou aplikaci na Stravě
        if (is_null($user->strava_access_token)) {
            throw new NoStravaAuthorizeException();
        };

        if ($user->strava_expires_at > time()) {
            return $this->fetchStravaData($user->strava_access_token, $activityId, $userId);
        } else {
            // Token expiroval, provedeme refresh
            $token = $this->refreshStravaToken($user);
            return $this->fetchStravaData($token, $activityId, $userId);
        }
    }

    /**
     * Interal helper to fetch data once we have a valid token
     */
    private function fetchStravaData($token, $activityId, $userId)
    {
        $urlStream = config('strava.stream.url') . $activityId . config('strava.stream.params');
        $response = Http::withToken($token)->get($urlStream)->json();

        if ($response) {
            $urlActivity = config('strava.activity.url') . $activityId . config('strava.activity.params');
            $responseActivity = Http::withToken($token)->get($urlActivity)->json();
            
            // Merge arrays safely
            if (is_array($responseActivity)) {
                $response += $responseActivity;
            }
            
            $response['user_id'] = $userId;
        }
        
        return $response;
    }

    /**
     * Refreshes Strava token for a user
     */
    public function refreshStravaToken(User $user)
    {
        $urlToken = config('strava.token.url');
        $params = config('strava.token.params');
        $params['refresh_token'] = $user->strava_refresh_token;

        $responseToken = Http::post($urlToken, $params);
        $content = $responseToken->json(); // Use json helper instead of json_decode($body)

        // Assuming User model has updateStravaToken method or we do it manually
        // If Model method exists:
        // $token = $user->updateStravaToken($user->id, $content);
        
        // Manual update if model method is just returning token (as seen in original code)
        // Updating the user model directly here
        $user->strava_access_token = $content['access_token'];
        $user->strava_refresh_token = $content['refresh_token'];
        $user->strava_expires_at = $content['expires_at'];
        $user->save();

        return $content['access_token'];
    }

    /**
     * vyextrahuje id aktivity z odkazu na strave
     */
    public function getActivityIdFromStravaShareLink($shareLink)
    {
        $lastChar = substr($shareLink, -1);
        if ($lastChar == '/') {
            $shareLink = substr($shareLink, 0, -1);
        }

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create();
        $stack->push($history);

        $client = new Client([
            'handler' => $stack,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            ]
        ]);

        $client->get($shareLink);

        $finalUrl = "";
        foreach ($container as $transaction) {
            $finalUrl = (string)$transaction['request']->getUri();
        }

        if (preg_match('/\/activities\/(\d+)/', $finalUrl, $matches)) {
            $activityId = $matches[1];
            return $activityId;
        } else {
            return false;
        }
    }

    public function getSubdomain($url)
    {
        $parseUrl = parse_url($url);
        $explodeHost = explode('.', $parseUrl['host']);
        return $explodeHost[0];
    }

    public function getActivityId($string)
    {
        $lastChar = substr($string, -1);
        if ($lastChar == '/') {
            $string = substr($string, 0, -1);
        }
        return substr($string, strrpos($string, '/') + 1);
    }
}
