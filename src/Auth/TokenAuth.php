<?php

declare(strict_types=1);

namespace Geo\Auth;

use Geo\EnvLoader;
use Geo\Logger;

class TokenAuth
{
    private const MAX_ATTEMPTS    = 5;
    private const WINDOW_SECONDS  = 900;  // 15 Minuten
    private const BLOCK_SECONDS   = 900;  // 15 Minuten gesperrt

    private const SESSION_KEY      = 'geocode_auth';
    private const SESSION_TIME_KEY = 'geocode_auth_time';
    private const SESSION_TTL      = 3600; // 1 Stunde

    // ── Session ────────────────────────────────────────────────────────────

    public static function isAuthenticated(): bool
    {
        if (
            !isset($_SESSION[self::SESSION_KEY])
            || $_SESSION[self::SESSION_KEY] !== true
        ) {
            return false;
        }

        $ts = (int) ($_SESSION[self::SESSION_TIME_KEY] ?? 0);
        if ((time() - $ts) > self::SESSION_TTL) {
            self::logout();
            return false;
        }

        return true;
    }

    public static function login(): void
    {
        $_SESSION[self::SESSION_KEY]      = true;
        $_SESSION[self::SESSION_TIME_KEY] = time();
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_TIME_KEY]);
        session_regenerate_id(true);
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: ?view=geocode-login');
            exit;
        }
    }

    // ── Token-Prüfung ──────────────────────────────────────────────────────

    public static function validateToken(string $token): bool
    {
        $envToken = EnvLoader::get('GEOCODE_TOKEN', '');
        if ($envToken === '' || $token === '') {
            return false;
        }

        // Constant-time-Vergleich verhindert Timing-Angriffe
        return hash_equals($envToken, $token);
    }

    // ── Rate-Limiting ──────────────────────────────────────────────────────

    public static function isRateLimited(string $ip): bool
    {
        $data = self::loadAttempts($ip);

        if ($data['blocked_until'] > time()) {
            return true;
        }

        // Zeitfenster abgelaufen → Zähler zurücksetzen
        if ((time() - $data['window_start']) > self::WINDOW_SECONDS) {
            self::saveAttempts($ip, ['attempts' => 0, 'window_start' => time(), 'blocked_until' => 0]);
        }

        return false;
    }

    public static function blockedUntil(string $ip): int
    {
        return self::loadAttempts($ip)['blocked_until'];
    }

    public static function recordFailure(string $ip): void
    {
        $data = self::loadAttempts($ip);
        $now  = time();

        // Zeitfenster abgelaufen → neu starten
        if (($now - $data['window_start']) > self::WINDOW_SECONDS) {
            $data = ['attempts' => 0, 'window_start' => $now, 'blocked_until' => 0];
        }

        $data['attempts']++;

        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            $data['blocked_until'] = $now + self::BLOCK_SECONDS;
        }

        self::saveAttempts($ip, $data);

        Logger::get()?->warning('Geocode-Login: Fehlversuch', [
            'ip'       => $ip,
            'attempts' => $data['attempts'],
        ]);
    }

    public static function clearAttempts(string $ip): void
    {
        $file = self::attemptsFile($ip);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // ── Hilfsmethoden ──────────────────────────────────────────────────────

    private static function attemptsDir(): string
    {
        $dir = rtrim(EnvLoader::get('CACHE_DIR', __DIR__ . '/../../cache/'), '/') . '/auth/';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        return $dir;
    }

    private static function attemptsFile(string $ip): string
    {
        return self::attemptsDir() . md5($ip) . '.json';
    }

    /**
     * @return array{attempts: int, window_start: int, blocked_until: int}
     */
    private static function loadAttempts(string $ip): array
    {
        $file = self::attemptsFile($ip);
        if (!file_exists($file)) {
            return ['attempts' => 0, 'window_start' => time(), 'blocked_until' => 0];
        }

        $json = file_get_contents($file);
        /** @var array{attempts?: int, window_start?: int, blocked_until?: int}|null $data */
        $data = json_decode($json !== false ? $json : '{}', true);

        return [
            'attempts'      => (int) ($data['attempts']      ?? 0),
            'window_start'  => (int) ($data['window_start']  ?? time()),
            'blocked_until' => (int) ($data['blocked_until'] ?? 0),
        ];
    }

    /**
     * @param array{attempts: int, window_start: int, blocked_until: int} $data
     */
    private static function saveAttempts(string $ip, array $data): void
    {
        file_put_contents(self::attemptsFile($ip), json_encode($data));
    }
}
