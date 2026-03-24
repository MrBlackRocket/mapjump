<?php

declare(strict_types=1);

namespace Geo\Nominatim;

use Geo\EnvLoader;
use Geo\Logger;

class ForwardGeocoder
{
    /**
     * Sucht Koordinaten für eine Adresse oder einen Ortsnamen.
     *
     * @return list<array{lat: float, lon: float, display_name: string}>
     */
    public static function search(string $query, int $limit = 5): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $cacheFile   = self::cacheDir() . md5(strtolower($query)) . '.json';
        $cacheMaxAge = (int) EnvLoader::get('CACHE_TTL', '2592000');

        if (file_exists($cacheFile) && filemtime($cacheFile) > time() - $cacheMaxAge) {
            $json = file_get_contents($cacheFile);
            /** @var list<array{lat: float, lon: float, display_name: string}>|null $cached */
            $cached = json_decode($json !== false ? $json : '[]', true);
            Logger::get()?->info('ForwardGeocoder Cache HIT', ['query' => $query]);
            return is_array($cached) ? $cached : [];
        }

        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q'              => $query,
            'format'         => 'json',
            'limit'          => $limit,
            'addressdetails' => 0,
        ]);

        $context  = stream_context_create(['http' => ['header' => "User-Agent: GeoHackBot/1.0\r\n"]]);
        $response = @file_get_contents($url, false, $context);

        if ($response === false || $response === '') {
            Logger::get()?->warning('ForwardGeocoder: keine Antwort von Nominatim', ['query' => $query]);
            return [];
        }

        /** @var list<array{lat: string, lon: string, display_name: string}>|null $raw */
        $raw = json_decode($response, true);
        if (!is_array($raw)) {
            return [];
        }

        $results = [];
        foreach ($raw as $item) {
            if (!isset($item['lat'], $item['lon'], $item['display_name'])) {
                continue;
            }
            $results[] = [
                'lat'          => (float) $item['lat'],
                'lon'          => (float) $item['lon'],
                'display_name' => (string) $item['display_name'],
            ];
        }

        file_put_contents($cacheFile, json_encode($results));
        Logger::get()?->info('ForwardGeocoder Cache MISS', ['query' => $query, 'results' => count($results)]);

        return $results;
    }

    private static function cacheDir(): string
    {
        $dir = rtrim(EnvLoader::get('CACHE_DIR', __DIR__ . '/../../cache/'), '/') . '/geocode/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }
}
