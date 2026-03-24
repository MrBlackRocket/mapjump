<?php

declare(strict_types=1);

namespace Geo;

use Geo\Layout;
use Geo\Logger;
use Geo\GeoIP\GeoIP;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Security
{
    public static function checkAccess(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ref = $_SERVER['HTTP_REFERER'] ?? '';

        if (self::isCrawler()) {
            if (EnvLoader::enabled('LOGGING_ENABLED')) {
                Logger::get()?->warning('Blockiert: Crawler erkannt', ['ip' => $ip]);
            }
            self::deny("Zugriff verweigert", "Automatisierte Zugriffe sind nicht erlaubt.");
        }

        // Referrer prüfen
        $envAllowed = getenv('ALLOWED_REFERRERS');
        $envBlacklisted = getenv('BLACKLISTED_REFERRERS');
        $allowed = array_map('trim', explode(',', $envAllowed !== false ? $envAllowed : ''));
        $blacklisted = array_map('trim', explode(',', $envBlacklisted !== false ? $envBlacklisted : ''));
        $allowEmpty = EnvLoader::enabled('ALLOW_EMPTY_REFERRER');

        if (empty($ref) && $allowEmpty) {
            if (EnvLoader::enabled('LOGGING_ENABLED')) {
                Logger::get()?->info('Zugriff erlaubt (leerer Referrer)', ['ip' => $ip]);
            }
        } elseif (!empty($ref)) {
            foreach ($blacklisted as $block) {
                if ($block !== '' && str_contains($ref, $block)) {
                    if (EnvLoader::enabled('LOGGING_ENABLED')) {
                        Logger::get()?->warning('Blockiert: Referrer geblacklistet', ['ip' => $ip, 'referrer' => $ref]);
                    }
                    self::deny("Zugriff verweigert", "Die aufrufende Seite ist explizit blockiert.", $ref);
                }
            }

            $valid = false;
            foreach ($allowed as $origin) {
                if ($origin !== '' && str_starts_with($ref, $origin)) {
                    $valid = true;
                    break;
                }
            }

            if (!$valid) {
                if (EnvLoader::enabled('LOGGING_ENABLED')) {
                    Logger::get()?->warning('Blockiert: Referrer nicht erlaubt', ['ip' => $ip, 'referrer' => $ref]);
                }
                self::deny("Zugriff verweigert", "Die aufrufende Seite ist nicht freigegeben.", $ref);
            }

            if (EnvLoader::enabled('LOGGING_ENABLED')) {
                Logger::get()?->info('Zugriff erlaubt (gültiger Referrer)', ['ip' => $ip, 'referrer' => $ref]);
            }
        } else {
            if (EnvLoader::enabled('LOGGING_ENABLED')) {
                Logger::get()?->warning('Blockiert: Kein Referrer übermittelt', ['ip' => $ip]);
            }
            self::deny("Zugriff verweigert", "Kein Referrer übermittelt.");
        }

        // Länderblock prüfen
        $envBlocked = getenv('BLOCKED_COUNTRIES');
        $blocked = array_values(array_filter(array_map('trim', explode(',', $envBlocked !== false ? $envBlocked : ''))));
        if ($blocked !== []) {
            $country = GeoIP::getCountryCode($ip);
            if (EnvLoader::enabled('LOGGING_ENABLED')) {
                Logger::get()?->info('GeoIP-Ergebnis', ['ip' => $ip, 'country' => $country]);
            }

            if ($country !== null && in_array($country, $blocked, true)) {
                if (EnvLoader::enabled('LOGGING_ENABLED')) {
                    Logger::get()?->warning('Blockiert: Land gesperrt', ['ip' => $ip, 'country' => $country]);
                }
                self::deny("Zugriff verweigert", "Der Zugriff aus <strong>{$country}</strong> ist nicht erlaubt.");
            }
        }

        if (EnvLoader::enabled('LOGGING_ENABLED')) {
            Logger::get()?->info('Zugriff vollständig erlaubt', ['ip' => $ip]);
        }
    }

    public static function isCrawler(): bool
    {
        /** @var CrawlerDetect|null $detector */
        static $detector = null;
        if ($detector === null) {
            $detector = new CrawlerDetect();
        }
        return $detector->isCrawler();
    }

    private static function deny(string $title, string $message, ?string $ref = null): void
    {
        $refLine = ($ref !== null && $ref !== '')
            ? "<hr><div><small>Übermittelter Referrer: <code>" . htmlspecialchars($ref) . "</code></small></div>"
            : "";
        Layout::render($title, "<div class='alert alert-danger'>{$message}{$refLine}</div>");
        exit;
    }
}
