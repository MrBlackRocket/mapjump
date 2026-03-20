<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\Layout;
use Geo\Helper;

class FormPage
{
    public static function render(?string $error = null): void
    {
        $errorBox = ($error !== null && $error !== '') ? "<div class='alert alert-danger'>" . Helper::clean($error) . "</div>" : "";

        $body = <<<HTML
{$errorBox}
<div class="card">
  <div class="card-body">
    <h5 class="card-title"><i class="bi bi-search me-1"></i> Koordinaten eingeben</h5>
    <form method="get" action="">
      <div class="row g-3">
        <div class="col-md-6">
          <label for="lat" class="form-label">Breitengrad (lat)</label>
          <input type="text" name="lat" id="lat" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label for="lon" class="form-label">Längengrad (lon)</label>
          <input type="text" name="lon" id="lon" class="form-control" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-3">
        <i class="bi bi-arrow-right-circle me-1"></i> Anzeigen
      </button>
    </form>
  </div>
</div>
HTML;

        Layout::render("Koordinaten eingeben", $body);
    }
}
