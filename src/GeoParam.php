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
        /** @var string[] $parts */
        $parts    = preg_split('/[_\s]+/', trim($param)) ?: [];
        $latDecimal = null;
        $lonDecimal = null;

        foreach ($parts as $i => $part) {
            if (preg_match('/^[NS]$/i', $part) && $latDecimal === null) {
                $latDecimal = self::dirPartsToDecimal($parts, $i);
            }
            if (preg_match('/^[EW]$/i', $part) && $lonDecimal === null) {
                $lonDecimal = self::dirPartsToDecimal($parts, $i);
            }
        }

        if ($latDecimal === null || $lonDecimal === null) {
            throw new \InvalidArgumentException("Unvollständige oder nicht erkannte params: $param");
        }

        return new self((string)$latDecimal, (string)$lonDecimal);
    }

    /**
     * Liest rückwärts ab dem Richtungsbuchstaben ($parts[$dirIndex])
     * alle vorangehenden Zahlen (Grad / Minuten / Sekunden) und
     * wandelt sie in einen Dezimalwert um.
     * Unterstützt D-, DM- und DMS-Format.
     *
     * @param string[] $parts
     */
    private static function dirPartsToDecimal(array $parts, int $dirIndex): float
    {
        $nums = [];
        for ($j = $dirIndex - 1; $j >= 0; $j--) {
            if (!is_numeric($parts[$j])) {
                break;
            }
            array_unshift($nums, (float)$parts[$j]);
        }

        $decimal  = $nums[0] ?? 0.0;
        $decimal += ($nums[1] ?? 0.0) / 60;
        $decimal += ($nums[2] ?? 0.0) / 3600;

        if (preg_match('/^[SW]$/i', $parts[$dirIndex])) {
            $decimal *= -1;
        }

        return $decimal;
    }
}
