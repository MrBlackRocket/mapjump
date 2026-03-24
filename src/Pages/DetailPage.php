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

        $markerSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="#2563eb"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3" fill="white"/></svg>';

        $body = <<<HTML
<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 mb-4">
  <div class="flex justify-between items-center mb-3">
    <h5 class="text-base font-semibold flex items-center gap-1.5">
      <i data-lucide="map-pin"></i> Koordinaten
    </h5>
HTML;

        if ($link !== null) {
            $safeLink = htmlspecialchars($link);
            $body .= <<<HTML
    <a href="{$safeLink}" target="_blank"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none">
      <i data-lucide="map"></i> {$label} öffnen
    </a>
HTML;
        }

        $body .= <<<HTML
  </div>
  <p class="text-sm leading-6">
    <strong>Dezimal:</strong> {$lat}, {$lon}<br>
    <strong>DMS:</strong> {$dms}<br>
    <strong>UTM:</strong> {$utm}<br>
    <strong>Geo URI:</strong> <a href="{$geoUri}" rel="nofollow" class="text-blue-600 hover:underline">{$geoUri}</a>
  </p>
  <div x-data="collapse" class="flex flex-wrap gap-2 mt-3">
    <a href="?lat={$lat}&lon={$lon}&output=vcard"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">
      <i data-lucide="contact"></i> vCard herunterladen
    </a>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50"
            @click="toggle()">
      <i data-lucide="eye"></i> vCard anzeigen
    </button>
    <div x-show="show" x-cloak x-transition class="w-full mt-2">
      <pre class="text-xs bg-gray-50 border border-gray-200 rounded p-3 overflow-auto">{$vcfEncoded}</pre>
    </div>
  </div>
</div>
HTML;

        if ($address !== null && $address !== '') {
            $safeAddress = htmlspecialchars($address);
            $body .= <<<HTML
<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 mb-4">
  <h5 class="text-base font-semibold flex items-center gap-1.5 mb-2">
    <i data-lucide="map-pin"></i> Adresse
  </h5>
  <p class="text-sm">{$safeAddress}</p>
</div>
HTML;
        }

        if ($link !== null) {
            $body .= <<<HTML
<div id="map" class="h-72 rounded overflow-hidden mb-6 border border-gray-200"></div>
<script src="public/js/leaflet.min.js"></script>
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
