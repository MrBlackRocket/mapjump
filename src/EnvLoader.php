<?php

declare(strict_types=1);

namespace Geo;

class EnvLoader
{
    /**
     * Lade die Umgebungsvariablen aus der .env-Datei, falls vorhanden.
     */
    public static function load(string $path = __DIR__ . '/../config/.env'): void
    {
        if (!file_exists($path)) {
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            $key = trim($parts[0] ?? '');
            $value = trim($parts[1] ?? '');

            if ($key === '') {
                continue;
            }

            $envValue = getenv($key);
            $_ENV[$key] = $_SERVER[$key] = $envValue === false ? $value : $envValue;
            putenv("$key=$value");
        }
    }

    /**
     * Gib eine Umgebungsvariable zurück oder einen Defaultwert
     */
    public static function get(string $key, string $default = ''): string
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return trim($value);
    }

    /**
     * Prüft, ob eine boolesche Umgebungsvariable als "aktiviert" gilt.
     */
    public static function enabled(string $key): bool
    {
        return strtolower(self::get($key)) === 'true' || self::get($key) === '1';
    }
}
