<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\Layout;
use Geo\EnvLoader;
use Geo\Helper;

class StaticPage
{
    public static function render(string $view): void
    {
        $allowed = array_map('trim', explode(',', EnvLoader::get('ALLOWED_VIEWS', 'hilfe,impressum,datenschutz,lizenz')));

        $view = strtolower(Helper::clean($view));
        if (!in_array($view, $allowed, true)) {
            Layout::render("Seite nicht gefunden", "<p class='text-danger'>Die Seite wurde nicht gefunden.</p>");
            return;
        }

        $filename = __DIR__ . "/../../static/{$view}.html";
        if (!file_exists($filename)) {
            Layout::render("Seite nicht gefunden", "<p class='text-danger'>Die Seite wurde nicht gefunden.</p>");
            return;
        }

        $content = file_get_contents($filename);
        $title = ucfirst($view);
        Layout::render("MapJump – " . $title, $content);
    }
}
