<?php

declare(strict_types=1);

namespace Geo;

use Geo\GeoFormatter;

class GeoHelper
{
    public static function formatDMS(float $lat, float $lon): string
    {
        $latStr = GeoFormatter::toDMS($lat, true);
        $lonStr = GeoFormatter::toDMS($lon, false);
        return $latStr . ' / ' . $lonStr;
    }

    /** @psalm-api */
    public static function extractCityFromAddress(?string $address): string
    {
        if ($address === null || $address === '') {
            return 'unbekannt';
        }
        $parts = explode(',', $address);
        $count = count($parts);
        return trim($count >= 2 ? $parts[$count - 2] : $parts[0]);
    }

    /** @psalm-api */
    public static function fallbackAddress(?string $address): string
    {
        return ($address !== null && $address !== '')
            ? $address
            : '<span class="text-muted">Keine Adresse verfügbar</span>';
    }
}
