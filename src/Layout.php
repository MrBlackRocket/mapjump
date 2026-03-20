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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { padding-top: 2rem; }
    .address-box { background: #f8f9fa; padding: 1rem; border-left: 4px solid #0d6efd; margin-bottom: 1rem; }
    .map-container { height: 300px; margin-bottom: 2rem; }
  </style>
{$matomoHtml}
</head>
<body>
  <div class="container">
    <h1 class="mb-4">{$safeTitle}</h1>
    {$body}
  </div>
	<footer class="mt-5 text-center text-muted small">
	  <hr>
	  <p>
		  <a href="?view=hilfe" class="link-secondary">Hilfe</a> – <a href="?view=impressum" class="link-secondary">Impressum</a>  –  <a href="?view=datenschutz" class="link-secondary">Datenschutz</a>  –  <a href="?view=lizenz" class="link-secondary">Lizenz</a><br />
		  <strong>MapJump</strong> – freies Karten-Link-Tool, <i class="bi bi-c-circle small"></i> 2024–{$year} MrBlackRocket<br />
		  Inspiriert von <a href="https://geohack.toolforge.org/" target="_blank" rel="nofollow">GeoHack</a> von Magnus Manske
	  </p>
	</footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;
    }
}
