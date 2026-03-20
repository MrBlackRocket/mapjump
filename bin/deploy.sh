#!/bin/bash
# ============================================================
# MapJump – Deployment-Script (auf dem Server ausführen)
# ============================================================
# Verwendung:
#   bash bin/deploy.sh
#
# Beim ersten Mal:
#   git clone <repo-url> /www/htdocs/w017d4f9/sonicscrewdriver.mbr.mobi/mapjump/
#   cd /www/htdocs/w017d4f9/sonicscrewdriver.mbr.mobi/mapjump/
#   wget https://getcomposer.org/composer.phar
#   cp config/.env.example config/.env
#   nano config/.env        # Produktionswerte eintragen
#   bash bin/deploy.sh
#
# KAS-Besonderheiten:
#   - PHP wird als "php83" aufgerufen (kein globales "php")
#   - Composer muss lokal als composer.phar im Projektverzeichnis liegen
#   - Aufruf: php83 composer.phar ...
# ============================================================

set -e  # Abbruch bei Fehler

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(dirname "$SCRIPT_DIR")"
PHP_BIN="${PHP_BIN:-php83}"
COMPOSER="$BASE_DIR/composer.phar"

cd "$BASE_DIR"

echo ""
echo "===================================================="
echo "  MapJump Deployment – $(date '+%Y-%m-%d %H:%M:%S')"
echo "===================================================="

# 1. Neuesten Stand holen
echo ""
echo "[1/7] Git Pull..."
git fetch origin
git reset --hard origin/master

# 2. composer.phar prüfen
echo ""
echo "[2/7] Composer prüfen..."
if [ ! -f "$COMPOSER" ]; then
    echo "  composer.phar nicht gefunden – lade herunter..."
    wget -q https://getcomposer.org/composer.phar -O "$COMPOSER"
    chmod 644 "$COMPOSER"
    echo "  composer.phar heruntergeladen ✓"
else
    echo "  composer.phar vorhanden ✓"
fi

# 3. Composer-Dependencies installieren (ohne Dev-Pakete)
echo ""
echo "[3/7] Composer install (--no-dev)..."
"$PHP_BIN" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction

# 4. Verzeichnisse anlegen + Rechte setzen
echo ""
echo "[4/7] Verzeichnisse prüfen..."
mkdir -p cache/ logs/ data/
chmod 755 cache/ logs/ data/

# 5. .env prüfen
echo ""
echo "[5/7] Konfiguration prüfen..."
if [ ! -f "config/.env" ]; then
    echo "FEHLER: config/.env fehlt!"
    echo "  cp config/.env.example config/.env && nano config/.env"
    exit 1
fi
echo "  config/.env vorhanden ✓"

# 6. GeoIP-Datenbank initialisieren (nur wenn noch nicht vorhanden)
echo ""
echo "[6/7] GeoIP-Datenbank..."
if [ ! -f "data/GeoLite2-Country.mmdb" ]; then
    echo "  Erstmaliger Download..."
    "$PHP_BIN" bin/update-geoip.php
else
    echo "  GeoLite2-Country.mmdb vorhanden ✓"
fi

# 7. Schreibrechte + Verzeichnisschutz
echo ""
echo "[7/7] Dateisystem-Rechte..."
chmod -R 775 cache/ logs/ data/
find src/ -name "*.php" -exec chmod 644 {} \;
find bin/ -name "*.sh" -exec chmod 755 {} \;

# .htaccess-Schutz für gitignorierte Verzeichnisse
echo "Require all denied" > cache/.htaccess
echo "Require all denied" > logs/.htaccess
echo "Require all denied" > vendor/.htaccess

echo ""
echo "===================================================="
echo "  Deployment abgeschlossen!"
echo ""
echo "  Nächste Schritte (einmalig):"
echo "  → Cron-Jobs einrichten:"
echo "    crontab -e"
echo "    → Inhalt aus etc/cron.d/ einfügen"
echo "===================================================="
echo ""
