<?php

declare(strict_types=1);

namespace Geo\Tests;

use Geo\Helper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class HelperTest extends TestCase
{
    // ----------------------------------------------------------------
    // isTruthy
    // ----------------------------------------------------------------

    /** @return array<string, array{string, bool}> */
    public static function truthyProvider(): array
    {
        return [
            '"1" ist truthy'     => ['1',     true],
            '"true" ist truthy'  => ['true',  true],
            '"yes" ist truthy'   => ['yes',   true],
            '"TRUE" ist truthy'  => ['TRUE',  true],
            '"YES" ist truthy'   => ['YES',   true],
            '"0" ist falsy'      => ['0',     false],
            '"false" ist falsy'  => ['false', false],
            '"no" ist falsy'     => ['no',    false],
            'leer ist falsy'     => ['',      false],
            '"on" ist falsy'     => ['on',    false],
        ];
    }

    #[Test]
    #[DataProvider('truthyProvider')]
    public function isTruthyReturnsCorrectBoolean(string $value, bool $expected): void
    {
        $this->assertSame($expected, Helper::isTruthy($value));
    }

    // ----------------------------------------------------------------
    // clean
    // ----------------------------------------------------------------

    #[Test]
    public function cleanPassesSafeString(): void
    {
        $input = 'Berlin';
        $this->assertSame('Berlin', Helper::clean($input));
    }

    #[Test]
    public function cleanStripsScriptTag(): void
    {
        $result = Helper::clean('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $result);
    }

    #[Test]
    public function cleanStripsJavascriptProtocol(): void
    {
        $result = Helper::clean('javascript:alert(1)');
        $this->assertStringNotContainsString('javascript:', strtolower($result));
    }
}
