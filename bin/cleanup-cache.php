#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Nominatim-Cache aufräumen
 *
 * Löscht abgelaufene Cache-Einträge gemäß CACHE_TTL aus config/.env.
 * Wird alle 14 Tage per Cron ausgeführt.
 *
 * Verwendung:
 *   php bin/cleanup-cache.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Geo\EnvLoader;
use Geo\GeoReverse;

EnvLoader::load(__DIR__ . '/../config/.env');

function info(string $msg): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}

info('Cache-Cleanup gestartet.');

$deleted = GeoReverse::cleanupCache();

if ($deleted === 0) {
    info('Keine abgelaufenen Einträge gefunden.');
} else {
    info(sprintf('%d abgelaufene Cache-Datei(en) gelöscht.', $deleted));
}

info('Fertig.');
