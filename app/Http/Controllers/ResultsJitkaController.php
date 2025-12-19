<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultsJitkaController extends Controller
{
    public function index(Result $result)
    {
        // Získání všech výsledků pro registration_id 131 (Jitka)
        $results = $result->resultsIndividual(131);

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

        return view('results-jitka.index', [
            'results' => $results,
            'totalRaces' => $stats->total_count ?? 0,
            'totalKm' => $totalKm,
            'totalTime' => $totalTime ?? '0:00:00',
            'avgPace' => $avgPace ?? '0:00'
        ]);
    }

    public function show($id)
    {
        
        // Získání konkrétního výsledku
        $result = Result::where('registration_id', 131)->findOrFail($id);

        // Získání všech výsledků pro registration_id 131 pro určení pořadí
        $allResults = Result::where('registration_id', 131)
            ->orderBy('finish_time_date')
            ->orderBy('finish_time')
            ->get();

        $raceNumber = $allResults->search(function($item) use ($id) {
            return $item->id == $id;
        }) + 1;

        $totalRaces = $allResults->count();

        return view('results-jitka.show', [
            'result' => $result,
            'raceNumber' => $raceNumber,
            'totalRaces' => $totalRaces
        ]);
    }

   public function ogImage($id)
{
    // Vyčistit všechny output buffery
    while (ob_get_level()) {
        ob_end_clean();
    }

    // --- ZÍSKÁNÍ DAT ---
    $result = Result::where('registration_id', 131)->findOrFail($id);

    $allResults = Result::where('registration_id', 131)
        ->orderBy('finish_time_date')
        ->orderBy('finish_time')
        ->get();

    $raceNumber = $allResults->search(function($item) use ($id) {
        return $item->id == $id;
    }) + 1;

    $totalRaces = $allResults->count();

    // --- VÝPOČET STATISTIK ---

    // Celkový počet km (počet půlmaratonů × 21.0975 km)
    $totalKm = number_format($raceNumber * 21.0975, 2, ',', '');

    // Součet časů všech půlmaratonů (do aktuálního závodu včetně)
    $completedResults = $allResults->take($raceNumber);
    $totalSeconds = $completedResults->sum('finish_time_sec');

    // Převod celkového času ze sekund na formát HH:MM:SS
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;
    $totalTime = sprintf('%d:%02d', $hours, $minutes);
    $totalTimeSec = sprintf('%02d', $seconds);

    // Průměrné tempo všech půlmaratonů
    $avgPaceSeconds = $completedResults->avg(function($result) {
        // Převod MM:SS na sekundy
        list($min, $sec) = explode(':', $result->pace_km);
        return ($min * 60) + $sec;
    });

    $avgPaceMinutes = floor($avgPaceSeconds / 60);
    $avgPaceSeconds = round($avgPaceSeconds % 60);
    $avgPace = sprintf('%d:%02d', $avgPaceMinutes, $avgPaceSeconds);

    // --- PŘÍPRAVA PLÁTNA A BAREV ---

    $width = 1200;
    $height = 630;
    
    // 1. Získání cesty k souboru šablony
    $templatePath = storage_path('app/assets/og/sablona_jitka.png');

    // 2. Kontrola, zda soubor existuje
    if (!file_exists($templatePath)) {
        // Zde by měla být robustní obsluha chyby, např. vrácení defaultního statického obrázku
        return response('Template not found', 500); 
    }

    // 3. Vytvoření obrázku NAČTENÍM ŠABLONY (nahrazuje imagecreatetruecolor a imagefilledrectangle)
    $image = imagecreatefrompng($templatePath);

    // Definice barev pro text (předpokládáme bílý text, který vynikne na šabloně)
    $whiteColor = imagecolorallocate($image, 255, 255, 255);
    $lightGrayColor = imagecolorallocate($image, 209, 213, 219); 
    $highlightColor = imagecolorallocate($image, 255, 255, 0); // Např. žlutá pro čas

    // Cesta k systémovému fontu (Nutné zkontrolovat!)
    $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

    // Příprava textu
    $dateFormatted = date('d.m', strtotime($result->finish_time_date));
    $yearFormatted = date('y', strtotime($result->finish_time_date));
    // $dateFormatted = date('d.m.Y', strtotime($result->finish_time_date));
    $finishTime = substr($result->finish_time, 1,4);
    $finishTimeSec = substr($result->finish_time, 6);

    // --- VYKRESLENÍ TEXTU (Levá strana je zachována, ale souřadnice je nutné přizpůsobit šabloně) ---

    // Nadpis - číslo závodu
    // POZOR: Souřadnice (60, 120) platily pro šedé pozadí. Pokud šablona zabírá prostor, upravte!
   // imagettftext($image, 60, 0, 60, 120, $whiteColor, $fontPath, "Pulmaraton #{$raceNumber}/{$totalRaces}");

    // Jméno
   // imagettftext($image, 40, 0, 60, 200, $lightGrayColor, $fontPath, "Jitka Dvorackova");

    //Hlavni nadpisy
    imagettftext($image, 24, 0, 106, 430, $whiteColor, $fontPath, "1/2maraton #{$raceNumber}");
    imagettftext($image, 24, 0, 500, 430, $whiteColor, $fontPath, "Dokončeno");
    imagettftext($image, 24, 0, 880, 430, $whiteColor, $fontPath, "CELKEM");


   
    // zeleny obdelnik
    // Datum
    imagettftext($image, 18, 0, 56, 516, $whiteColor, $fontPath, "{$dateFormatted}");
    imagesetthickness($image, 2);
    imageline($image, 54, 522, 136, 522, $whiteColor);
    imagettftext($image, 18, 0, 80, 546, $whiteColor, $fontPath, "{$yearFormatted}");

    // Čas
    imagettftext($image, 18, 0, 210, 516, $whiteColor, $fontPath, "{$finishTime}");
    imageline($image, 198, 522, 280, 522, $whiteColor);
     imagettftext($image, 18, 0, 222, 546, $whiteColor, $fontPath, "{$finishTimeSec}");
   

    // Tempo
    imagettftext($image, 18, 0, 352, 516, $whiteColor, $fontPath, "{$result->pace_km}");
    imageline($image, 338, 522, 422, 522, $whiteColor);
    imagettftext($image, 18, 0, 362, 546, $whiteColor, $fontPath, "km");

    //cerveny obdelnik
    $raceNumber < 10 ? $leftMargin = 574 : $leftMargin = 552; 
    imagettftext($image, 50, 0, $leftMargin, 522, $whiteColor, $fontPath, "{$raceNumber}");
    imageline($image, 530, 528, 660, 528, $whiteColor);
    imagettftext($image, 50, 0, 530, 586, $whiteColor, $fontPath, "100");


    //Modry obdelnik
     $totalKmLength = strlen($totalKm);

    // Nastavení levého okraje podle počtu znaků
    switch ($totalKmLength) {
        case 2:
            $marginLeft = 801;
            break;
        case 3:
            $marginLeft = 792;
            break;
        case 4:
            $marginLeft = 784;
            break;
        case 5:
            $marginLeft = 780;
            break;
        case 6:
            $marginLeft = 773;
            break;
        case 7:
            $marginLeft = 763;
            break;
        default:
            $marginLeft = 0;
            break;
    }

    imagettftext($image, 18, 0, $marginLeft, 528, $whiteColor, $fontPath, "{$totalKm}");
    // Nastavení levého okraje pro celkový čas podle počtu znaků
    $totalTimeLength = strlen($totalTime);

    switch ($totalTimeLength) {
        case 4:
            $marginLeft = 930;
            break;
        case 5:
            $marginLeft = 922;
            break;
        case 6:
            $marginLeft = 912;
            break;
        default:
            $marginLeft = 0;
            break;
    }

    //čas
    imagettftext($image, 18, 0, $marginLeft, 516, $whiteColor, $fontPath, "{$totalTime}");
    imageline($image, 914, 522, 1006, 522, $whiteColor);
    imagettftext($image, 18, 0, 945, 546, $whiteColor, $fontPath, "{$totalTimeSec}");

    //tempo
    imagettftext($image, 18, 0, 1072, 516, $whiteColor, $fontPath, "{$avgPace}");
    imageline($image, 1058, 522, 1146, 522, $whiteColor);
    imagettftext($image, 18, 0, 1082, 546, $whiteColor, $fontPath, "km");
    
 

    // Logo/URL dole
    //imagettftext($image, 25, 0, 60, 580, $lightGrayColor, $fontPath, "TimeLife.run");

    // --- ZÁVĚR A ODESLÁNÍ OBRÁZKU ---

    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return response($imageData, 200)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'public, max-age=86400');
}
}
