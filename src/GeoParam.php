<?php

declare(strict_types=1);

namespace Geo;

class GeoParam
{
    public readonly float $lat;
    public readonly float $lon;

    public function __construct(string $lat, string $lon)
    {
        $this->lat = self::parseCoordinate($lat, -90, 90, 'Breitengrad');
        $this->lon = self::parseCoordinate($lon, -180, 180, 'Längengrad');
    }

    public static function parseCoordinate(string $input, float $min, float $max, string $label): float
    {
        if (is_numeric($input)) {
            $val = (float)$input;
            if ($val >= $min && $val <= $max) {
                return $val;
            }
        }

        if (preg_match('/(\d+)[_\s](\d+)[_\s](\d+(?:\.\d+)?)[_\s]?([NSEW])?/i', $input, $m)) {
            $deg = (float)$m[1];
            $minu = (float)$m[2];
            $sec = (float)$m[3];
            $dir = strtoupper($m[4] ?? '');

            $decimal = $deg + $minu / 60 + $sec / 3600;

            if (in_array($dir, ['S', 'W'])) {
                $decimal *= -1;
            }

            if ($decimal >= $min && $decimal <= $max) {
                return $decimal;
            }
        }

        throw new \InvalidArgumentException("Ungültiger Wert für $label: $input");
    }

    /** @psalm-api */
    public static function fromGeoHackParams(string $param): self
    {
        $parts = preg_split('/[_\s]+/', $param);
        $latParts = [];
        $lonParts = [];
        $latFound = false;
        $lonFound = false;

        foreach ($parts as $i => $part) {
            if (preg_match('/^[NS]$/i', $part) && !$latFound) {
                $latParts = array_slice($parts, $i - 3, 4);
                $latFound = true;
            }
            if (preg_match('/^[EW]$/i', $part) && !$lonFound) {
                $lonParts = array_slice($parts, $i - 3, 4);
                $lonFound = true;
            }
        }

        if (!$latFound || !$lonFound) {
            throw new \InvalidArgumentException("Unvollständige oder nicht erkannte params: $param");
        }

        $latStr = implode('_', $latParts);
        $lonStr = implode('_', $lonParts);
        return new self($latStr, $lonStr);
    }
}
