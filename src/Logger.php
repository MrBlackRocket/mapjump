<?php

declare(strict_types=1);

namespace Geo;

use Monolog\Logger as MonoLogger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private static ?MonoLogger $logger = null;

    public static function init(): void
    {
        if (!EnvLoader::enabled('LOGGING_ENABLED')) {
            return;
        }

        if (self::$logger !== null) {
            return;
        }

        $logPath = EnvLoader::get('LOG_FILE', __DIR__ . '/../mapjump.log');
        $handler = new StreamHandler($logPath, Level::Info);
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        self::$logger = new MonoLogger('mapjump');
        self::$logger->pushHandler($handler);
    }

    public static function get(): ?MonoLogger
    {
        return self::$logger;
    }

    /** @psalm-api */
    public static function enabled(): bool
    {
        return EnvLoader::enabled('LOGGING_ENABLED');
    }
}
