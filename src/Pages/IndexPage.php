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

        $body = <<<HTML
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="card-title mb-0"><i class="bi bi-geo-alt me-1"></i> Koordinaten</h5>
      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-download me-1"></i> Exportieren
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><h6 class="dropdown-header"><i class="bi bi-box-arrow-up me-1"></i> Export als …</h6></li>
          <li>
            <a class="dropdown-item" href="?lat={$lat}&lon={$lon}&output=geojson">
              <i class="bi bi-braces me-2 text-primary"></i> GeoJSON
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="?lat={$lat}&lon={$lon}&output=csv">
              <i class="bi bi-filetype-csv me-2 text-success"></i> CSV
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="?lat={$lat}&lon={$lon}&output=kml">
              <i class="bi bi-globe me-2 text-danger"></i> KML
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="?lat={$lat}&lon={$lon}&output=vcard">
              <i class="bi bi-person-vcard me-2 text-warning"></i> vCard (.vcf)
            </a>
          </li>
        </ul>
      </div>
    </div>
    <span class="card-text">
      <strong>Dezimal:</strong> {$lat}, {$lon}<br>
      <strong>DMS:</strong> {$latDMS} / {$lonDMS}<br>
      <strong>UTM:</strong> {$utm}<br>
      <strong>Geo URI:</strong> <a rel="nofollow" href="{$geoUri}">{$geoUri}</a><br>
HTML;

        if ($address !== null && $address !== '') {
            $body .= <<<HTML
      <button class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="collapse" data-bs-target="#addressBox" aria-expanded="false" aria-controls="addressBox">
        <i class="bi bi-geo-alt me-1"></i> Adresse anzeigen
      </button>
      <div id="addressBox" class="collapse mt-2">
        <div class="alert alert-light fst-italic">{$safeAddress}</div>
      </div>
HTML;
        }

        $body .= <<<HTML
    </span>
  </div>
</div>

<div id="map" class="map-container border rounded mb-4"></div>

<h2 class="mt-4">Kartenanbieter</h2>
<input id="filter" class="form-control mb-3" placeholder="Suche Anbieter..." onkeyup="filterLinks()">
<table class="table table-bordered table-striped table-hover align-middle" id="linktable">
HTML;

        foreach ($groups as $group => $links) {
            $body .= "<tr class='table-secondary group'><th colspan='2'>" . htmlspecialchars($group) . "</th></tr>";
            foreach ($links as $name => $url) {
                $safeName = htmlspecialchars($name);
                $safeUrl = htmlspecialchars($url);
                $body .= "<tr><td>{$safeName}</td><td><a href=\"{$safeUrl}\" target=\"_blank\"><i class=\"bi bi-box-arrow-up-right me-1\"></i>Link</a></td></tr>";
            }
        }

        $body .= <<<HTML
					</table>
					<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
					<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
					<script>
					  const map = L.map('map').setView([{$lat}, {$lon}], 13);
					  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; OpenStreetMap contributors'
					  }).addTo(map);
					  L.marker([{$lat}, {$lon}]).addTo(map)
						.bindPopup('Koordinaten<br>{$lat}, {$lon}').openPopup();

					  function filterLinks() {
						const input = document.getElementById("filter").value.toLowerCase();
						const rows = document.querySelectorAll("#linktable tr");
						let lastGroup = null;

						rows.forEach(row => {
						  if (row.classList.contains("group")) {
							row.style.display = "none";
							lastGroup = row;
						  } else {
							const match = row.cells[0].textContent.toLowerCase().includes(input);
							row.style.display = match ? "" : "none";
							if (match && lastGroup) {
							  lastGroup.style.display = "";
							  lastGroup = null;
							}
						  }
						});
					  }
					</script>
HTML;

        Layout::render($pageTitle, $body);
    }
}
