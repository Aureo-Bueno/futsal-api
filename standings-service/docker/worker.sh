#!/usr/bin/env bash
set -e

until php -r "new PDO('pgsql:host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" >/dev/null 2>&1; do
  echo "Waiting for database..."
  sleep 2
done

exec php artisan events:consume-match
