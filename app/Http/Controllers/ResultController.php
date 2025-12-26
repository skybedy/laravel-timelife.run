<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Result;
use App\Models\TrackPoint;
use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request, Result $result)
    {
        dd($result->resultsOverall($request->eventId));

        return view('result.index');
    }

    public function manage(Request $request,Result $result)
    {
        return view('result.manage', [
            'results' => $result->getAllUserResults($request->user()->id)
        ]);
        
    }


    public function delete(Request $request)
    {
        TrackPoint::where('result_id', $request->resultId)->delete();

        Result::find($request->resultId)->delete();

        return back();
    }








    public function resultUser(Request $request, Result $result)
    {
        return response()->json($result->resultsIndividual($request->registrationId));
    }





    public function resultMap(Request $request, TrackPoint $trackPoint)
    {
        $result = Result::findOrFail($request->resultId);
        $trackPoints = $trackPoint::select('latitude', 'longitude')
            ->where('result_id', $request->resultId)
            ->get();

        return view('result.map', [
            'result' => $result,
            'trackPoints' => $trackPoints
        ]);
    }

    public function resultMapData(Request $request)
    {
        $trackPoints = TrackPoint::select('latitude', 'longitude')
            ->where('result_id', $request->resultId)
            ->get();

        // Vrátit track pointy jako GPX XML string (bez XML hlavičky)
        $gpxString = '';
        foreach ($trackPoints as $point) {
            $gpxString .= '<trkpt lat="' . $point->latitude . '" lon="' . $point->longitude . '"></trkpt>';
        }

        return response()->json($gpxString);
    }

}