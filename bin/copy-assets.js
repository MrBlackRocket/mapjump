const fs = require('fs');

fs.mkdirSync('public/js',  { recursive: true });
fs.mkdirSync('public/css', { recursive: true });

fs.copyFileSync('node_modules/@alpinejs/csp/dist/cdn.min.js',   'public/js/alpine.min.js');
fs.copyFileSync('node_modules/leaflet/dist/leaflet.js',          'public/js/leaflet.min.js');
fs.copyFileSync('node_modules/leaflet/dist/leaflet.css',         'public/css/leaflet.css');
fs.copyFileSync('node_modules/lucide/dist/umd/lucide.min.js',    'public/js/lucide.min.js');

console.log('Assets copied to public/');
