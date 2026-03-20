<?php

declare(strict_types=1);

namespace Geo\Tests;

use Geo\GeoFormatter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GeoFormatterTest extends TestCase
{
    #[Test]
    public function toDmsFormatsLatitudeNorth(): void
    {
        // 52.5° → 52°30'00.00"N
        $result = GeoFormatter::toDMS(52.5, true);
        $this->assertSame("52°30'00.00\"N", $result);
    }

    #[Test]
    public function toDmsFormatsLatitudeSouth(): void
    {
        // -33.8667° → 33°52'00.12"S  (gerundet)
        $result = GeoFormatter::toDMS(-33.8667, true);
        $this->assertStringEndsWith('S', $result);
        $this->assertStringStartsWith('33°', $result);
    }

    #[Test]
    public function toDmsFormatsLongitudeEast(): void
    {
        // 13.3833° → 13°23'00.00"E  (gerundet)
        $result = GeoFormatter::toDMS(13.3833, false);
        $this->assertStringEndsWith('E', $result);
        $this->assertStringStartsWith('13°', $result);
    }

    #[Test]
    public function toDmsFormatsLongitudeWest(): void
    {
        $result = GeoFormatter::toDMS(-74.0, false);
        $this->assertSame("74°00'00.00\"W", $result);
    }

    #[Test]
    public function toUtmReturnsZone33ForBerlin(): void
    {
        // Berlin: 52.5°N 13.383°E liegt in Zone 33U
        $result = GeoFormatter::toUTM(52.5, 13.383);
        $this->assertStringStartsWith('33', $result);
    }

    #[Test]
    public function toUtmContainsEastingAndNorthing(): void
    {
        $result = GeoFormatter::toUTM(52.5, 13.383);
        // Ergebnis hat Format "ZZL EEEEEEE NNNNNNN"
        $this->assertMatchesRegularExpression('/^\d+[A-Z]\s+\d+\s+\d+$/', $result);
    }
}
