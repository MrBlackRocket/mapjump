<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\Geo\GeoFormatter;
use Geo\Geo\GeoParam;
use Geo\Map\MapSource;
use Geo\Nominatim\ReverseGeocoder;
use Geo\Layout;

class IndexPage
{
    public static function render(GeoParam $geo, ?string $title = null): void
    {
        $lat = $geo->lat;
        $lon = $geo->lon;
        $latDMS = GeoFormatter::toDMS($lat, true);
        $lonDMS = GeoFormatter::toDMS($lon, false);
        $utm = GeoFormatter::toUTM($lat, $lon);
        $geoUri = "geo:$lat,$lon";
        $address = ReverseGeocoder::getAddress($lat, $lon);
        $safeAddress = htmlspecialchars($address ?? '');
        $groups = MapSource::renderLinks($lat, $lon);

        $pageTitle = ($title !== null && $title !== '')
            ? "MapJump – " . $title
            : "MapJump – ($latDMS / $lonDMS)";

        $markerSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="#2563eb"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3" fill="white"/></svg>';

        $body = <<<HTML
<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 mb-4">
  <div class="flex justify-between items-center mb-3">
    <h5 class="text-base font-semibold flex items-center gap-1.5">
      <i data-lucide="map-pin"></i> Koordinaten
    </h5>
    <div class="relative inline-block" x-data="dropdown">
      <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50 focus:outline-none"
              @click="toggle()" @click.outside="close()">
        <i data-lucide="download"></i> Exportieren
        <i data-lucide="chevron-down"></i>
      </button>
      <ul class="absolute right-0 mt-1 w-52 bg-white rounded border border-gray-200 shadow-lg z-50 py-1 list-none"
          x-show="open" x-cloak x-transition>
        <li class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">Export als …</li>
        <li><a href="?lat={$lat}&lon={$lon}&output=geojson"
               class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-blue-50">
          <i data-lucide="braces"></i> GeoJSON</a></li>
        <li><a href="?lat={$lat}&lon={$lon}&output=csv"
               class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-blue-50">
          <i data-lucide="file-spreadsheet"></i> CSV</a></li>
        <li><a href="?lat={$lat}&lon={$lon}&output=kml"
               class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-blue-50">
          <i data-lucide="globe"></i> KML</a></li>
        <li class="border-t border-gray-100 mt-1 pt-1">
          <a href="?lat={$lat}&lon={$lon}&output=vcard"
             class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-blue-50">
            <i data-lucide="contact"></i> vCard (.vcf)</a></li>
      </ul>
    </div>
  </div>
  <p class="text-sm leading-6">
    <strong>Dezimal:</strong> {$lat}, {$lon}<br>
    <strong>DMS:</strong> {$latDMS} / {$lonDMS}<br>
    <strong>UTM:</strong> {$utm}<br>
    <strong>Geo URI:</strong> <a href="{$geoUri}" rel="nofollow" class="text-blue-600 hover:underline">{$geoUri}</a>
  </p>
HTML;

        if ($address !== null && $address !== '') {
            $body .= <<<HTML
  <div x-data="collapse" class="mt-3">
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50"
            @click="toggle()">
      <i data-lucide="map-pin"></i> Adresse anzeigen
    </button>
    <div x-show="show" x-cloak x-transition
         class="mt-2 bg-gray-50 border border-gray-200 px-4 py-3 rounded text-sm italic">
      {$safeAddress}
    </div>
  </div>
HTML;
        }

        $body .= <<<HTML
</div>

<div id="map" class="h-72 rounded overflow-hidden mb-6 border border-gray-200"></div>

<h2 class="text-lg font-semibold mb-3">Kartenanbieter</h2>
<input id="filter" placeholder="Suche Anbieter…" onkeyup="filterLinks()"
       class="w-full px-3 py-2 border border-gray-300 rounded mb-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
<table class="w-full text-sm border-collapse border border-gray-200" id="linktable">
HTML;

        foreach ($groups as $group => $links) {
            $body .= "<tr class='group bg-gray-100'><th class='px-3 py-2 text-left font-semibold text-gray-600 border border-gray-200' colspan='2'>" . htmlspecialchars($group) . "</th></tr>";
            foreach ($links as $name => $url) {
                $safeName = htmlspecialchars($name);
                $safeUrl = htmlspecialchars($url);
                $body .= "<tr class='border-b border-gray-100 hover:bg-gray-50'><td class='px-3 py-2'>{$safeName}</td><td class='px-3 py-2'><a href=\"{$safeUrl}\" target=\"_blank\" class=\"inline-flex items-center gap-1 text-blue-600 hover:underline\"><i data-lucide=\"external-link\"></i> Link</a></td></tr>";
            }
        }

        $body .= <<<HTML
</table>
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

  function filterLinks() {
    const input = document.getElementById('filter').value.toLowerCase();
    const rows = document.querySelectorAll('#linktable tr');
    let lastGroup = null;
    rows.forEach(row => {
      if (row.classList.contains('group')) {
        row.style.display = 'none';
        lastGroup = row;
      } else {
        const match = row.cells[0].textContent.toLowerCase().includes(input);
        row.style.display = match ? '' : 'none';
        if (match && lastGroup) { lastGroup.style.display = ''; lastGroup = null; }
      }
    });
  }
</script>
HTML;

        Layout::render($pageTitle, $body);
    }
}
