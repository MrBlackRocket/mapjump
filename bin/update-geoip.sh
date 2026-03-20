#!/bin/bash
# GeoLite2-Country Datenbank aktualisieren
# Wird monatlich per Cron aufgerufen.
#
# Einrichtung (einmalig auf dem Server):
#   chmod +x /path/to/mapjump2/bin/update-geoip.sh
#   crontab -e
#   → Zeile aus mapjump2/etc/cron.d/mapjump-geoip einfügen

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="${PHP_BIN:-php}"
LOG_FILE="${SCRIPT_DIR}/../logs/geoip-update.log"

mkdir -p "$(dirname "$LOG_FILE")"

echo "--- $(date '+%Y-%m-%d %H:%M:%S') GeoIP-Update gestartet ---" >> "$LOG_FILE"
"$PHP_BIN" "$SCRIPT_DIR/update-geoip.php" >> "$LOG_FILE" 2>&1
EXIT_CODE=$?

if [ $EXIT_CODE -ne 0 ]; then
    echo "FEHLER: update-geoip.php beendet mit Code $EXIT_CODE" >> "$LOG_FILE"
fi

echo "--- $(date '+%Y-%m-%d %H:%M:%S') GeoIP-Update beendet (Exit: $EXIT_CODE) ---" >> "$LOG_FILE"
exit $EXIT_CODE
