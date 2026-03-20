#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * GeoIP-Datenbank aktualisieren
 *
 * Lädt GeoLite2-Country.mmdb.gz von JSDelivr und entpackt sie lokal.
 * Wird monatlich per Cron ausgeführt.
 *
 * Verwendung:
 *   php bin/update-geoip.php
 */

const GEOLITE2_URL  = 'https://cdn.jsdelivr.net/npm/geolite2-country/GeoLite2-Country.mmdb.gz';
const DATA_DIR      = __DIR__ . '/../data';
const TMP_GZ_FILE   = DATA_DIR . '/GeoLite2-Country.mmdb.gz';
const OUTPUT_FILE   = DATA_DIR . '/GeoLite2-Country.mmdb';

function info(string $msg): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}

function fail(string $msg, int $code = 1): never
{
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] FEHLER: ' . $msg . PHP_EOL);
    exit($code);
}

// Verzeichnis anlegen falls nicht vorhanden
if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR, 0755, true)) {
    fail('Verzeichnis ' . DATA_DIR . ' konnte nicht angelegt werden.');
}

// Download
info('Lade GeoLite2-Country.mmdb.gz von JSDelivr...');

$ctx   = stream_context_create(['http' => [
    'timeout' => 60,
    'header'  => "User-Agent: MapJump-GeoIP-Updater/1.0\r\n",
]]);
$bytes = file_get_contents(GEOLITE2_URL, false, $ctx);

if ($bytes === false) {
    fail('Download fehlgeschlagen: ' . GEOLITE2_URL);
}

info(sprintf('Download abgeschlossen (%s KB).', number_format((int) round(strlen($bytes) / 1024), 0, ',', '.')));

// .gz temporär speichern
if (file_put_contents(TMP_GZ_FILE, $bytes) === false) {
    fail('Konnte temporäre Datei nicht schreiben: ' . TMP_GZ_FILE);
}

// Entpacken via gzopen()
info('Entpacke Archiv...');

$gz = gzopen(TMP_GZ_FILE, 'rb');
if ($gz === false) {
    @unlink(TMP_GZ_FILE);
    fail('Konnte .gz-Datei nicht öffnen.');
}

$out = fopen(OUTPUT_FILE . '.tmp', 'wb');
if ($out === false) {
    gzclose($gz);
    @unlink(TMP_GZ_FILE);
    fail('Konnte Ausgabedatei nicht öffnen: ' . OUTPUT_FILE . '.tmp');
}

while (!gzeof($gz)) {
    $chunk = gzread($gz, 65536);
    if ($chunk !== false && $chunk !== '') {
        fwrite($out, $chunk);
    }
}

gzclose($gz);
fclose($out);
@unlink(TMP_GZ_FILE);

// Atomisch einspielen (rename ist atomar auf POSIX, auf Windows best-effort)
if (!rename(OUTPUT_FILE . '.tmp', OUTPUT_FILE)) {
    @unlink(OUTPUT_FILE . '.tmp');
    fail('Konnte Datenbankdatei nicht einsetzen: ' . OUTPUT_FILE);
}

$sizeMB = round(filesize(OUTPUT_FILE) / 1024 / 1024, 1);
info(sprintf('GeoLite2-Country.mmdb gespeichert: %s (%.1f MB)', OUTPUT_FILE, $sizeMB));
info('Fertig. GEOIP_DB in config/.env sollte auf folgenden Pfad zeigen:');
info('  GEOIP_DB=' . realpath(OUTPUT_FILE));
