<?php

declare(strict_types=1);

namespace Geo;

class MapSource
{
    /**
     * @return array<string, array<string, array{alias: string, url: string}>>
     * @psalm-suppress PossiblyUnusedParam
     */
    public static function getSources(float $lat, float $lon): array
    {
        return [
            'Navigation' => [
                'Google Maps'  => ['alias' => 'google',      'url' => 'https://www.google.com/maps?q=@LAT@,@LON@&ll=@LAT@,@LON@&z=15'],
                'Bing Maps'    => ['alias' => 'bing',        'url' => 'https://www.bing.com/maps?cp=@LAT@~@LON@&lvl=15'],
                'Apple Maps'   => ['alias' => 'apple',       'url' => 'https://maps.apple.com/?ll=@LAT@,@LON@&t=m'],
                'HERE WeGo'    => ['alias' => 'here',        'url' => 'https://wego.here.com/?map=@LAT@,@LON@,15,normal'],
                'Waze'         => ['alias' => 'waze',        'url' => 'https://www.waze.com/livemap/?zoom=15&lat=@LAT@&lon=@LON@'],
                'TomTom'       => ['alias' => 'tomtom',      'url' => 'https://plan.tomtom.com/en?p=@LAT@,@LON@'],
                'MapQuest'     => ['alias' => 'mapquest',    'url' => 'https://www.mapquest.com/latlng/@LAT@,@LON@'],
                'ViaMichelin'  => ['alias' => 'viamichelin', 'url' => 'https://www.viamichelin.com/web/Maps?lat=@LAT@&lon=@LON@&zoom=12'],
                'Yandex Maps'  => ['alias' => 'yandex',      'url' => 'https://maps.yandex.com/?ll=@LON@,@LAT@&spn=0.3,0.3&l=map&pt=@LON@,@LAT@'],
                'Mapy.com'     => ['alias' => 'mapy',        'url' => 'https://mapy.com/en/zakladni?x=@LON@&y=@LAT@&z=15&source=coor&id=@LON@,@LAT@'],
            ],
            'Luftbilder & Satellit' => [
                'Google Satellite'        => ['alias' => 'googlesat',  'url' => 'https://www.google.com/maps/@LAT@,@LON@,15z/data=!3m1!1e3'],
                'Bing Aerial'             => ['alias' => 'bingaerial', 'url' => 'https://www.bing.com/maps?cp=@LAT@~@LON@&lvl=15&style=a'],
                'Esri World Imagery'      => ['alias' => 'esri',       'url' => 'https://www.arcgis.com/home/webmap/viewer.html?center=@LON@,@LAT@&level=15'],
                'Zoom Earth'              => ['alias' => 'zoomearth',  'url' => 'https://zoom.earth/#view=@LAT@,@LON@,15z'],
                'Sentinel-2 (EO Browser)' => ['alias' => 'sentinel',   'url' => 'https://apps.sentinel-hub.com/eo-browser/?zoom=15&lat=@LAT@&lng=@LON@&themeId=DEFAULT-THEME'],
                'Copernicus Browser'      => ['alias' => 'copernicus', 'url' => 'https://browser.dataspace.copernicus.eu/?zoom=15&lat=@LAT@&lng=@LON@&themeId=DEFAULT-THEME'],
                'OpenStreetMap'           => ['alias' => 'osm',        'url' => 'https://www.openstreetmap.org/#map=15/@LAT@/@LON@'],
                'Wikimapia'               => ['alias' => 'wikimapia',  'url' => 'https://wikimapia.org/#lang=de&lat=@LAT@&lon=@LON@&z=15'],
            ],
            'Topografie' => [
                'OpenTopoMap'       => ['alias' => 'opentopo',  'url' => 'https://opentopomap.org/#map=15/@LAT@/@LON@'],
                'OpenHistoricalMap' => ['alias' => 'ohm',       'url' => 'https://www.openhistoricalmap.org/?mlat=@LAT@&mlon=@LON@&zoom=15&layers=O'],
                'Old Maps Online'   => ['alias' => 'oldmaps',   'url' => 'https://www.oldmapsonline.org/?lat=@LAT@&lon=@LON@'],
                'TopoView (USGS)'   => ['alias' => 'topoview',  'url' => 'https://ngmdb.usgs.gov/topoview/viewer/#13/@LAT@/@LON@'],
            ],
            'Outdoor & Sport' => [
                'OpenCycleMap'     => ['alias' => 'cyclemap',   'url' => 'https://www.opencyclemap.org/?zoom=15&lat=@LAT@&lon=@LON@'],
                'Waymarked Trails' => ['alias' => 'trails',     'url' => 'https://hiking.waymarkedtrails.org/#?map=15!@LAT@!@LON@'],
                'PeakFinder'       => ['alias' => 'peakfinder', 'url' => 'https://www.peakfinder.org/?lat=@LAT@&lng=@LON@'],
                'Strava Heatmap'   => ['alias' => 'strava',     'url' => 'https://labs.strava.com/heatmap/#14/@LON@/@LAT@/hot/all'],
                'CalTopo'          => ['alias' => 'caltopo',    'url' => 'https://caltopo.com/map.html#ll=@LAT@,@LON@&z=15&b=t'],
            ],
            'Wetter & Umwelt' => [
                'Windy.com'     => ['alias' => 'windy',   'url' => 'https://www.windy.com/@LAT@/@LON@/15'],
                'NASA FIRMS'    => ['alias' => 'firms',   'url' => 'https://firms.modaps.eosdis.nasa.gov/map/#d:today;@LON@,@LAT@,14z'],
                'Heavens-Above' => ['alias' => 'heavens', 'url' => 'https://www.heavens-above.com/?Loc=&Lat=@LAT@&Lng=@LON@&Alt=0&tz=CET'],
            ],
            'Schifffahrt & Luftfahrt' => [
                'OpenSeaMap'    => ['alias' => 'openseamap',    'url' => 'https://map.openseamap.org/map/?zoom=13&mlat=@LAT@&mlon=@LON@'],
                'MarineTraffic' => ['alias' => 'marinetraffic', 'url' => 'https://www.marinetraffic.com/en/ais/home/centerx:@LON@/centery:@LAT@/zoom:13'],
                'Flightradar24' => ['alias' => 'flightradar',   'url' => 'https://www.flightradar24.com/@LAT@,@LON@/13'],
                'SkyVector'     => ['alias' => 'skyvector',     'url' => 'https://skyvector.com/?ll=@LAT@,@LON@&zoom=2'],
            ],
            'Wikipedia & Fotos' => [
                'Wikipedia Nearby' => ['alias' => 'wpnearby',  'url' => 'https://en.wikipedia.org/wiki/Special:Nearby#/coord/@LAT@,@LON@'],
                'Commons WikiMap'  => ['alias' => 'wikimap',   'url' => 'https://wikimap.toolforge.org/?lat=@LAT@&lon=@LON@&zoom=13'],
                'WikiShootMe'      => ['alias' => 'wikishoot', 'url' => 'https://wikishootme.toolforge.org/#lat=@LAT@&lng=@LON@'],
                'Mapillary'        => ['alias' => 'mapillary', 'url' => 'https://www.mapillary.com/app/?lat=@LAT@&lng=@LON@&z=15'],
                'Flickr'           => ['alias' => 'flickr',    'url' => 'https://www.flickr.com/map/?fLat=@LAT@&fLon=@LON@&zl=14'],
            ],
            'DACH' => [
                'OSM Deutschland' => ['alias' => 'osmde',    'url' => 'https://www.openstreetmap.de/#map=15/@LAT@/@LON@'],
                'Geoportal.de'    => ['alias' => 'geoportal','url' => 'https://wikitools.toolforge.org/geolink.php?config=geoportalde&lat=@LAT@&lon=@LON@&scale=300000'],
                'BEV (Österreich)' => ['alias' => 'bev',      'url' => 'https://maps.bev.gv.at/#/center/@LON@,@LAT@/zoom/15'],
            ],
            'Open Data / Spezial' => [
                'OpenRailwayMap' => ['alias' => 'rail',      'url' => 'https://www.openrailwaymap.org/?lat=@LAT@&lon=@LON@&zoom=14&style=standard'],
                'OpenInfraMap'   => ['alias' => 'infra',     'url' => 'https://openinframap.org/#13/@LAT@/@LON@'],
                'WaterwayMap'    => ['alias' => 'waterway',  'url' => 'https://waterwaymap.org/#map=13/@LAT@/@LON@'],
                'Geocaching.com' => ['alias' => 'geocaching','url' => 'https://www.geocaching.com/map/default.aspx?lat=@LAT@&lng=@LON@'],
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function renderLinks(float $lat, float $lon): array
    {
        $result = [];
        $sources = self::getSources($lat, $lon);

        foreach ($sources as $group => $entries) {
            $links = [];
            foreach ($entries as $name => $data) {
                $url = str_replace(['@LAT@', '@LON@'], [strval($lat), strval($lon)], $data['url']);
                $links[$name] = $url;
            }
            $result[$group] = $links;
        }

        return $result;
    }

    public static function getLinkForService(string $alias, float $lat, float $lon): ?string
    {
        $sources = self::getSources($lat, $lon);

        foreach ($sources as $group) {
            foreach ($group as $entry) {
                if (strtolower($entry['alias']) === strtolower($alias)) {
                    return str_replace(['@LAT@', '@LON@'], [strval($lat), strval($lon)], $entry['url']);
                }
            }
        }

        return null;
    }
    /**
     * @return array<string, string>
     */
    public static function flatLinks(float $lat, float $lon): array
    {
        $out = [];
        foreach (self::getSources($lat, $lon) as $links) {
            foreach ($links as $name => $data) {
                $out[$name] = str_replace(['@LAT@', '@LON@'], [strval($lat), strval($lon)], $data['url']);
            }
        }
        return $out;
    }
}
