# Deploy Elpay em VPS (Ubuntu 24.04) — produção

## 1. Servidor (ex.: Hetzner CX22 / Contabo, Ubuntu 24.04)

```bash
sudo apt update && sudo apt install -y nginx mysql-server php8.3-fpm \
  php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-gd \
  php8.3-bcmath php8.3-intl unzip git supervisor certbot python3-certbot-nginx
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

## 2. DNS

Apontar `app.tudominio.com` (ou o domínio escolhido) com registo **A** para o IP do VPS.
Recomendado: gerir o DNS na Cloudflare (SSL gratuito + proxy).

## 3. Código

```bash
sudo mkdir -p /var/www && sudo chown $USER:$USER /var/www
cd /var/www && git clone <URL-DO-REPO> elpay && cd elpay
cp .env.production.example .env
nano .env   # APP_URL, DB_*, MAIL_*, MPESA_*/EMOLA_* quando houver
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class="Database\Seeders\ProductionSeeder" --force   # só na 1ª vez (admin + settings, sem demo)
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
npm ci --ignore-scripts && npm run build   # ou corre ./deploy/deploy.sh que já inclui o build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 4. Nginx — copiar `deploy/nginx-elpay.conf` para `/etc/nginx/sites-available/elpay`,
substituir `app.tudominio.com` e o root, ativar e recarregar:

```bash
sudo ln -s /etc/nginx/sites-available/elpay /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

## 5. HTTPS (Let's Encrypt; dispensável se a Cloudflare já termina SSL)

```bash
sudo certbot --nginx -d app.tudominio.com
```

## 6. Worker da fila (webhooks + emails) — copiar `deploy/supervisor-elpay-worker.conf`
para `/etc/supervisor/conf.d/`, ajustar `user`/`directory` e:

```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start elpay-worker:*
```

## 7. Scheduler (se um dia houver tarefas agendadas)

```bash
* * * * * cd /var/www/elpay && php artisan schedule:run >> /dev/null 2>&1
```

## 8. Atualizações

```bash
cd /var/www/elpay && ./deploy/deploy.sh
```

## 9. App mobile

Em `elpay-mobile/www/js/app.js`, trocar o `API_BASE` por defeito para
`https://app.tudominio.com/api`, correr `npx cap sync android` e gerar
**release assinada**:

```bash
keytool -genkeypair -alias elpay -keyalg RSA -keysize 2048 -validity 10000 -keystore elpay.keystore
# configurar signingConfigs.release em android/app/build.gradle e:
cd android && ./gradlew assembleRelease
# APK: android/app/build/outputs/apk/release/app-release.apk
```
