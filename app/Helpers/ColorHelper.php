<?php

namespace App\Helpers;

class ColorHelper
{
    /**
     * Get color based on value range
     */
    public static function getColorByValue($value, $ranges)
    {
        foreach ($ranges as $range) {
            if ($value >= $range['min'] && $value <= $range['max']) {
                return $range['color'];
            }
        }
        return '#808080'; // Default gray
    }

    /**
     * Get fertilizer color indicator
     */
    public static function getFertilizerColor($amount)
    {
        if ($amount == 0) return 'black';
        if ($amount > 500) return 'red';
        if ($amount > 200) return 'yellow';
        return 'green';
    }

    /**
     * Convert hex to RGB
     */
    public static function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Generate gradient colors
     */
    public static function generateGradient($startColor, $endColor, $steps)
    {
        $startRgb = self::hexToRgb($startColor);
        $endRgb = self::hexToRgb($endColor);

        $gradient = [];

        for ($i = 0; $i < $steps; $i++) {
            $ratio = $i / ($steps - 1);

            $r = round($startRgb['r'] + ($endRgb['r'] - $startRgb['r']) * $ratio);
            $g = round($startRgb['g'] + ($endRgb['g'] - $startRgb['g']) * $ratio);
            $b = round($startRgb['b'] + ($endRgb['b'] - $startRgb['b']) * $ratio);

            $gradient[] = sprintf('#%02x%02x%02x', $r, $g, $b);
        }

        return $gradient;
    }

    /**
     * Get random color
     */
    public static function getRandomColor()
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
