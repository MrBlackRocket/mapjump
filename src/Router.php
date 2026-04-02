<?php

declare(strict_types=1);

namespace Geo;

use Geo\Auth\TokenAuth;
use Geo\Geo\GeoParam;
use Geo\Map\Exporter;
use Geo\Pages\FormPage;
use Geo\Pages\GeocodePage;
use Geo\Pages\GeocodeLoginPage;
use Geo\Pages\IndexPage;
use Geo\Pages\DetailPage;
use Geo\Pages\ForwardPage;
use Geo\Pages\StaticPage;

/** @psalm-api */
class Router
{
    public function dispatch(): void
    {
        $lat     = isset($_GET['lat'])     && is_string($_GET['lat'])     ? $_GET['lat']                                   : null;
        $lon     = isset($_GET['lon'])     && is_string($_GET['lon'])     ? $_GET['lon']                                   : null;
        $params  = isset($_GET['params'])  && is_string($_GET['params'])  ? $_GET['params']                                : null;
        $service = isset($_GET['service']) && is_string($_GET['service']) ? Helper::clean($_GET['service'])               : null;
        $title   = isset($_GET['title'])   && is_string($_GET['title'])   ? Helper::clean($_GET['title'])                  : null;
        $view    = isset($_GET['view'])    && is_string($_GET['view'])    ? Helper::clean($_GET['view'])                   : null;
        $output  = isset($_GET['output'])  && is_string($_GET['output'])  ? strtolower(trim($_GET['output']))              : null;
        $link    = isset($_GET['link'])    && is_string($_GET['link'])    ? Helper::clean($_GET['link'])                    : null;

        // Spezialseiten
        if ($view === 'geocode') {
            GeocodePage::render();
            return;
        }

        if ($view === 'geocode-login') {
            GeocodeLoginPage::render();
            return;
        }

        if ($view === 'geocode-logout') {
            TokenAuth::logout();
            header('Location: ?view=geocode-login');
            exit;
        }

        if ($view !== null) {
            StaticPage::render($view);
            return;
        }

        // Koordinatenaufbereitung
        $geo = null;
        try {
            if ($lat !== null && $lon !== null) {
                $geo = new GeoParam($lat, $lon);
            } elseif ($params !== null && $params !== '') {
                $geo = GeoParam::fromGeoHackParams($params);
            }
        } catch (\InvalidArgumentException) {
            FormPage::render();
            return;
        }

        // Exportfunktion
        if ($geo !== null && $output !== null) {
            Exporter::handle($output, $geo->lat, $geo->lon, $title);
            return;
        }

        // Weiterleitungsseite mit Datenschutzhinweis
        if ($geo !== null && $service !== null && $link === 'forward') {
            ForwardPage::render($geo, $service, $title);
            return;
        }

        // Detailansicht
        if ($geo !== null && $service !== null) {
            DetailPage::render($geo, $service, $title);
            return;
        }

        // Indexansicht
        if ($geo !== null) {
            IndexPage::render($geo, $title);
            return;
        }

        // Standard: Formular
        FormPage::render();
    }
}
