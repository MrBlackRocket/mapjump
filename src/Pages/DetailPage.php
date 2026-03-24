<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\GeoParam;
use Geo\GeoReverse;
use Geo\GeoFormatter;
use Geo\GeoHelper;
use Geo\MapSource;
use Geo\Exporter;
use Geo\Layout;

class DetailPage
{
    public static function render(GeoParam $geo, string $service, ?string $title = null): void
    {
        $lat = $geo->lat;
        $lon = $geo->lon;

        $address = GeoReverse::getAddress($lat, $lon);
        $dms = GeoHelper::formatDMS($lat, $lon);
        $utm = GeoFormatter::toUTM($lat, $lon);
        $geoUri = sprintf("geo:%f,%f", $lat, $lon);
        $label = ucfirst(str_replace('_', ' ', $service));
        $link = MapSource::getLinkForService($service, $lat, $lon);

        $vcf = Exporter::vcard($geo, $address, $title);
        $vcfEncoded = htmlspecialchars($vcf);

        $pageTitle = ($title !== null && $title !== '') ? "MapJump – {$title}" : "MapJump – {$label}";

        $markerSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="#0d6efd"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3" fill="white"/></svg>';

        $body = <<<HTML
<article>
  <header style="display:flex; justify-content:space-between; align-items:center;">
    <h5 style="margin:0"><i data-lucide="map-pin"></i> Koordinaten</h5>
HTML;

        if ($link !== null) {
            $safeLink = htmlspecialchars($link);
            $body .= <<<HTML
    <a href="{$safeLink}" class="btn-group" target="_blank" role="button">
      <i data-lucide="map"></i> {$label} öffnen
    </a>
HTML;
        }

        $body .= <<<HTML
  </header>
  <p>
    <strong>Dezimal:</strong> {$lat}, {$lon}<br>
    <strong>DMS:</strong> {$dms}<br>
    <strong>UTM:</strong> {$utm}<br>
    <strong>Geo URI:</strong> <a rel="nofollow" href="{$geoUri}">{$geoUri}</a>
  </p>
  <div x-data="{ show: false }" class="btn-group">
    <a href="?lat={$lat}&lon={$lon}&output=vcard" role="button" class="outline secondary">
      <i data-lucide="contact"></i> vCard herunterladen
    </a>
    <button class="outline secondary" @click="show = !show">
      <i data-lucide="eye"></i> vCard anzeigen
    </button>
    <div x-show="show" x-cloak x-transition style="width:100%; margin-top:0.5rem">
      <pre>{$vcfEncoded}</pre>
    </div>
  </div>
</article>
HTML;

        if ($address !== null && $address !== '') {
            $safeAddress = htmlspecialchars($address);
            $body .= <<<HTML
<article>
  <header><h5 style="margin:0"><i data-lucide="map-pin"></i> Adresse</h5></header>
  <p>{$safeAddress}</p>
</article>
HTML;
        }

        if ($link !== null) {
            $body .= <<<HTML
<div id="map" class="map-container"></div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
  const markerIcon = L.divIcon({
    html: '{$markerSvg}',
    className: '',
    iconAnchor: [14, 28],
    popupAnchor: [0, -28]
  });
  const map = L.map('map').setView([{$lat}, {$lon}], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  L.marker([{$lat}, {$lon}], {icon: markerIcon}).addTo(map)
    .bindPopup('Koordinaten<br>{$lat}, {$lon}').openPopup();
</script>
HTML;
        }

        Layout::render($pageTitle, $body);
    }
}
