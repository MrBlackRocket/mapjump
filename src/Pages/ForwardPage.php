<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\Geo\GeoParam;
use Geo\Map\MapSource;
use Geo\Layout;

class ForwardPage
{
    public static function render(GeoParam $geo, string $service, ?string $title = null): void
    {
        $lat = $geo->lat;
        $lon = $geo->lon;

        $destUrl = MapSource::getLinkForService($service, $lat, $lon);

        if ($destUrl === null) {
            header("Location: ?lat={$lat}&lon={$lon}");
            exit;
        }

        $serviceName = MapSource::getNameForAlias($service)
            ?? ucfirst(str_replace('_', ' ', $service));

        $safeServiceName = htmlspecialchars($serviceName);
        $safeDestUrl     = htmlspecialchars($destUrl);
        $jsDestUrl       = json_encode($destUrl);

        $backParams = "?lat={$lat}&lon={$lon}";
        if ($title !== null && $title !== '') {
            $backParams .= '&title=' . urlencode($title);
        }
        $safeBackUrl = htmlspecialchars($backParams);

        $body = <<<HTML
<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
  <h5 class="text-base font-semibold flex items-center gap-1.5 mb-3">
    <i data-lucide="shield-alert"></i> Sie verlassen MapJump
  </h5>

  <p class="text-sm text-gray-700 mb-2">
    Sie werden zu <strong>{$safeServiceName}</strong> weitergeleitet.
    Externe Kartendienste können Ihre IP-Adresse und Ihr Nutzungsverhalten erfassen.
  </p>

  <p class="text-xs text-gray-400 break-all mb-5">{$safeDestUrl}</p>

  <div x-data="{ seconds: 45 }"
       x-init="setInterval(() => { if (seconds > 0) seconds-- }, 1000)"
       class="text-xs text-gray-400 mb-5">
    Automatische Weiterleitung in <span x-text="seconds"></span> Sekunden …
  </div>

  <div class="flex flex-wrap gap-3">
    <a href="{$safeDestUrl}" target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
      <i data-lucide="external-link"></i> Weiter zu {$safeServiceName}
    </a>
    <a href="{$safeBackUrl}"
       class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 text-sm text-gray-700 rounded hover:bg-gray-50">
      <i data-lucide="arrow-left"></i> Zurück
    </a>
  </div>
</div>
<script>setTimeout(() => { window.location.href = {$jsDestUrl}; }, 45000);</script>
HTML;

        Layout::render("MapJump – Weiterleitung zu {$serviceName}", $body);
    }
}
