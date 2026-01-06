<?php

namespace App\Services;

use Carbon\Carbon;
use App\Exceptions\TimeMissingException;
use App\Exceptions\TimeIsOutOfRangeException;
use App\Exceptions\SmallDistanceException;

class GpxService
{
    /**
     * Získá data o dokončení aktivity z GPX souboru
     */
    public function parseGpx($fileContent, $event, $userId)
    {
        $eventDistance = $event->distance;
        $dateEventStartTimestamp = Carbon::createFromFormat('Y-m-d', $event->date_start)->timestamp;
        $dateEventEndTimestamp = Carbon::createFromFormat('Y-m-d', $event->date_end)->timestamp;

        $xmlObject = simplexml_load_string(trim($fileContent));
        
        $namespaces = $xmlObject->getNamespaces(true);
        $activityDateTime = $xmlObject->metadata->time;

        if ($activityDateTime == null) {
            throw new TimeMissingException();
        }

        $activityDate = date("Y-m-d", strtotime($activityDateTime));
        
        $distance = 0;
        $trackPointArray = [];
        $lastPointLat = $lastPointLon = $currentPointLat = $currentPointLon = null;
        $i = 1;
        $startDayTimestamp = null;
        $finishTime = null;

        foreach ($xmlObject->trk->trkseg->trkpt as $point) {
            if (!isset($point->time)) {
                throw new TimeMissingException();
            }

            $time = $this->iso8601ToTimestamp($point->time);

            if (!$this->isTimeInRange($time, $dateEventStartTimestamp, $dateEventEndTimestamp)) {
                throw new TimeIsOutOfRangeException('Čas je mimo rozsah akce.');
            }

            if ($i == 1) {
                $startDayTimestamp = $time;
            }

            $lastPointLat = $currentPointLat;
            $lastPointLon = $currentPointLon;
            $currentPointLat = floatval($point['lat']);
            $currentPointLon = floatval($point['lon']);

            $cad = null;
            if (isset($namespaces['ns3'])) {
                $cad = (string) $point->extensions->children($namespaces['ns3'])->TrackPointExtension->cad;
            } elseif (isset($namespaces['gpxtpx'])) {
                $cad = (string) $point->extensions->children($namespaces['gpxtpx'])->TrackPointExtension->cad;
            }

            $trackPointArray[] = [
                'latitude' => $currentPointLat,
                'longitude' => $currentPointLon,
                'time' => $time,
                'user_id' => $userId,
                'cadence' => (string) $cad,
            ];

            if ($lastPointLat != null) {
                $pointDistance = $this->haversineGreatCircleDistance($lastPointLat, $lastPointLon, $currentPointLat, $currentPointLon);
                $distance += $pointDistance;

                if ($distance >= $eventDistance) {
                    $finishTime = $this->finishTimeCalculation($eventDistance, $distance, $point->time, $startDayTimestamp);
                    break;
                }
            }
            $i++;
        }

        if ($distance < $eventDistance) {
            throw new SmallDistanceException('Vzdálenost je menší než délka tratě.');
        }

        return [
            'finish_time' => $finishTime['finish_time'],
            'finish_time_sec' => $finishTime['finish_time_sec'],
            'finish_time_date' => $activityDate,
            'pace' => $finishTime['pace'],
            'track_points' => $trackPointArray,
        ];
    }

    private function isTimeInRange($time, $dateEventStartTimestamp, $dateEventEndTimestamp)
    {
        return ($time >= $dateEventStartTimestamp && $time <= $dateEventEndTimestamp);
    }

    public function iso8601ToTimestamp($time)
    {
        return Carbon::parse($time)->timestamp;
    }

    public function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
    
    // Extracted calculation logic
    public function finishTimeCalculation($eventDistance, $rawActivityDistance, $rawDayTimestamp, $startDayTimestamp = null)
    {
        // Handling input format: if rawDayTimestamp is actually ISO string from XML
        $timestamp = is_numeric($rawDayTimestamp) ? $rawDayTimestamp : $this->iso8601ToTimestamp($rawDayTimestamp);

        if ($startDayTimestamp == null) {
            $rawFinishTimeSec = $timestamp;
        } else {
            $rawFinishTimeSec = $timestamp - $startDayTimestamp;
        }

        $finishTime = $this->finishTimeRecountAccordingDistance($eventDistance, $rawActivityDistance, $rawFinishTimeSec);

        return [
            'finish_time' => $finishTime['finish_time'],
            'finish_time_sec' => intval(round($finishTime['finish_time_sec'], 0)),
            'pace' => $this->averageTimePerKm($eventDistance, $finishTime['finish_time_sec'])
        ];

    }

    public function finishTimeRecountAccordingDistance($eventDistance, $activityDistance, $rawFinishTimeSec)
    {
        $secPerMeter = $rawFinishTimeSec / $activityDistance;
        $plusDistance = $activityDistance - $eventDistance;
        $plusSecond = $plusDistance * $secPerMeter; 
        
        $finishTimeSec = intval(round($rawFinishTimeSec - $plusSecond));

        $finishTime = Carbon::createFromTimestamp($finishTimeSec)->format('G:i:s');

        return [
            "finish_time" => $finishTime,
            "finish_time_sec" => $finishTimeSec
        ];
    }

    public function averageTimePerKm($eventDistance, $finishTimeSec)
    {
        $secondPerKm = round(($finishTimeSec * 1000) / $eventDistance);
        $timeObj = Carbon::createFromTime(0, 0, 0)->addSeconds($secondPerKm);
        return substr($timeObj->format('i:s'), 1);
    }
}
