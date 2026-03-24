<?php

declare(strict_types=1);

namespace Geo\Pages;

use Geo\Auth\TokenAuth;
use Geo\Layout;

class GeocodeLoginPage
{
    public static function render(): void
    {
        // Bereits angemeldet → direkt weiter
        if (TokenAuth::isAuthenticated()) {
            header('Location: ?view=geocode');
            exit;
        }

        $ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $error   = '';
        $blocked = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = isset($_POST['token']) && is_string($_POST['token'])
                ? trim($_POST['token'])
                : '';

            if (TokenAuth::isRateLimited($ip)) {
                $wait    = (int) ceil((TokenAuth::blockedUntil($ip) - time()) / 60);
                $error   = "Zu viele Fehlversuche. Bitte warte noch {$wait} Minute(n).";
                $blocked = true;
            } elseif (TokenAuth::validateToken($token)) {
                TokenAuth::clearAttempts($ip);
                TokenAuth::login();
                header('Location: ?view=geocode');
                exit;
            } else {
                TokenAuth::recordFailure($ip);
                $error = 'Ungültiger Zugangscode.';
            }
        }

        $errorHtml    = $error !== ''
            ? '<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">'
              . htmlspecialchars($error) . '</div>'
            : '';
        $disabledAttr = $blocked ? ' disabled' : '';

        $body = <<<HTML
        <div class="max-w-sm mx-auto">
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-4">
              Diese Funktion ist zugangsbeschränkt. Bitte Zugangscode eingeben.
            </p>
            {$errorHtml}
            <form method="POST" action="?view=geocode-login" autocomplete="off">
              <label class="block text-sm font-medium text-gray-700 mb-1" for="token">
                Zugangscode
              </label>
              <input
                type="password"
                id="token"
                name="token"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm mb-4"{$disabledAttr}
                autofocus
              >
              <button
                type="submit"
                class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"{$disabledAttr}
              >Anmelden</button>
            </form>
          </div>
        </div>
        HTML;

        Layout::render('Geocodierung — Anmeldung', $body);
    }
}
