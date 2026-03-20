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

        $body = <<<HTML
<div class="card mb-4">
  <div class="card-body">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="card-title mb-0"><i class="bi bi-geo-alt me-1"></i> Koordinaten</h5>
HTML;

        if ($link !== null) {
            $safeLink = htmlspecialchars($link);
            $body .= <<<HTML
				<div class="btn-group" role="group" aria-label="Export">
					<a href="{$safeLink}" class="btn btn-primary btn-sm" target="_blank">
						<i class="bi bi-map me-1"></i> {$label} Dienst öffnen
					</a>
				</div>
				</div>
HTML;
        }

        $body .= <<<HTML
		<p class="card-text">
		  <strong>Dezimal:</strong> {$lat}, {$lon}<br>
		  <strong>DMS:</strong> {$dms}<br>
		  <strong>UTM:</strong> {$utm}<br>
		  <strong>Geo URI:</strong> <a rel="nofollow" href="{$geoUri}">{$geoUri}</a><br>
		</p>
		<div class="btn-group mt-2" role="group">
		  <a class="btn btn-outline-secondary btn-sm" href="?lat={$lat}&lon={$lon}&output=vcard">
			<i class="bi bi-person-vcard me-1"></i> vCard herunterladen
		  </a>
		  <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#vcfBox">
			<i class="bi bi-eye me-1"></i> vCard anzeigen
		  </button>
		</div>
		<div id="vcfBox" class="collapse mt-2">
		  <pre class="bg-light p-2 border rounded small">{$vcfEncoded}</pre>
		</div>
  </div>
</div>
HTML;

        if ($address !== null && $address !== '') {
            $safeAddress = htmlspecialchars($address);
            $body .= <<<HTML
<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title"><i class="bi bi-geo-alt me-1"></i> Adresse</h5>
    <p class="card-text">{$safeAddress}</p>
  </div>
</div>
HTML;
        }

        if ($link !== null) {
            $body .= <<<HTML
					<div id="map" class="map-container border rounded mb-4"></div>
					<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
					<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
					<script>
					  const markerIcon = L.divIcon({ html: '<i class="bi bi-geo-alt-fill" style="font-size:1.8rem;color:#0d6efd;line-height:1"></i>', className: '', iconAnchor: [9, 30], popupAnchor: [0, -30] });
					  const map = L.map('map').setView([{$lat}, {$lon}], 13);
					  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; OpenStreetMap contributors'
					  }).addTo(map);
					  L.marker([{$lat}, {$lon}], {icon: markerIcon}).addTo(map)
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
        }

        Layout::render($pageTitle, $body);
    }
}
