#!/bin/bash
set -e
git config core.fileMode false
git pull
composer install --optimize-autoloader
php artisan migrate --force
php artisan cubrel:sync-defaults
php artisan optimize
npm ci && npm run build
chmod -R 755 . 
chown -R www-data:www-data .
php artisan queue:restart
php artisan storage:link
echo "Deploy done."