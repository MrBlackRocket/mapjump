#!/bin/bash
# ============================================================
# MapJump – Deployment-Script (auf dem Server ausführen)
# ============================================================
# Verwendung:
#   bash bin/deploy.sh
#
# Beim ersten Mal:
#   git clone <repo-url> /www/htdocs/w017d4f9/mapjump/
#   cd /www/htdocs/w017d4f9/mapjump/
#   cp config/.env.example config/.env
#   nano config/.env        # Produktionswerte eintragen
#   bash bin/deploy.sh
# ============================================================

set -e  # Abbruch bei Fehler

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(dirname "$SCRIPT_DIR")"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER="${COMPOSER:-$BASE_DIR/composer.phar}"

cd "$BASE_DIR"

echo ""
echo "===================================================="
echo "  MapJump Deployment – $(date '+%Y-%m-%d %H:%M:%S')"
echo "===================================================="

# 1. Neuesten Stand holen
echo ""
echo "[1/6] Git Pull..."
git pull --ff-only

# 2. Composer-Dependencies aktualisieren (ohne Dev-Pakete)
echo ""
echo "[2/6] Composer install (--no-dev)..."
if command -v composer &>/dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
elif [ -f "$COMPOSER" ]; then
    "$PHP_BIN" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction
else
    echo "WARNUNG: Composer nicht gefunden – Dependencies nicht aktualisiert."
fi

# 3. Verzeichnisse anlegen + Rechte setzen
echo ""
echo "[3/6] Verzeichnisse prüfen..."
mkdir -p cache/ logs/ data/
chmod 755 cache/ logs/ data/

# 4. .env prüfen
echo ""
echo "[4/6] Konfiguration prüfen..."
if [ ! -f "config/.env" ]; then
    echo "FEHLER: config/.env fehlt!"
    echo "  cp config/.env.example config/.env && nano config/.env"
    exit 1
fi
echo "  config/.env vorhanden ✓"

# 5. GeoIP-Datenbank initialisieren (nur wenn noch nicht vorhanden)
echo ""
echo "[5/6] GeoIP-Datenbank..."
if [ ! -f "data/GeoLite2-Country.mmdb" ]; then
    echo "  Erstmaliger Download..."
    "$PHP_BIN" bin/update-geoip.php
else
    echo "  GeoLite2-Country.mmdb vorhanden ✓"
fi

# 6. Schreibrechte für Cache + Logs
echo ""
echo "[6/6] Dateisystem-Rechte..."
chmod -R 775 cache/ logs/ data/
find src/ -name "*.php" -exec chmod 644 {} \;
find bin/ -name "*.sh" -exec chmod 755 {} \;

echo ""
echo "===================================================="
echo "  Deployment abgeschlossen!"
echo ""
echo "  Nächste Schritte (einmalig):"
echo "  → Cron-Jobs einrichten:"
echo "    crontab -e"
echo "    → Inhalt aus etc/cron.d/ einfügen (Pfade anpassen)"
echo "===================================================="
echo ""
