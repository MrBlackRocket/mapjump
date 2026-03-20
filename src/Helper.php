<?php

declare(strict_types=1);

namespace Geo;

use voku\helper\AntiXSS;

class Helper
{
    private static ?AntiXSS $antiXss = null;

    public static function clean(string $input): string
    {
        if (self::$antiXss === null) {
            self::$antiXss = new AntiXSS();
        }

        return self::$antiXss->xss_clean($input);
    }

    /** @psalm-api */
    public static function isTruthy(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes'], true);
    }
}
