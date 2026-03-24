<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\GeoFormatter;
use Geo\GeoReverse;
use Geo\MapSource;
use Geo\Layout;
use Geo\GeoParam;

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
        $address = GeoReverse::getAddress($lat, $lon);
        $safeAddress = htmlspecialchars($address ?? '');
        $groups = MapSource::renderLinks($lat, $lon);

        $pageTitle = ($title !== null && $title !== '')
            ? "MapJump – " . $title
            : "MapJump – ($latDMS / $lonDMS)";

        $markerSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="#0d6efd"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3" fill="white"/></svg>';

        $body = <<<HTML
<article>
  <header style="display:flex; justify-content:space-between; align-items:center;">
    <h5 style="margin:0"><i data-lucide="map-pin"></i> Koordinaten</h5>
    <div class="dropdown-wrap" x-data="{ open: false }">
      <button class="outline secondary" style="margin:0" @click="open = !open" @click.outside="open = false">
        <i data-lucide="download"></i> Exportieren
      </button>
      <ul class="dropdown-menu" x-show="open" x-cloak x-transition>
        <li><span class="dropdown-header"><i data-lucide="upload"></i> Export als …</span></li>
        <li><a href="?lat={$lat}&lon={$lon}&output=geojson"><i data-lucide="braces"></i> GeoJSON</a></li>
        <li><a href="?lat={$lat}&lon={$lon}&output=csv"><i data-lucide="file-spreadsheet"></i> CSV</a></li>
        <li><a href="?lat={$lat}&lon={$lon}&output=kml"><i data-lucide="globe"></i> KML</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a href="?lat={$lat}&lon={$lon}&output=vcard"><i data-lucide="contact"></i> vCard (.vcf)</a></li>
      </ul>
    </div>
  </header>
  <p>
    <strong>Dezimal:</strong> {$lat}, {$lon}<br>
    <strong>DMS:</strong> {$latDMS} / {$lonDMS}<br>
    <strong>UTM:</strong> {$utm}<br>
    <strong>Geo URI:</strong> <a rel="nofollow" href="{$geoUri}">{$geoUri}</a>
  </p>
HTML;

        if ($address !== null && $address !== '') {
            $body .= <<<HTML
  <div x-data="{ show: false }">
    <button class="outline secondary" style="margin-bottom:0.5rem" @click="show = !show">
      <i data-lucide="map-pin"></i> Adresse anzeigen
    </button>
    <div x-show="show" x-cloak x-transition>
      <p class="alert alert-light fst-italic">{$safeAddress}</p>
    </div>
  </div>
HTML;
        }

        $body .= <<<HTML
</article>

<div id="map" class="map-container"></div>

<h2>Kartenanbieter</h2>
<input id="filter" placeholder="Suche Anbieter..." onkeyup="filterLinks()" style="margin-bottom:1rem">
<table id="linktable">
HTML;

        foreach ($groups as $group => $links) {
            $body .= "<tr class='table-secondary group'><th colspan='2'>" . htmlspecialchars($group) . "</th></tr>";
            foreach ($links as $name => $url) {
                $safeName = htmlspecialchars($name);
                $safeUrl = htmlspecialchars($url);
                $body .= "<tr><td>{$safeName}</td><td><a href=\"{$safeUrl}\" target=\"_blank\"><i data-lucide=\"external-link\"></i> Link</a></td></tr>";
            }
        }

        $body .= <<<HTML
</table>
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
