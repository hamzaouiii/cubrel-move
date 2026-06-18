#!/bin/bash
set -e

git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
npm ci && npm run build
php artisan queue:restart

echo "Deploy done."