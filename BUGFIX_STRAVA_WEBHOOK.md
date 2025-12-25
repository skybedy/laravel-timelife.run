# Oprava Strava Webhook - GPS Data Missing

**Datum:** 2025-12-20, 2025-12-22
**Problém:** Webhook ze Strava nefungoval automaticky

## Analýza problému

### Symptomy
```
[2025-12-19 23:39:05] production.INFO: Webhook event received!
{"query":[],"body":{"aspect_type":"create","event_time":1766187545,"object_id":16788604005,"object_type":"activity","owner_id":129179906,"subscription_id":317506,"updates":[]}}

[2025-12-19 23:39:06] production.ERROR: Undefined array key "latlng"
{"exception":"[object] (ErrorException(code: 0): Undefined array key \"latlng\" at /var/www/laravel-timelife.run/app/Services/ResultService.php:452)
```

### Hlavní příčina
V `StravaController::webhookPostStrava()` byl **duplicitní kód** pro stahování dat ze Strava API, který nebyl synchronizovaný s metodou `ResultService::getStreamFromStrava()`.

- **Nahrávání přes URL** (funkční): Používalo `ResultService::getStreamFromStrava()` ✓
- **Webhook** (nefunkční): Mělo vlastní duplicitní kód pro API volání ✗

## Řešení

### 1. Upravený soubor: `app/Http/Controllers/StravaController.php`

**Před:**
```php
public function webhookPostStrava(Request $request, ResultService $resultService, Registration $registration, TrackPoint $trackPoint, Event $event)
{
    // ... kontrola aspect_type ...

    $stravaId = $request->input('owner_id');
    $user = User::select('id', 'strava_access_token', 'strava_refresh_token', 'strava_expires_at')->where('strava_id', $stravaId)->first();

    // DUPLICITNÍ KÓD: Manuální volání API pro streams a activity
    if ($user->strava_expires_at > time()) {
        $url = config('strava.stream.url').$request->input('object_id').config('strava.stream.params');
        $token = $user->strava_access_token;
        $response = Http::withToken($token)->get($url)->json();

        if ($response) {
            $url = config('strava.activity.url').$request->input('object_id').config('strava.activity.params');
            $response += Http::withToken($token)->get($url)->json();
            $this->dataProcessing($resultService, $registration, $trackPoint, $event, $response, $user->id);
        }
    } else {
        // ... stejný duplicitní kód pro refresh token ...
    }
}
```

**Po (řádky 31-77):**
```php
public function webhookPostStrava(Request $request, ResultService $resultService, Registration $registration, TrackPoint $trackPoint, Event $event)
{
    //pokud to neni 'create'tak to nechcem
    if ($request->input('aspect_type') != 'create')
    {
        exit();
    }

    // zaloguje se prijem dat ze Stravy a budeme logovat jen 'create'
    Log::info('Webhook event received!', ['query' => $request->query(),'body' => $request->all()]);

    try {
        // ✓ Použijeme stejnou metodu jako při nahrávání přes URL
        $activityData = $resultService->getStreamFromStrava($request);

        if (!$activityData) {
            Log::error('Failed to fetch activity data from Strava', [
                'activity_id' => $request->input('object_id'),
                'owner_id' => $request->input('owner_id'),
            ]);
            return response('Failed to fetch activity data', 500);
        }

        // ✓ Kontrola, zda aktivita má GPS data (indoor aktivity nemají)
        if (!isset($activityData['latlng']['data']) || empty($activityData['latlng']['data'])) {
            Log::info('Activity has no GPS data (indoor activity?)', [
                'activity_id' => $request->input('object_id'),
                'type' => $activityData['type'] ?? 'unknown',
            ]);
            return response('Activity has no GPS data, skipping', 200);
        }

        $userId = $activityData['user_id'];

        $this->dataProcessing($resultService, $registration, $trackPoint, $event, $activityData, $userId);

    } catch (\Exception $e) {
        Log::error('Webhook processing failed', [
            'activity_id' => $request->input('object_id'),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response('Webhook processing failed', 500);
    }

    return response('OK', 200);
}
```

### 2. Vyčištěno logování v `app/Services/ResultService.php`

Odstraněny dočasné debug logy (řádky 439-460), protože problém byl v controlleru, ne v service.

### 3. Oprava volání `registrationExists()` - DRUHÁ CHYBA (2025-12-20 11:40)

**Poznámka:** Tato oprava byla provedena v `ResultService.php`, ale webhook používá `WebhookController.php`, který má vlastní logiku pro zpracování registrací.

**Nový problém po první opravě:**
```
[2025-12-20 11:40:05] production.INFO: Webhook event received!
[2025-12-20 11:40:07] production.ERROR: Too few arguments to function App\Models\Registration::registrationExists(), 2 passed in /var/www/laravel-timelife.run/app/Services/ResultService.php on line 480 and exactly 4 expected
```

**Příčina:**
Metoda `Registration::registrationExists()` očekává 4 parametry (řádek 17 v `app/Models/Registration.php`):
```php
public function registrationExists($userId, $eventId, $platformId, $serieId)
```

Ale na **3 místech** v `ResultService.php` se volala jen se 2 parametry a navíc **v obráceném pořadí**:
- ❌ Řádek 279-280: `registrationExists($event['id'], $user)`
- ❌ Řádek 480, 483: `registrationExists($event['id'], $userId)` - **webhook chyba**
- ❌ Řádek 711-712: `registrationExists($event['id'], $request->user()->id)`

**Řešení - upraveno `app/Services/ResultService.php`:**

**Řádek 279-280 - PŘED:**
```php
if (isset($registration->registrationExists($event['id'], $user)->id)) {
    $registrationId = $registration->registrationExists($event['id'], $user)->id;
```

**Řádek 279-280 - PO:**
```php
if (isset($registration->registrationExists($user, $event['id'], NULL, NULL)->id)) {
    $registrationId = $registration->registrationExists($user, $event['id'], NULL, NULL)->id;
```

**Řádek 480, 483 - PŘED:**
```php
if (isset($registration->registrationExists($event['id'], $userId)->id))
{
    $registrationId = $registration->registrationExists($event['id'], $userId)->id;
```

**Řádek 480, 483 - PO:**
```php
if (isset($registration->registrationExists($userId, $event['id'], NULL, NULL)->id))
{
    $registrationId = $registration->registrationExists($userId, $event['id'], NULL, NULL)->id;
```

**Řádek 711-712 - PŘED:**
```php
if (isset($registration->registrationExists($event['id'], $request->user()->id)->id)) {
    $registrationId = $registration->registrationExists($event['id'], $request->user()->id)->id;
```

**Řádek 711-712 - PO:**
```php
if (isset($registration->registrationExists($request->user()->id, $event['id'], NULL, NULL)->id)) {
    $registrationId = $registration->registrationExists($request->user()->id, $event['id'], NULL, NULL)->id;
```

**Správné pořadí parametrů:**
1. `$userId` - ID uživatele (PRVNÍ!)
2. `$eventId` - ID závodu (DRUHÝ!)
3. `$platformId` - ID platformy (NULL pokud se nepoužívá)
4. `$serieId` - ID série (NULL pokud se nepoužívá)

**Důležité:**
- Parametry byly v **obráceném pořadí** (eventId, userId místo userId, eventId)
- Chyběly poslední 2 parametry (platformId, serieId)
- Tato chyba byla na 3 různých místech v kódu
- Správně je to v `EventController.php` (řádky 88, 90, 256, 258) jako vzor

## Jak to funguje

### Strava API Endpoints
Při zpracování aktivity se volají 2 endpointy:

1. **Streams API**: `/api/v3/activities/{id}/streams?keys=time,latlng,altitude,cadence&key_by_type=true`
   - Vrací GPS data, čas, nadmořskou výšku, kadenci
   - `key_by_type=true` vrací data jako asociativní pole:
   ```json
   {
     "latlng": {
       "data": [[lat, lng], [lat, lng], ...],
       "series_type": "distance",
       "original_size": 512
     },
     "time": {
       "data": [0, 5, 10, ...],
       ...
     }
   }
   ```

2. **Activity Detail API**: `/api/v3/activities/{id}?include_all_efforts=true`
   - Vrací metadata: `start_date_local`, `type`, `name`, atd.

### Metoda `ResultService::getStreamFromStrava()`
(Řádky 42-116 v `app/Services/ResultService.php`)

- Stahuje oba endpointy a spojuje je pomocí `$response += $activityDetail`
- Přidává `user_id` do výsledku
- Automaticky obnovuje token, pokud vypršel
- Používá se pro:
  - ✓ Nahrávání přes URL (`EventController`)
  - ✓ Webhook ze Strava (`StravaController`) - **NOVĚ**

## Testování

### 1. Sledování logů
```bash
tail -f /var/www/laravel-timelife.run/storage/logs/laravel.log
```

### 2. Očekávané logy při úspěchu
```
[timestamp] production.INFO: Webhook event received! {"query":[],"body":{...}}
[timestamp] production.INFO: Result saved successfully
```

### 3. Očekávané logy při indoor aktivitě (bez GPS)
```
[timestamp] production.INFO: Webhook event received! {"query":[],"body":{...}}
[timestamp] production.INFO: Activity has no GPS data (indoor activity?) {"activity_id":...}
```

### 4. Test webhook manuálně
Pokud potřebujete otestovat webhook bez čekání na novou Strava aktivitu:

```bash
# Příklad POST requestu (upravte hodnoty)
curl -X POST https://vase-domena.cz/webhook/strava \
  -H "Content-Type: application/json" \
  -d '{
    "aspect_type": "create",
    "event_time": 1766187545,
    "object_id": 16788604005,
    "object_type": "activity",
    "owner_id": 129179906,
    "subscription_id": 317506,
    "updates": []
  }'
```

## Vyčištění cache po změnách
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Konfigurace Strava API

Viz `config/strava.php`:
```php
'stream' => [
    'url' => 'https://www.strava.com/api/v3/activities/',
    'params' => '/streams?keys=time,latlng,altitude,cadence&key_by_type=true',
],

'activity' => [
    'url' => 'https://www.strava.com/api/v3/activities/',
    'params' => '?include_all_efforts=true',
],
```

## Soubory změněné

1. ✓ `app/Http/Controllers/StravaController.php` (řádky 31-77) - Hlavní oprava
2. ✓ `app/Services/ResultService.php` (řádky 439-460) - Vyčištěno debug logování
3. ✓ `app/Services/ResultService.php` (řádky 279-280, 480+483, 711-712) - Oprava volání registrationExists()
4. ✓ `app/Http/Controllers/WebhookController.php` (řádky 169-175) - Oprava chybějícího pace_mile (2025-12-22)

## Důležité poznámky

- **Webhook funguje pouze pro outdoor aktivity** s GPS daty
- Indoor aktivity (běžecký pás, kolo na trenažéru) nemají `latlng` data a budou přeskočeny (200 OK response)
- Token refresh je automatický v `getStreamFromStrava()`
- Webhook a URL upload nyní používají **identický kód** pro získání dat

## Možné budoucí problémy

### Aktivita nemá GPS data
- Indoor aktivity
- Manuálně vytvořené aktivity
- **Řešení**: Už implementováno - kontrola na řádku 55 v StravaController

### Token expiroval
- **Řešení**: Už implementováno v `getStreamFromStrava()` - automatický refresh

### Strava API rate limit
- 100 requests per 15 minutes, 1000 per day
- **Řešení**: Webhook přichází max 1x za aktivitu, nemělo by být problém

### Aktivita je private
- Strava API vrátí prázdnou response nebo chybu
- **Řešení**: Try-catch blok zachytí chybu a zaloguje (řádek 67-74)

### 4. Oprava chybějícího pole `pace_mile` - TŘETÍ CHYBA (2025-12-22, 2025-12-23)

**Nový problém (2025-12-22):**
```
[2025-12-22 15:58:24] production.ALERT: Došlo k problému s nahráním dat
{"error":"SQLSTATE[HY000]: General error: 1364 Field 'pace_mile' doesn't have a default value (Connection: mariadb, SQL: insert into `results` (`registration_id`, `finish_time_date`, `finish_time`, `pace_km`, `finish_time_sec`, `updated_at`, `created_at`) values (131, 2025-12-22, 2:08:11, 6:05, 7691, 2025-12-22 15:58:23, 2025-12-22 15:58:23))"}
```

**Problém se vrátil (2025-12-23):**
```
[2025-12-23 09:14:33] production.ALERT: Došlo k problému s nahráním dat
{"error":"SQLSTATE[HY000]: General error: 1364 Field 'pace_mile' doesn't have a default value (Connection: mariadb, SQL: insert into `results` (`registration_id`, `finish_time_date`, `finish_time`, `pace_km`, `finish_time_sec`, `updated_at`, `created_at`) values (131, 2025-12-23, 2:16:29, 6:28, 8189, 2025-12-23 09:14:33, 2025-12-23 09:14:33))"}
```

**Příčina:**
Webhook route `/webhook` volá `StravaController::webhookPostStrava()`, NIKOLI `WebhookController::postStrava()`.
- `StravaController::webhookPostStrava()` → volá → `StravaController::dataProcessing()`
- V metodě `dataProcessing()` chyběl výpočet `pace_mile`
- Oprava z 2025-12-22 byla provedena v `WebhookController.php`, ale webhook používá `StravaController.php`

**Řešení - upraveno `app/Http/Controllers/StravaController.php`:**

**1. Metoda `dataProcessing()` (řádky 106-112):**

```php
// Save result to database
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
```

**Vysvětlení:**
- Kód převzat z metody `ResultService::resultSave()` (řádky 1053-1062)
- Převádí tempo na kilometr (např. "6:05") na tempo na míli
- Výpočet: 1 míle = 1.60934 km
- Formát výstupu: "m:ss" (např. "6:05" → "9:47")

## Reference

- Strava Webhooks API: https://developers.strava.com/docs/webhooks/
- Strava Activity Streams: https://developers.strava.com/docs/reference/#api-Streams-getActivityStreams
- Strava Activity Detail: https://developers.strava.com/docs/reference/#api-Activities-getActivityById
