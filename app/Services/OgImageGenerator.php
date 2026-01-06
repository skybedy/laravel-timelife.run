<?php

namespace App\Services;

use App\Models\Result;

class OgImageGenerator
{
    private \GdImage $image;
    private int $whiteColor;
    private string $fontPath;

    public function __construct()
    {
        // Potentially load config here
        $this->fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        if (!file_exists($this->fontPath)) {
             throw new \Exception('Font not found: ' . $this->fontPath);
        }
    }

    public function generateJitkaImage(Result $result, array $stats): string
    {
        $this->initializeImage();

        $this->drawGreenSection($result);
        $this->drawRedSection($stats['race_number']);
        $this->drawBlueSection($stats);
        
        // Header Texts
        imagettftext($this->image, 24, 0, 106, 430, $this->whiteColor, $this->fontPath, "1/2maraton #{$stats['race_number']}");
        imagettftext($this->image, 24, 0, 500, 430, $this->whiteColor, $this->fontPath, "Dokončeno");
        imagettftext($this->image, 24, 0, 880, 430, $this->whiteColor, $this->fontPath, "CELKEM");

        return $this->outputImage();
    }

    private function initializeImage(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $templatePath = storage_path('app/assets/og/sablona_jitka.png');
        if (!file_exists($templatePath)) {
            throw new \Exception('Template not found: ' . $templatePath);
        }

        $this->image = imagecreatefrompng($templatePath);
        $this->whiteColor = imagecolorallocate($this->image, 255, 255, 255);
    }

    private function drawGreenSection(Result $result): void
    {
        $dateFormatted = date('d.m.y', strtotime($result->finish_time_date));
        $finishTime = substr($result->finish_time, 1);

        // Date - dynamic margin
        $dateMargin = $this->calculateMarginForLength(strlen($dateFormatted), [6 => 59, 7 => 51, 8 => 45], 45);
        imagettftext($this->image, 15, 0, $dateMargin, 528, $this->whiteColor, $this->fontPath, $dateFormatted);

        imagettftext($this->image, 15, 0, 196, 528, $this->whiteColor, $this->fontPath, $finishTime);
        imagettftext($this->image, 15, 0, 335, 528, $this->whiteColor, $this->fontPath, "{$result->pace_km}/km");
    }

    private function drawRedSection(int $raceNumber): void
    {
        $leftMargin = $raceNumber < 10 ? 574 : 552;
        imagettftext($this->image, 50, 0, $leftMargin, 522, $this->whiteColor, $this->fontPath, (string)$raceNumber);
        
        imageline($this->image, 530, 528, 660, 528, $this->whiteColor);
        imagettftext($this->image, 50, 0, 530, 586, $this->whiteColor, $this->fontPath, "100");
    }

    private function drawBlueSection(array $stats): void
    {
        // Total Km
        $totalKm = number_format($stats['total_km_raw'], 2, ',', '');
        $kmMargin = $this->calculateMarginForLength(strlen($totalKm), [2 => 805, 3 => 796, 4 => 788, 5 => 780, 6 => 777, 7 => 768]);
        if ($kmMargin) {
            imagettftext($this->image, 15, 0, $kmMargin, 528, $this->whiteColor, $this->fontPath, $totalKm);
        }

        // Total Time
        $totalTime = $stats['total_time'];
        $timeMargin = $this->calculateMarginForLength(strlen($totalTime), [7 => 916, 8 => 910, 9 => 903]);
        if ($timeMargin) {
            imagettftext($this->image, 15, 0, $timeMargin, 528, $this->whiteColor, $this->fontPath, $totalTime);
        }

        imagettftext($this->image, 15, 0, 1056, 528, $this->whiteColor, $this->fontPath, "{$stats['avg_pace']}/km");
    }

    private function calculateMarginForLength(int $length, array $map, int $default = 0): int
    {
        return $map[$length] ?? $default;
    }

    private function outputImage(): string
    {
        ob_start();
        imagepng($this->image);
        $imageData = ob_get_clean();
        imagedestroy($this->image);

        return $imageData;
    }
}
