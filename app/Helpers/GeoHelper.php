<?php

namespace App\Helpers;

class GeoHelper
{
    /**
     * Calculate distance between two coordinates (in kilometers)
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Get bounds for a set of coordinates
     */
    public static function getBounds($coordinates)
    {
        if (empty($coordinates)) {
            return null;
        }

        $lats = array_column($coordinates, 'lat');
        $lngs = array_column($coordinates, 'lng');

        return [
            'north' => max($lats),
            'south' => min($lats),
            'east' => max($lngs),
            'west' => min($lngs),
        ];
    }

    /**
     * Validate coordinates
     */
    public static function validateCoordinates($lat, $lng)
    {
        return is_numeric($lat) && is_numeric($lng) &&
               $lat >= -90 && $lat <= 90 &&
               $lng >= -180 && $lng <= 180;
    }

    /**
     * Convert DMS to Decimal
     */
    public static function dmsToDecimal($degrees, $minutes, $seconds, $direction)
    {
        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ($direction === 'S' || $direction === 'W') {
            $decimal *= -1;
        }

        return $decimal;
    }

    /**
     * Generate random coordinates within bounds (for testing)
     */
    public static function generateRandomCoordinate($centerLat, $centerLng, $radiusKm)
    {
        $radiusInDegrees = $radiusKm / 111.32;

        $u = mt_rand() / mt_getrandmax();
        $v = mt_rand() / mt_getrandmax();

        $w = $radiusInDegrees * sqrt($u);
        $t = 2 * pi() * $v;

        $x = $w * cos($t);
        $y = $w * sin($t);

        $newLat = $centerLat + $y;
        $newLng = $centerLng + ($x / cos(deg2rad($centerLat)));

        return [
            'latitude' => round($newLat, 6),
            'longitude' => round($newLng, 6)
        ];
    }
}
