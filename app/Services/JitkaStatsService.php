<?php

namespace App\Services;

use App\Models\Result;
use Illuminate\Support\Facades\DB;

class JitkaStatsService
{
    private int $registrationId;

    public function __construct() {
        $this->registrationId = 131; // TODO: Move to config
    }

    public function getOverallStats(): array
    {
        return cache()->remember('jitka.overall_stats', 86400, function () {
            $stats = DB::selectOne(
                "SELECT 
                    COUNT(*) as total_count,
                    SUM(finish_time_sec) as total_seconds,
                    AVG(TIME_TO_SEC(STR_TO_DATE(pace_km, '%i:%s'))) as avg_pace_seconds
                FROM results 
                WHERE registration_id = ?", 
                [$this->registrationId]
            );

            return $this->formatStats($stats);
        });
    }

    public function getResults()
    {
        return Result::selectRaw('id,SUBSTRING(finish_time,2) AS finish_time,pace_km,DATE_FORMAT(results.finish_time_date,"%e.%c.") AS date')
            ->where('registration_id', $this->registrationId)
            ->orderBy('finish_time_date', 'DESC')
            ->get();
    }

    public function getCumulativeStats($raceId)
    {
        $allResults = Result::where('registration_id', $this->registrationId)
            ->orderBy('finish_time_date')
            ->orderBy('finish_time')
            ->get();

        $raceNumber = $allResults->search(function($item) use ($raceId) {
            return $item->id == $raceId;
        }) + 1;

        $completedResults = $allResults->take($raceNumber);
        $totalSeconds = $completedResults->sum('finish_time_sec');
        
        // Calculate average pace manually from results
        $avgPaceSeconds = $completedResults->avg(function($result) {
            list($min, $sec) = explode(':', $result->pace_km);
            return ($min * 60) + $sec;
        });

        // Construct a stats object similar to DB result for consistency
        $stats = (object) [
            'total_count' => $raceNumber,
            'total_seconds' => $totalSeconds,
            'avg_pace_seconds' => $avgPaceSeconds,
            'total_races_count' => $allResults->count() // Extra info if needed
        ];

        return $this->formatStats($stats) + [
            'race_number' => $raceNumber,
            'total_races_count' => $allResults->count()
        ];
    }

    private function formatStats(object $stats): array
    {
        $totalTime = null;
        if (isset($stats->total_seconds) && $stats->total_seconds) {
            $hours = floor($stats->total_seconds / 3600);
            $minutes = floor(($stats->total_seconds % 3600) / 60);
            $seconds = $stats->total_seconds % 60;
            $totalTime = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        $avgPace = null;
        if (isset($stats->avg_pace_seconds) && $stats->avg_pace_seconds) {
            $minutes = floor($stats->avg_pace_seconds / 60);
            $seconds = round($stats->avg_pace_seconds % 60);
            $avgPace = sprintf('%d:%02d', $minutes, $seconds);
        }

        // Format totalKm (e.g. "2 100,50" for view, raw for calculations)
        $totalKmRaw = ($stats->total_count ?? 0) * 21.0975;
        $totalKm = number_format($totalKmRaw, 2, ',', ' ');
        
        return [
            'total_count' => $stats->total_count ?? 0,
            'total_km' => $totalKm,
            'total_km_raw' => $totalKmRaw,
            'total_time' => $totalTime ?? '0:00:00',
            'avg_pace' => $avgPace ?? '0:00',
            'avg_pace_seconds' => $stats->avg_pace_seconds ?? 0
        ];
    }
}
