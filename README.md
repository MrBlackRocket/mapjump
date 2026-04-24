# MapJump

<p align="center">
  <img src="public/img/logo-banner.png" alt="MapJump" width="480">
</p>

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.5-777BB4)](https://www.php.net/)
[![CI](https://github.com/MrBlackRocket/mapjump/actions/workflows/ci.yml/badge.svg)](https://github.com/MrBlackRocket/mapjump/actions/workflows/ci.yml)

**MapJump** is a modern PHP-based geographic coordinate tool that transforms coordinates into links to various map services. Inspired by [GeoHack](https://bitbucket.org/magnusmanske/geohack/) by Magnus Manske, MapJump provides a clean, privacy-focused interface for accessing multiple mapping platforms from a single coordinate input.

## 📖 About the Project

MapJump accepts geographic coordinates in multiple formats (decimal, degrees/minutes/seconds, GeoHack format) and generates links to popular map services like Google Maps, OpenStreetMap, Bing Maps, and many more. It's built with modern web technologies and follows best practices for security and performance.

**Key Technologies:**
- **Backend:** PHP 8.5+ with PSR-4 autoloading, Composer dependency management
- **Logging:** Monolog for structured logging
- **GeoIP:** GeoIP2 for country-based access control
- **Frontend:** Tailwind CSS for responsive design, Alpine.js for interactivity
- **Maps:** Leaflet.js for embedded map preview
- **Security:** XSS protection, CSP headers, input validation

## 🌟 The Story Behind MapJump

In 2019, after 20 years of running **daenemarkfan.de**, I decided to migrate its content to MediaWiki. During this process, I discovered **GeoHack** – a brilliant tool, but one that came with challenges: it was tightly coupled to Wikipedia's infrastructure and incompatible with my PHP environment.

The solution seemed simple: adapt it. I managed to get it working, but I was never truly satisfied with the result. It felt like a workaround rather than a proper solution.

In 2023, I revisited the project and made another attempt to improve it. Yet again, it still felt like a **temporary fix** rather than something clean and maintainable.

Finally, during the **summer vacation of 2025**, I decided to start fresh. This time with a clear plan: build a **modular, modern system** from the ground up. I completed the implementation, but testing and code quality took a backseat – as they often do.

Fortunately, modern **AI tools** have made it much easier to catch up on those aspects. With their help, I refined the code, added comprehensive tests, and brought MapJump to a point where I can confidently say:

**It's finally done.** ✨

MapJump is now what I always wanted it to be: a clean, maintainable, and modern solution for geographic coordinate linking – free from the constraints of its predecessor and ready to be shared with the world.

## ✨ Features

- **Multiple Coordinate Formats:**
  - Decimal coordinates (e.g., `52.516667, 13.4`)
  - Degrees/Minutes/Seconds (DMS)
  - GeoHack-compatible `params` format

- **Wide Range of Map Services:**
  - Google Maps, OpenStreetMap, Bing Maps
  - Apple Maps, Here Maps, Yandex Maps
  - Specialized services (hiking, aviation, marine charts)

- **Export Formats:**
  - JSON / GeoJSON
  - CSV
  - KML (Google Earth)
  - GPX (GPS devices and outdoor apps)
  - vCard (location-enabled contact)

- **Privacy Features:**
  - Optional forwarding page with privacy notice before opening external services
  - No tracking by default (optional Matomo integration)
  - GeoIP-based country blocking capability

- **User Interface:**
  - Responsive design that works on all devices
  - Interactive Leaflet map preview
  - Clean, accessible interface
  - Dark mode support

- **Geocoding:**
  - Convert addresses to coordinates via Nominatim
  - Access-controlled feature (requires authentication code)

## 🚀 Installation

### Prerequisites

- **PHP 8.5 or higher** with extensions: `mbstring`, `curl`, `json`
- **Composer** for dependency management
- **Node.js & npm** for building Tailwind CSS (development only)
- **Apache** or **Nginx** web server
- **Git** for version control

### Step-by-Step Installation

#### 1. Clone the Repository

```bash
git clone https://github.com/MrBlackRocket/mapjump.git
cd mapjump
```

#### 2. Install PHP Dependencies

```bash
composer install
```

For production environments, use:
```bash
composer install --no-dev --optimize-autoloader
```

#### 3. Install Node.js Dependencies (Development)

```bash
npm install
```

To rebuild Tailwind CSS after making changes:
```bash
npm run build:css
```

#### 4. Create Configuration File

```bash
cp config/.env.example config/.env
```

Edit `config/.env` and adjust the settings for your environment:

```env
# Timezone
TIMEZONE=Europe/Berlin

# Security: Configure allowed referrers (comma-separated)
ALLOW_EMPTY_REFERRER=false
ALLOWED_REFERRERS=https://yourdomain.com

# GeoIP Database Path
GEOIP_DB=data/GeoLite2-Country.mmdb

# Optional: Block specific countries (ISO 3166-1 alpha-2 codes)
BLOCKED_COUNTRIES=

# Logging (set to true in production)
LOGGING_ENABLED=false
LOG_FILE=logs/mapjump.log

# Optional: Matomo Analytics (leave empty to disable)
MATOMO_URL=
MATOMO_SITE_ID=
```

#### 5. Initialize GeoIP Database

MapJump uses MaxMind's free GeoLite2 Country database for country detection:

```bash
php bin/update-geoip.php
```

This downloads the latest GeoLite2-Country database to `data/GeoLite2-Country.mmdb`.

**Note:** MaxMind requires a (free) license key for downloads. Get yours at https://www.maxmind.com/en/geolite2/signup

#### 6. Set Up Directory Permissions

```bash
# Make cache, logs, and data directories writable
chmod 755 cache/ logs/ data/

# Create .htaccess protection for sensitive directories
echo "Require all denied" > cache/.htaccess
echo "Require all denied" > logs/.htaccess
echo "Require all denied" > vendor/.htaccess
```

#### 7. Configure Web Server

##### Apache

The included `.htaccess` file configures URL rewriting and security headers. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Your virtual host should allow `.htaccess` overrides:
```apache
<Directory /var/www/mapjump>
    AllowOverride All
</Directory>
```

##### Nginx

Example configuration:

```nginx
server {
    listen 80;
    server_name mapjump.yourdomain.com;
    root /var/www/mapjump;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }

    location ~ ^/(vendor|logs|cache|config)/.*$ {
        deny all;
    }
}
```

#### 8. Set Up Automatic GeoIP Updates (Optional but Recommended)

Add a cron job to keep the GeoIP database up to date:

```bash
crontab -e
```

Add this line to update weekly:
```cron
0 3 * * 1 /usr/bin/php /var/www/mapjump/bin/update-geoip.php >> /var/www/mapjump/logs/geoip-update.log 2>&1
```

## 📋 Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `CACHE_DIR` | `cache/` | Directory for cached data |
| `CACHE_TTL` | `2592000` | Cache lifetime in seconds (30 days) |
| `TIMEZONE` | `Europe/Berlin` | PHP timezone setting |
| `ALLOWED_VIEWS` | `hilfe,impressum,datenschutz,lizenz` | Static pages accessible via `?view=` |
| `ALLOW_EMPTY_REFERRER` | `false` | Allow requests without referrer |
| `ALLOWED_REFERRERS` | `https://example.com` | Comma-separated allowed referrers |
| `BLOCKED_COUNTRIES` | _(empty)_ | ISO 3166-1 alpha-2 country codes to block |
| `GEOIP_DB` | `data/GeoLite2-Country.mmdb` | Path to GeoIP database |
| `LOGGING_ENABLED` | `false` | Enable request logging |
| `LOG_FILE` | `logs/mapjump.log` | Log file path |
| `MATOMO_URL` | _(empty)_ | Matomo analytics URL (optional) |
| `MATOMO_SITE_ID` | _(empty)_ | Matomo site ID (optional) |

### Analytics Integration

MapJump supports optional Matomo (formerly Piwik) analytics. To enable:

1. Set `MATOMO_URL` and `MATOMO_SITE_ID` in `config/.env`
2. Update `.htaccess` to allow your analytics domain in the CSP headers:

```apache
script-src 'self' 'unsafe-inline' https://your-analytics-domain.com;
img-src 'self' data: https://*.tile.openstreetmap.org https://your-analytics-domain.com;
connect-src 'self' https://nominatim.openstreetmap.org https://your-analytics-domain.com;
```

## 🖥️ Deployment

For production deployment, use the provided deployment script template:

```bash
# Copy the example script
cp bin/deploy.sh.example bin/deploy.sh

# Edit with your server-specific settings
nano bin/deploy.sh

# Run deployment
bash bin/deploy.sh
```

The deployment script handles:
- Git pull from remote repository
- Composer dependency installation (production mode)
- Directory creation and permissions
- GeoIP database initialization
- Security file protection

See `bin/deploy.sh.example` for detailed configuration options.

## 🧪 Testing & Code Quality

MapJump includes a comprehensive suite of development tools:

```bash
# Run PHPUnit tests
composer test

# Check code style (PSR-12)
composer lint

# Fix code style automatically
composer fix

# Run PHPStan analysis
composer stan
```

## 📖 Usage Examples

### Basic Coordinate Input

**Decimal format:**
```
https://your-domain.com/mapjump/?lat=52.516667&lon=13.4&title=Berlin
```

**GeoHack format:**
```
https://your-domain.com/mapjump/?params=52_31_N_13_24_E&title=Berlin
```

### Direct Service Link

Open a specific map service directly:
```
https://your-domain.com/mapjump/?lat=52.52&lon=13.4&service=google
```

### Privacy Forwarding

Add `link=forward` to show a privacy notice before opening external services:
```
https://your-domain.com/mapjump/?lat=52.52&lon=13.4&link=forward
```

### Export Formats

Get coordinates in different formats using `output=`:

```
https://your-domain.com/mapjump/?lat=52.52&lon=13.4&output=json
https://your-domain.com/mapjump/?lat=52.52&lon=13.4&output=geojson
https://your-domain.com/mapjump/?lat=52.52&lon=13.4&output=gpx
https://your-domain.com/mapjump/?lat=52.52&lon=13.4&output=kml
```

## 🛠️ Project Structure

```
mapjump/
├── bin/                    # Command-line scripts
│   ├── deploy.sh.example   # Deployment script template
│   └── update-geoip.php    # GeoIP database updater
├── cache/                  # Cache directory (auto-created)
├── config/                 # Configuration files
│   └── .env.example        # Environment configuration template
├── data/                   # GeoIP database storage
├── logs/                   # Application logs
├── public/                 # Web-accessible assets
│   ├── css/                # Compiled stylesheets
│   ├── img/                # Logo and favicon assets
│   └── js/                 # Frontend libraries
├── src/                    # PHP source code
│   ├── Auth/               # Authentication logic
│   ├── Geo/                # Core coordinate handling
│   ├── GeoIP/              # GeoIP integration
│   ├── Map/                # Map service definitions
│   ├── Nominatim/          # Geocoding (address → coordinates)
│   ├── Pages/              # Page controllers
│   └── bootstrap.php       # Application bootstrap
├── static/                 # Static HTML fragments
├── tests/                  # PHPUnit tests
├── vendor/                 # Composer dependencies
├── .htaccess               # Apache configuration
├── composer.json           # PHP dependencies
├── index.php               # Application entry point
├── LICENSE                 # GPL-3.0 license
└── README.md               # This file
```

## 🤝 Contributing

Contributions are welcome! To contribute:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and add tests
4. Ensure code passes quality checks: `composer lint && composer stan && composer test`
5. Commit your changes: `git commit -m 'Add amazing feature'`
6. Push to your branch: `git push origin feature/amazing-feature`
7. Open a Pull Request

### Code Style

- Follow **PSR-12** coding standard
- Add PHPDoc comments for public methods
- Write tests for new features
- Run `composer fix` before committing to auto-fix style issues

## 📜 License

This project is licensed under the **GNU General Public License v3.0**. See the [LICENSE](LICENSE) file for details.

### Credits & Inspiration

MapJump is inspired by **GeoHack** by Magnus Manske, a geolink generator originally created for Wikimedia projects. While MapJump shares the same core concept, it is built from scratch with a modern PHP architecture, updated dependencies, and enhanced features.

- **GeoHack Repository:** https://bitbucket.org/magnusmanske/geohack/
- **Original License:** GPL v2+

## 🙏 Acknowledgments

- **Magnus Manske** for the original GeoHack concept
- **MaxMind** for providing the free GeoLite2 database
- **OpenStreetMap** contributors for mapping data and Nominatim geocoding
- All the map service providers integrated into MapJump

## 📞 Support

- **Issues:** Report bugs or request features via [GitHub Issues](https://github.com/MrBlackRocket/mapjump/issues)
- **Discussions:** Join the conversation in [GitHub Discussions](https://github.com/MrBlackRocket/mapjump/discussions)

---

**Made with ❤️ for the geospatial community**
