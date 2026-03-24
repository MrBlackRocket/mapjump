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
  <form @submit.prevent="submit()" x-data="coordForm" novalidate>
    <div class="overflow-x-auto">
      <table class="text-sm w-full">
        <tbody>
          <tr class="align-middle">
            <td class="pr-4 py-2 font-medium whitespace-nowrap text-gray-700">Breitengrad</td>
            <td class="pr-1 py-1">
              <input x-model="dLat" type="number" min="0" max="90" placeholder="°"
                     class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm text-center">
            </td>
            <td class="pr-1 py-1">
              <input x-model="mLat" type="number" min="0" max="59" placeholder="'"
                     class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm text-center">
            </td>
            <td class="pr-3 py-1">
              <input x-model="sLat" type="number" min="0" max="59.99" step="0.01" placeholder="''"
                     class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm text-center">
            </td>
            <td class="pr-4 py-1">
              <button type="button" @click="toggleLatDir()"
                      class="w-10 py-1.5 border border-gray-300 rounded text-sm font-bold text-amber-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span x-text="dirLat">N</span>
              </button>
            </td>
            <td class="px-3 text-gray-400 text-xs whitespace-nowrap">oder Dezimal</td>
            <td class="py-1">
              <input x-model="decLat" type="text" placeholder="52.516667"
                     class="w-32 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
            </td>
          </tr>
          <tr class="align-middle">
            <td class="pr-4 py-2 font-medium whitespace-nowrap text-gray-700">Längengrad</td>
            <td class="pr-1 py-1">
              <input x-model="dLon" type="number" min="0" max="180" placeholder="°"
                     class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm text-center">
            </td>
            <td class="pr-1 py-1">
              <input x-model="mLon" type="number" min="0" max="59" placeholder="'"
                     class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm text-center">
            </td>
            <td class="pr-3 py-1">
              <input x-model="sLon" type="number" min="0" max="59.99" step="0.01" placeholder="''"
                     class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm text-center">
            </td>
            <td class="pr-4 py-1">
              <button type="button" @click="toggleLonDir()"
                      class="w-10 py-1.5 border border-gray-300 rounded text-sm font-bold text-amber-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span x-text="dirLon">E</span>
              </button>
            </td>
            <td class="px-3 text-gray-400 text-xs whitespace-nowrap">oder Dezimal</td>
            <td class="py-1">
              <input x-model="decLon" type="text" placeholder="13.383333"
                     class="w-32 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p x-show="error !== ''" x-text="error" x-cloak
       class="text-red-600 text-sm mt-3"></p>
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
