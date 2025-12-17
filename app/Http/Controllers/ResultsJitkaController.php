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
    $totalKm = number_format($raceNumber * 21.0975, 2, ',', ' ');

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

 imagettftext($image, 22, 0, 120, 442, $whiteColor, $fontPath, "1/2maraton #{$raceNumber}");

  imagettftext($image, 22, 0, 545, 442, $whiteColor, $fontPath, "Dokončeno");


    // Datum
    imagettftext($image, 18, 0, 64, 522, $whiteColor, $fontPath, "{$dateFormatted}");
    // Bílá linka (podtržení)
    imageline($image, 60, 528, 142, 528, $whiteColor);
    imagettftext($image, 18, 0, 86, 552, $whiteColor, $fontPath, "{$yearFormatted}");

    // Čas
    imagettftext($image, 18, 0, 226, 522, $whiteColor, $fontPath, "{$finishTime}");
    imageline($image, 214, 528, 296, 528, $whiteColor);
     imagettftext($image, 18, 0, 238, 552, $whiteColor, $fontPath, "{$finishTimeSec}");

    // Tempo
        imagettftext($image, 18, 0, 386, 522, $whiteColor, $fontPath, "{$result->pace_km}");
        imageline($image, 570, 528, 700, 528, $whiteColor);
     
    imagettftext($image, 18, 0, 395, 552, $whiteColor, $fontPath, "km");

    $raceNumber < 10 ? $leftMargin = 612 : $leftMargin = 590; 
    
    imagettftext($image, 50, 0, $leftMargin, 522, $whiteColor, $fontPath, "{$raceNumber}");
    
    imageline($image, 378, 528, 452, 528, $whiteColor);
    imagettftext($image, 50, 0, 566, 586, $whiteColor, $fontPath, "100");



    //Celkově

     imagettftext($image, 22, 0, 940, 442, $whiteColor, $fontPath, "CELKEM");

    $totalKmLength = strlen($totalKm);

    // Nastavení levého okraje podle počtu znaků
    switch ($totalKmLength) {
        case 2:
            $marginLeft = 843;
            break;
        case 3:
            $marginLeft = 834;
            break;
        case 4:
            $marginLeft = 826;
            break;
        case 5:
            $marginLeft = 822;
            break;
        case 6:
            $marginLeft = 815;
            break;
        case 7:
            $marginLeft = 804;
            break;
        default:
            $marginLeft = 0;
            break;
    }

    imagettftext($image, 18, 0, $marginLeft, 534, $whiteColor, $fontPath, "{$totalKm}");

    // Nastavení levého okraje pro celkový čas podle počtu znaků
    $totalTimeLength = strlen($totalTime);

    switch ($totalTimeLength) {
        case 4:
            $marginLeft = 984;
            break;
        case 5:
            $marginLeft = 976;
            break;
        case 6:
            $marginLeft = 966;
            break;
        default:
            $marginLeft = 0;
            break;
    }

    imagettftext($image, 18, 0, $marginLeft, 522, $whiteColor, $fontPath, "{$totalTime}");
    imageline($image, 972, 528, 1054, 528, $whiteColor);
    imagettftext($image, 18, 0, 998, 552, $whiteColor, $fontPath, "{$totalTimeSec}");

    //celkovw tempo
     
    imagettftext($image, 18, 0, 1145, 522, $whiteColor, $fontPath, "{$avgPace}");
        imageline($image, 1140, 528, 1208, 528, $whiteColor);
     
    imagettftext($image, 18, 0, 1156, 552, $whiteColor, $fontPath, "km");
    
 

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
