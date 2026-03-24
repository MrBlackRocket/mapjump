<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\Auth\TokenAuth;
use Geo\Helper;
use Geo\Layout;
use Geo\Nominatim\ForwardGeocoder;

class GeocodePage
{
    public static function render(): void
    {
        TokenAuth::requireAuth();

        $query   = isset($_GET['q']) && is_string($_GET['q']) ? Helper::clean($_GET['q']) : '';
        $results = $query !== '' ? ForwardGeocoder::search($query) : [];

        $resultsHtml = self::buildResults($query, $results);
        $safeQuery   = htmlspecialchars($query);
        $logoutUrl   = htmlspecialchars('?view=geocode-logout');

        $body = <<<HTML
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
          <div class="flex justify-between items-center mb-4">
            <p class="text-sm text-gray-500">Adresse oder Ortsname eingeben, um Koordinaten zu ermitteln.</p>
            <a href="{$logoutUrl}" class="text-xs text-gray-400 hover:text-gray-600">Abmelden</a>
          </div>
          <form method="GET" action="">
            <input type="hidden" name="view" value="geocode">
            <div class="flex gap-2">
              <input
                type="search"
                name="q"
                value="{$safeQuery}"
                placeholder="z. B. Brandenburger Tor, Berlin …"
                class="flex-1 px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
                autofocus
              >
              <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                <i data-lucide="search" class="w-4 h-4"></i> Suchen
              </button>
            </div>
          </form>
          {$resultsHtml}
        </div>
        HTML;

        Layout::render('Geocodierung', $body);
    }

    /**
     * @param list<array{lat: float, lon: float, display_name: string}> $results
     */
    private static function buildResults(string $query, array $results): string
    {
        if ($query === '') {
            return '';
        }

        if ($results === []) {
            return '<p class="text-sm text-gray-500 mt-4">Keine Ergebnisse für „'
                . htmlspecialchars($query) . '" gefunden.</p>';
        }

        $items = '';
        foreach ($results as $r) {
            $safeTitle = htmlspecialchars($r['display_name']);
            $lat       = number_format($r['lat'], 6, '.', '');
            $lon       = number_format($r['lon'], 6, '.', '');
            $url       = htmlspecialchars(
                '?lat=' . $lat . '&lon=' . $lon . '&title=' . urlencode($r['display_name'])
            );

            $items .= <<<HTML
            <li>
              <a href="{$url}" class="flex justify-between items-center px-3 py-2.5 hover:bg-blue-50 rounded text-sm gap-4">
                <span class="text-gray-800">{$safeTitle}</span>
                <span class="text-xs text-gray-400 whitespace-nowrap">{$lat}, {$lon}</span>
              </a>
            </li>
            HTML;
        }

        return '<ul class="mt-4 divide-y divide-gray-100 border border-gray-200 rounded bg-white">'
            . $items . '</ul>';
    }
}
