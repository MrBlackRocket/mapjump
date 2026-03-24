/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./src/**/*.php', './static/**/*.html', './index.php'],
  theme: { extend: {} },
  plugins: [require('@tailwindcss/forms')]
}
