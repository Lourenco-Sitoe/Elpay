#!/usr/bin/env bash
set -e
cd /var/www/elpay
git pull --ff-only
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
sudo chown -R www-data:www-data storage bootstrap/cache
echo "Deploy OK: $(date -u +%FT%TZ)"
