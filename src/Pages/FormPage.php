<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\Layout;
use Geo\Helper;

class FormPage
{
    public static function render(?string $error = null): void
    {
        $errorBox = ($error !== null && $error !== '')
            ? "<div class='bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded mb-4 text-sm'>" . Helper::clean($error) . "</div>"
            : "";

        $body = <<<HTML
{$errorBox}
<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
  <h5 class="text-base font-semibold mb-4 flex items-center gap-1.5">
    <i data-lucide="search"></i> Koordinaten eingeben
  </h5>
  <form method="get" action="">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <label class="block">
        <span class="block text-sm font-medium text-gray-700 mb-1">Breitengrad (lat)</span>
        <input type="text" name="lat" id="lat" required
               class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
      </label>
      <label class="block">
        <span class="block text-sm font-medium text-gray-700 mb-1">Längengrad (lon)</span>
        <input type="text" name="lon" id="lon" required
               class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
      </label>
    </div>
    <button type="submit"
            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <i data-lucide="circle-arrow-right"></i> Anzeigen
    </button>
  </form>
</div>
HTML;

        Layout::render("Koordinaten eingeben", $body);
    }
}
