<?php

declare(strict_types=1);

namespace Geo\Tests;

use Geo\GeoParam;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class GeoParamTest extends TestCase
{
    // ----------------------------------------------------------------
    // fromGeoHackParams
    // ----------------------------------------------------------------

    /** @return array<string, array{string, float, float}> */
    public static function geoHackProvider(): array
    {
        return [
            'DM – Berlin'         => ['52_30_N_13_23_E',         52.5,              13.383333333333],
            'DMS – Berlin'        => ['52_30_15_N_13_23_45_E',   52.504166666667,   13.395833333333],
            'D decimal – Berlin'  => ['52.5_N_13.383_E',         52.5,              13.383],
            'DM – Sydney (S/E)'   => ['33_52_S_151_12_E',       -33.866666666667,   151.2],
            'DM – New York (W)'   => ['40_42_N_74_0_W',          40.7,             -74.0],
            'DMS – Südpol-nahe'   => ['89_59_59_S_0_0_0_E',    -89.999722222222,    0.0],
        ];
    }

    #[Test]
    #[DataProvider('geoHackProvider')]
    public function fromGeoHackParamsParsesAllFormats(
        string $params,
        float $expectedLat,
        float $expectedLon
    ): void {
        $geo = GeoParam::fromGeoHackParams($params);
        $this->assertEqualsWithDelta($expectedLat, $geo->lat, 0.00001);
        $this->assertEqualsWithDelta($expectedLon, $geo->lon, 0.00001);
    }

    #[Test]
    public function fromGeoHackParamsThrowsOnGarbage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        GeoParam::fromGeoHackParams('not_a_coordinate');
    }

    #[Test]
    public function fromGeoHackParamsThrowsOnEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        GeoParam::fromGeoHackParams('');
    }

    // ----------------------------------------------------------------
    // parseCoordinate
    // ----------------------------------------------------------------

    #[Test]
    public function parseCoordinateAcceptsDecimalString(): void
    {
        $result = GeoParam::parseCoordinate('52.5', -90, 90, 'lat');
        $this->assertEqualsWithDelta(52.5, $result, 0.00001);
    }

    #[Test]
    public function parseCoordinateAcceptsNegativeDecimal(): void
    {
        $result = GeoParam::parseCoordinate('-33.8670', -90, 90, 'lat');
        $this->assertEqualsWithDelta(-33.867, $result, 0.00001);
    }

    #[Test]
    public function parseCoordinateThrowsOutOfRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        GeoParam::parseCoordinate('91', -90, 90, 'lat');
    }

    #[Test]
    public function parseCoordinateThrowsOnAlphaString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        GeoParam::parseCoordinate('abc', -90, 90, 'lat');
    }

    // ----------------------------------------------------------------
    // Constructor
    // ----------------------------------------------------------------

    #[Test]
    public function constructorStoresCoordinates(): void
    {
        $geo = new GeoParam('48.137', '11.576');
        $this->assertEqualsWithDelta(48.137, $geo->lat, 0.00001);
        $this->assertEqualsWithDelta(11.576, $geo->lon, 0.00001);
    }
}
