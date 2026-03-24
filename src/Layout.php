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
  <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
  <style>
    [x-cloak] { display: none !important; }
    body { padding-top: 2rem; }
    main { max-width: 960px; margin: 0 auto; padding: 0 1rem; }
    .alert { padding: 0.75rem 1rem; border-radius: var(--pico-border-radius); margin-bottom: 1rem; border: 1px solid transparent; }
    .alert-danger  { background: #fff0f0; border-color: #f5c6cb; color: #842029; }
    .alert-light   { background: var(--pico-card-background-color); border-color: var(--pico-border-color); }
    .alert-warning { background: #fff8e1; border-color: #ffe082; color: #5d4037; }
    .badge { display: inline-block; padding: 0.2em 0.55em; border-radius: 0.3em; font-size: 0.78em; font-weight: 600; color: #fff; }
    .bg-primary   { background: var(--pico-primary); }
    .bg-success   { background: #198754; }
    .bg-warning   { background: #ffc107; color: #000; }
    .bg-info      { background: #0dcaf0; color: #000; }
    .bg-secondary { background: #6c757d; }
    .btn-group { display: inline-flex; flex-wrap: wrap; gap: 0.25rem; }
    .map-container { height: 300px; margin-bottom: 2rem; border-radius: var(--pico-border-radius); overflow: hidden; }
    .text-muted { color: var(--pico-muted-color); }
    .small, small { font-size: 0.875em; }
    .fst-italic { font-style: italic; }
    .dropdown-wrap { position: relative; display: inline-block; }
    .dropdown-menu { position: absolute; right: 0; top: 100%; z-index: 200; min-width: 200px; background: var(--pico-card-background-color); border: 1px solid var(--pico-border-color); border-radius: var(--pico-border-radius); padding: 0.25rem 0; list-style: none; margin: 0.25rem 0 0; box-shadow: 0 4px 12px rgba(0,0,0,.12); }
    .dropdown-menu li a { display: block; padding: 0.4rem 1rem; color: var(--pico-color); text-decoration: none; }
    .dropdown-menu li a:hover { background: var(--pico-primary-background); }
    .dropdown-header { padding: 0.4rem 1rem; font-size: 0.8em; color: var(--pico-muted-color); font-weight: 600; }
    .dropdown-divider { border-top: 1px solid var(--pico-border-color); margin: 0.25rem 0; }
    .address-box { background: var(--pico-card-background-color); padding: 1rem; border-left: 4px solid var(--pico-primary); margin-bottom: 1rem; border-radius: var(--pico-border-radius); }
    .table-secondary td, .table-secondary th { background: var(--pico-table-border-color); font-weight: 600; }
    [data-lucide] { display: inline-block; vertical-align: middle; width: 1em; height: 1em; }
  </style>
{$matomoHtml}
</head>
<body>
  <main>
    <h1>{$safeTitle}</h1>
    {$body}
  </main>
  <footer style="text-align:center; margin-top:3rem;">
    <hr>
    <p class="text-muted small">
      <a href="?view=hilfe">Hilfe</a> – <a href="?view=impressum">Impressum</a> – <a href="?view=datenschutz">Datenschutz</a> – <a href="?view=lizenz">Lizenz</a><br>
      <strong>MapJump</strong> – freies Karten-Link-Tool, © 2024–{$year} MrBlackRocket<br>
      Inspiriert von <a href="https://geohack.toolforge.org/" target="_blank" rel="nofollow">GeoHack</a> von Magnus Manske
    </p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>document.addEventListener('DOMContentLoaded', () => lucide.createIcons());</script>
</body>
</html>
HTML;
    }
}
