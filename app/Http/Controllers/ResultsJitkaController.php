<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;
use App\Services\JitkaStatsService;
use App\Services\OgImageGenerator;

class ResultsJitkaController extends Controller
{
    protected $jitkaStatsService;
    protected $ogImageGenerator;

    public function __construct(JitkaStatsService $jitkaStatsService, OgImageGenerator $ogImageGenerator)
    {
        $this->jitkaStatsService = $jitkaStatsService;
        $this->ogImageGenerator = $ogImageGenerator;
    }

    public function index(Result $result)
    {
        // Získání všech výsledků pro registration_id 131 (Jitka)
        $results = $this->jitkaStatsService->getResults();

        $stats = $this->jitkaStatsService->getOverallStats();

        return view('results-jitka.index', [
            'results' => $results,
            'totalRaces' => $stats['total_count'],
            'totalKm' => $stats['total_km'],
            'totalTime' => $stats['total_time'],
            'avgPace' => $stats['avg_pace']
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
        $result = Result::where('registration_id', 131)->findOrFail($id);
        
        $stats = $this->jitkaStatsService->getCumulativeStats($id);

        try {
            $imageData = $this->ogImageGenerator->generateJitkaImage($result, $stats);
            
            return response($imageData, 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=86400');
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }
}
