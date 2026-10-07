#!/usr/bin/env bash
set -e
cd /var/www/elpay

echo "== Elpay deploy: $(date -u +%FT%TZ) =="

git pull --ff-only

composer install --no-dev --optimize-autoloader

# Assets de produção (public/build está no .gitignore)
if command -v npm >/dev/null 2>&1; then
  npm ci --ignore-scripts
  npm run build
else
  echo "AVISO: npm indisponível — a saltar build de assets."
fi

php artisan migrate --force
php artisan storage:link || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan queue:restart
sudo chown -R www-data:www-data storage bootstrap/cache

echo "Deploy OK: $(date -u +%FT%TZ)"
