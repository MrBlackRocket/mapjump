<?php

declare(strict_types=1);

namespace Geo\GeoIP;

use MaxMind\Db\Reader;
use Geo\Logger;
use Geo\EnvLoader;

class GeoIP
{
    protected static ?Reader $reader = null;

    public static function getCountryCode(string $ip): ?string
    {
        $dbPath = EnvLoader::get('GEOIP_DB', __DIR__ . '/../../data/GeoLite2-Country.mmdb');

        if (!self::$reader) {
            if (!file_exists($dbPath)) {
                Logger::get()?->error('GeoIP: Datenbankdatei nicht gefunden', ['path' => $dbPath]);
                return null;
            }
            self::$reader = new Reader($dbPath);
            Logger::get()?->info('GeoIP: Datenbank geladen', ['path' => $dbPath]);
        }

        try {
            $record = self::$reader->get($ip);

            if (!is_array($record) || !isset($record['country']) || !is_array($record['country'])) {
                return null;
            }
            return isset($record['country']['iso_code']) ? (string) $record['country']['iso_code'] : null;
        } catch (\Throwable $e) {
            Logger::get()?->error('GeoIP: Fehler beim Lesen der Datenbank', ['ip' => $ip, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
