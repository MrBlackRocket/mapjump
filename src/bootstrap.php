<?php

declare(strict_types=1);

// Autoloader laden (von ../vendor ausgehend, da wir in src/ liegen)
require_once __DIR__ . '/../vendor/autoload.php';


// .env laden
use Geo\EnvLoader;

EnvLoader::load();

// Logfunktion initialisieren
use Geo\Logger;

Logger::init();

// Zugriffserlaubnis Testen
use Geo\Security;

Security::checkAccess();

// Standard-Zeichensatz und Zeitzone setzen
ini_set('default_charset', 'UTF-8');
$tz = getenv('TIMEZONE');
date_default_timezone_set($tz !== false && $tz !== '' ? $tz : 'Europe/Berlin');
