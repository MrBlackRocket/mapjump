<?php

declare(strict_types=1);

namespace Geo;

use Geo\EnvLoader;

class Layout
{
    public static function render(string $title, string $body): void
    {
        $year = date("Y");
        $matomoUrl = EnvLoader::get('MATOMO_URL', '');
        $matomoSiteId = EnvLoader::get('MATOMO_SITE_ID', '');
        $matomoHtml = '';
        if ($matomoUrl !== '' && $matomoSiteId !== '') {
            $matomoUrl = rtrim(htmlspecialchars($matomoUrl, ENT_QUOTES, 'UTF-8'), '/') . '/';
            $matomoSiteIdInt = (int) $matomoSiteId;
            $matomoHtml = <<<MATOMO
    <!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="{$matomoUrl}";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '{$matomoSiteIdInt}']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img referrerpolicy="no-referrer-when-downgrade" src="{$matomoUrl}matomo.php?idsite={$matomoSiteIdInt}&amp;rec=1" style="border:0;" alt="" /></p></noscript>
<!-- End Matomo Code -->
MATOMO;
        }
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        echo <<<HTML
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$safeTitle}</title>
  <link href="public/css/app.css" rel="stylesheet">
  <link href="public/css/leaflet.css" rel="stylesheet">
  <style>[x-cloak]{display:none!important}[data-lucide]{display:inline-block;vertical-align:middle;width:1em;height:1em}</style>
{$matomoHtml}
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
  <main class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">{$safeTitle}</h1>
    {$body}
  </main>
  <footer class="text-center mt-12 pb-8 text-sm text-gray-500">
    <hr class="border-gray-200 mb-4 max-w-4xl mx-auto">
    <p>
      <a href="?view=hilfe" class="hover:underline">Hilfe</a> –
      <a href="?view=impressum" class="hover:underline">Impressum</a> –
      <a href="?view=datenschutz" class="hover:underline">Datenschutz</a> –
      <a href="?view=lizenz" class="hover:underline">Lizenz</a><br>
      <strong>MapJump</strong> – freies Karten-Link-Tool, © 2024–{$year} MrBlackRocket<br>
      Inspiriert von <a href="https://geohack.toolforge.org/" target="_blank" rel="nofollow" class="hover:underline">GeoHack</a> von Magnus Manske
    </p>
  </footer>
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('dropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; },
        close() { this.open = false; }
      }));
      Alpine.data('collapse', () => ({
        show: false,
        toggle() { this.show = !this.show; }
      }));
    });
  </script>
  <script src="public/js/lucide.min.js"></script>
  <script defer src="public/js/alpine.min.js"></script>
  <script>document.addEventListener('DOMContentLoaded', () => lucide.createIcons());</script>
</body>
</html>
HTML;
    }
}
