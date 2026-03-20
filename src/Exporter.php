<?php

declare(strict_types=1);

namespace Geo;

use Geo\GeoFormatter;
use Geo\GeoReverse;
use Geo\MapSource;
use Geo\Logger;

class Exporter
{
    public static function handle(string $output, float $lat, float $lon, ?string $title = null): void
    {
        $logger = Logger::get();
        if ($logger !== null) {
            $logger->info('Export request', ['output' => $output, 'lat' => $lat, 'lon' => $lon]);
        }

        $address = GeoReverse::getAddress($lat, $lon);
        $title = $title ?? GeoFormatter::toDMS($lat, true) . ' / ' . GeoFormatter::toDMS($lon, false);
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $escapedAddress = str_replace(["\r", "\n"], '', $address ?? '');

        switch ($output) {
            case 'kml':
                header('Content-Type: application/vnd.google-earth.kml+xml');
                header('Content-Disposition: attachment; filename="coordinates.kml"');
                echo <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
<Document>
  <Placemark>
    <name>{$escapedTitle}</name>
    <description>{$escapedAddress}</description>
    <Point>
      <coordinates>{$lon},{$lat},0</coordinates>
    </Point>
  </Placemark>
</Document>
</kml>
KML;
                break;

            case 'geojson':
                header('Content-Type: application/geo+json');
                header('Content-Disposition: attachment; filename="coordinates.geojson"');
                echo json_encode([
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lon, $lat]
                    ],
                    'properties' => [
                        'title' => $title,
                        'address' => $address
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;

            case 'json':
                header('Content-Type: application/json');
                //header('Content-Disposition: attachment; filename="coordinates.json"');
                echo json_encode([
                    'lat' => $lat,
                    'lon' => $lon,
                    'lat_dms' => GeoFormatter::toDMS($lat, true),
                    'lon_dms' => GeoFormatter::toDMS($lon, false),
                    'utm' => GeoFormatter::toUTM($lat, $lon),
                    'geo_uri' => "geo:{$lat},{$lon}",
                    'address' => $address,
                    'links' => MapSource::flatLinks($lat, $lon)
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;

            case 'csv':
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="coordinates.csv"');
                $csvAddress = str_replace(['"', "\r", "\n"], ["'", '', ''], $address ?? '');
                echo "lat,lon,lat_dms,lon_dms,utm,address\r\n";
                echo implode(',', [
                    $lat,
                    $lon,
                    '"' . GeoFormatter::toDMS($lat, true) . '"',
                    '"' . GeoFormatter::toDMS($lon, false) . '"',
                    '"' . GeoFormatter::toUTM($lat, $lon) . '"',
                    '"' . $csvAddress . '"',
                ]) . "\r\n";
                break;

            case 'vcard':
                header('Content-Type: text/vcard; charset=utf-8');
                header('Content-Disposition: attachment; filename="coordinates.vcf"');
                echo <<<VCF
BEGIN:VCARD
VERSION:3.0
FN:{$escapedTitle}
item1.ADR:;;{$escapedAddress}
item1.X-ABADR:de
GEO:{$lat};{$lon}
END:VCARD
VCF;
                break;

            default:
                http_response_code(400);
                echo "Ungültiges Exportformat.";
        }
    }
    public static function vcard(GeoParam $geo, ?string $address = null, ?string $title = null): string
    {
        $lat = $geo->lat;
        $lon = $geo->lon;
        $escapedAddress = str_replace(["\r", "\n"], '', $address ?? '');
        $fn = ($title !== null && $title !== '') ? $title : GeoFormatter::toDMS($lat, true) . ' / ' . GeoFormatter::toDMS($lon, false);

        return <<<VCF
BEGIN:VCARD
VERSION:3.0
FN:{$fn}
item1.ADR:;;{$escapedAddress}
item1.X-ABADR:de
GEO:{$lat};{$lon}
END:VCARD
VCF;
    }
}
