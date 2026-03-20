<?php

declare(strict_types=1);

namespace Geo;

class GeoFormatter
{
    public static function toDMS(float $coord, bool $isLat): string
    {
        $dir = $isLat
            ? ($coord >= 0 ? 'N' : 'S')
            : ($coord >= 0 ? 'E' : 'W');

        $coord = abs($coord);
        $deg = (int)$coord;
        $minFloat = ($coord - $deg) * 60;
        $min = (int)$minFloat;
        $sec = ($minFloat - $min) * 60;

        return sprintf("%d°%02d'%05.2f\"%s", $deg, $min, $sec, $dir);
    }
    public static function toUTM(float $lat, float $lon): string
    {
        // Konstanten für WGS84
        $a = 6378137.0;               // große Halbachse
        $f = 1 / 298.257223563;       // Abplattung
        $k0 = 0.9996;                 // Maßstabsfaktor

        $e2 = 2 * $f - $f * $f;       // 1. numerische Exzentrizität

        // Zone berechnen
        $zone = (int)floor(($lon + 180) / 6) + 1;
        $λ0 = deg2rad(-183 + $zone * 6); // zentraler Meridian der Zone

        $φ = deg2rad($lat);
        $λ = deg2rad($lon);

        $N = $a / sqrt(1 - $e2 * pow(sin($φ), 2));
        $T = pow(tan($φ), 2);
        $C = ($e2 / (1 - $e2)) * pow(cos($φ), 2);
        $A = cos($φ) * ($λ - $λ0);

        $M = $a * (
            (1 - $e2 / 4 - 3 * pow($e2, 2) / 64 - 5 * pow($e2, 3) / 256) * $φ
            - (3 * $e2 / 8 + 3 * pow($e2, 2) / 32 + 45 * pow($e2, 3) / 1024) * sin(2 * $φ)
            + (15 * pow($e2, 2) / 256 + 45 * pow($e2, 3) / 1024) * sin(4 * $φ)
            - (35 * pow($e2, 3) / 3072) * sin(6 * $φ)
        );

        $easting = $k0 * $N * (
            $A + (1 - $T + $C) * pow($A, 3) / 6
            + (5 - 18 * $T + $T * $T + 72 * $C - 58 * $e2) * pow($A, 5) / 120
        ) + 500000.0;

        $northing = $k0 * (
            $M + $N * tan($φ) * (
                pow($A, 2) / 2
                + (5 - $T + 9 * $C + 4 * $C * $C) * pow($A, 4) / 24
                + (61 - 58 * $T + $T * $T + 600 * $C - 330 * $e2) * pow($A, 6) / 720
            )
        );

        if ($lat < 0) {
            $northing += 10000000.0; // südliche Hemisphäre
        }

        // Zonenbuchstabe (nur grobe Einteilung)
        $zoneLetters = "CDEFGHJKLMNPQRSTUVWX";
        $zoneBand = $zoneLetters[(int)floor(($lat + 80) / 8)];
        $zoneCode = $zone . $zoneBand;

        return sprintf("%s %.0f %.0f", $zoneCode, $easting, $northing);
    }
}
