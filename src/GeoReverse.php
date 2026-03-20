<?php

declare(strict_types=1);

namespace Geo;

class GeoReverse
{
    private static function cacheDir(): string
    {
        return rtrim(EnvLoader::get('CACHE_DIR', __DIR__ . '/../cache/'), '/') . '/';
    }

    public static function getAddress(float $lat, float $lon, bool $withDebug = false): ?string
    {
        $cacheDir = self::cacheDir();
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $cacheMaxAge = (int) EnvLoader::get('CACHE_TTL', '2592000'); // 30 Tage
        $now = time();

        $hash = md5($lat . ',' . $lon);
        $cacheFile = $cacheDir . $hash . '.json';

        $isHit = false;
        $address = null;

        if (file_exists($cacheFile) && filemtime($cacheFile) > $now - $cacheMaxAge) {
            $json = file_get_contents($cacheFile);
            /** @var array{address?: string}|null $data */
            $data = json_decode($json !== false ? $json : '{}', true);
            $address = $data['address'] ?? null;
            $isHit = true;

            Logger::get()?->info("GeoReverse Cache HIT", ['lat' => $lat, 'lon' => $lon, 'file' => $cacheFile]);
        } else {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon&zoom=18&addressdetails=1";
            $options = [
                "http" => ["header" => "User-Agent: GeoHackBot/1.0\r\n"]
            ];
            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);

            if ($response !== false && $response !== '') {
                /** @var array{address?: array<string, string>, display_name?: string}|null $data */
                $data = json_decode($response, true);
                $parts = $data['address'] ?? [];
                $displayName = $data['display_name'] ?? null;
                $address = ($parts !== [])
                    ? self::formatAddressParts($parts)
                    : $displayName;

                if ($address !== null) {
                    file_put_contents($cacheFile, json_encode(['address' => $address]));
                    Logger::get()?->info("GeoReverse Cache MISS → gespeichert", ['lat' => $lat, 'lon' => $lon, 'file' => $cacheFile]);
                }
            } else {
                Logger::get()?->warning("GeoReverse: keine Antwort von Nominatim", ['lat' => $lat, 'lon' => $lon]);
            }
        }

        if ($withDebug) {
            Logger::get()?->debug("GeoReverse Debug", [
                'lat' => $lat,
                'lon' => $lon,
                'address' => $address,
                'cache_hit' => $isHit,
                'cache_file' => $cacheFile
            ]);
        }

        return $address;
    }

    /**
     * Formats a Nominatim address-parts array into a human-readable string
     * using country-appropriate notation (e.g. "Straße Nr" for DE/AT/CH,
     * "Nr Street" for US/CA/AU).
     *
     * @param array<string, string> $parts
     */
    private static function formatAddressParts(array $parts): string
    {
        $country = strtolower($parts['country_code'] ?? '');

        // Countries where house number comes BEFORE the street name
        $numberFirst = ['us', 'ca', 'au', 'nz', 'ph', 'mx', 'gb', 'ie'];

        $road = $parts['road']
            ?? $parts['pedestrian']
            ?? $parts['footway']
            ?? $parts['path']
            ?? '';
        $houseNumber = $parts['house_number'] ?? '';

        if ($road !== '' && $houseNumber !== '') {
            $street = in_array($country, $numberFirst)
                ? $houseNumber . ' ' . $road
                : $road . ' ' . $houseNumber;
        } else {
            $street = $road !== '' ? $road : $houseNumber;
        }

        $suburb = $parts['suburb']
            ?? $parts['quarter']
            ?? $parts['neighbourhood']
            ?? $parts['district']
            ?? '';

        $city = $parts['city']
            ?? $parts['town']
            ?? $parts['village']
            ?? $parts['municipality']
            ?? '';

        $postcode    = $parts['postcode'] ?? '';
        $state       = $parts['state'] ?? '';
        $countryName = $parts['country'] ?? '';

        $pieces = [];

        if ($street !== '') {
            $pieces[] = $street;
        }

        if ($suburb !== '' && $suburb !== $city) {
            $pieces[] = $suburb;
        }

        if (in_array($country, $numberFirst)) {
            // Anglo-American: City, State Postcode
            if ($city !== '') {
                $pieces[] = $city;
            }
            if ($state !== '' || $postcode !== '') {
                $pieces[] = trim($state . ' ' . $postcode);
            }
        } else {
            // European: Postcode City, State
            if ($postcode !== '' && $city !== '') {
                $pieces[] = $postcode . ' ' . $city;
            } elseif ($city !== '') {
                $pieces[] = $city;
            } elseif ($postcode !== '') {
                $pieces[] = $postcode;
            }
            if ($state !== '' && $state !== $city) {
                $pieces[] = $state;
            }
        }

        if ($countryName !== '') {
            $pieces[] = $countryName;
        }

        return implode(', ', array_filter($pieces, fn (string $p): bool => $p !== ''));
    }

    /** @psalm-api */
    public static function cleanupCache(?string $cacheDir = null, ?int $maxAge = null): int
    {
        $cacheDir = $cacheDir ?? self::cacheDir();
        $maxAge = $maxAge ?? (int) EnvLoader::get('CACHE_TTL', '2592000');

        if (!is_dir($cacheDir)) {
            return 0;
        }

        $deleted = 0;
        $files = glob($cacheDir . '*.json');
        if ($files !== false) {
            foreach ($files as $file) {
                if (filemtime($file) < time() - $maxAge) {
                    unlink($file);
                    $deleted++;
                }
            }
        }
        return $deleted;
    }
}
