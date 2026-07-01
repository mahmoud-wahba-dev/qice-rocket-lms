#!/bin/bash
# QIEC — First-time Hostinger server setup
# Run once via SSH after connecting: bash scripts/hostinger-first-setup.sh
#
# Prerequisites:
# - SSH access to Hostinger (see _docs/DEPLOYMENT.md)
# - GitHub deploy key added on server (for git pull)
# - MySQL database created in hPanel

set -e

DOMAIN_PATH="domains/training.qiec.sa/public_html"
REPO_URL="git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git"

echo "=== QIEC first-time server setup ==="
cd ~/"$DOMAIN_PATH"

if [ ! -d ".git" ]; then
    echo "Cloning repository..."
    git clone "$REPO_URL" .
else
    echo "Git repo exists. Pulling latest..."
    git pull origin master
fi

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

if [ -d vendor/stijnvanouplines/blade-country-flags/resources/svg ]; then
    mkdir -p public/vendor/blade-country-flags
    cp -r vendor/stijnvanouplines/blade-country-flags/resources/svg/* public/vendor/blade-country-flags/
    echo "Synced blade-country-flags SVG assets"
fi

if [ ! -f ".env" ]; then
    echo "WARNING: .env not found. Copy from .env.example and configure DB credentials."
    if [ -f ".env.example" ]; then
        cp .env.example .env
        php artisan key:generate
    fi
else
    echo ".env exists — skipping key generation."
fi

echo "Linking storage..."
php artisan storage:link 2>/dev/null || true

echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "=== Manual steps still required ==="
echo "1. Edit .env with production DB credentials (hPanel -> Databases)"
echo "2. Set APP_ENV=production, APP_DEBUG=false, APP_URL=https://training.qiec.sa"
echo "3. Upload public/assets/design_1/ via File Manager (not in git)"
echo "4. Upload public/assets/landing_v1/img/ via File Manager (not in git)"
echo "5. Run: php artisan migrate --force  (if schema updates needed)"
echo "6. Remove X-Robots-Tag noindex from .htaccess before public launch"
echo ""
echo "=== Setup script finished ==="
