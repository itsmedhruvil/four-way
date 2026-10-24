#!/bin/sh
# Idempotent WordPress bootstrap for the 4Way Safety headless site.
# Run from the wpcli container:
#   docker compose run --rm wpcli sh /scripts/wp-setup.sh
set -eu

WP_URL="${WP_URL:-http://localhost:8080}"
WP_TITLE="${WP_TITLE:-4Way Safety Solutions LLP}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-4way-admin-password}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-support@4waysafety.com}"
FOURWAY_FRONTEND_URL="${FOURWAY_FRONTEND_URL:-http://localhost:5173}"

WP="wp --path=/var/www/html"

echo "Waiting for the WordPress database…"
attempt=0
until $WP core is-installed >/dev/null 2>&1 || [ "$attempt" -ge 40 ]; do
  attempt=$((attempt + 1))
  sleep 3
done

if ! $WP core is-installed >/dev/null 2>&1; then
  echo "Installing WordPress at $WP_URL…"
  $WP core install \
    --url="$WP_URL" \
    --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --skip-email
else
  echo "WordPress already installed."
fi

$WP theme activate 4way-headless
$WP rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || $WP rewrite structure '/%postname%/'
$WP option update blogdescription "Complete fire protection & engineering systems" >/dev/null

echo "Seeding the 4Way Safety content model…"
$WP 4way seed --import-images --frontend-url="$FOURWAY_FRONTEND_URL"

echo "Setting the React app as the site front page…"
$WP 4way status

echo ""
echo "WordPress admin : $WP_URL/wp-admin  ($WP_ADMIN_USER / $WP_ADMIN_PASSWORD)"
echo "REST payload    : $WP_URL/wp-json/4way/v1/home"
echo "React homepage  : $FOURWAY_FRONTEND_URL"
