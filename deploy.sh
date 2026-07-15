#!/bin/bash
set -e
git config core.fileMode false
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
npm ci && npm run build
chmod -R 755 . 
chown -R www-data:www-data .
php artisan queue:restart
php artisan storage:link
echo "Deploy done."