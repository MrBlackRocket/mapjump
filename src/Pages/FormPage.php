<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\Layout;
use Geo\Helper;

class FormPage
{
    public static function render(?string $error = null): void
    {
        $errorBox = ($error !== null && $error !== '') ? "<aside class='alert alert-danger'>" . Helper::clean($error) . "</aside>" : "";

        $body = <<<HTML
{$errorBox}
<article>
  <header><h5><i data-lucide="search"></i> Koordinaten eingeben</h5></header>
  <form method="get" action="">
    <div class="grid">
      <label for="lat">Breitengrad (lat)
        <input type="text" name="lat" id="lat" required>
      </label>
      <label for="lon">Längengrad (lon)
        <input type="text" name="lon" id="lon" required>
      </label>
    </div>
    <button type="submit">
      <i data-lucide="circle-arrow-right"></i> Anzeigen
    </button>
  </form>
</article>
HTML;

        Layout::render("Koordinaten eingeben", $body);
    }
}
