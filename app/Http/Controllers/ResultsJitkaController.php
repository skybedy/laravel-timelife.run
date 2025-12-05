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
}
