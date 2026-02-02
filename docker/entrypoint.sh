#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.example .env
fi

mkdir -p storage/logs bootstrap/cache
if [ "$(id -u)" = "0" ]; then
  chown -R www-data:www-data storage bootstrap/cache
  chmod -R ug+rwX storage bootstrap/cache
fi

if ! grep -q '^APP_KEY=.' .env; then
  php artisan key:generate --force
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  for i in 1 2 3 4 5; do
    if php artisan migrate --force; then
      break
    fi
    echo "Migrations failed, retrying in 3s..."
    sleep 3
  done
fi

if [ "${RUN_SEED:-true}" = "true" ]; then
  php artisan db:seed --force || true
fi

if php artisan list | grep -q "passport:keys"; then
  if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
    php artisan passport:keys --force || true
  fi
fi

if php artisan list | grep -q "passport:client"; then
  personal_count=$(php -r 'require __DIR__."/vendor/autoload.php"; $app=require __DIR__."/bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); try { echo (int)\DB::table("oauth_personal_access_clients")->count(); } catch (Throwable $e) { echo 0; }')
  if [ "${personal_count:-0}" = "0" ]; then
    php artisan passport:client --personal --name="Personal Access Client" --no-interaction || true
  fi
fi

if [ "$(id -u)" = "0" ]; then
  chown -R www-data:www-data storage bootstrap/cache
  chmod -R ug+rwX storage bootstrap/cache
fi

if php artisan list | grep -q "l5-swagger:generate"; then
  if [ ! -f storage/api-docs/api-docs.json ]; then
    mkdir -p storage/api-docs
    php artisan l5-swagger:generate || true
  fi
fi

exec apache2-foreground
