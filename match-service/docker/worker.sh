#!/usr/bin/env bash
set -e

until php -r "new PDO('pgsql:host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" >/dev/null 2>&1; do
  echo "Waiting for database..."
  sleep 2
done

php artisan outbox:relay --daemon &
RELAY_PID=$!

php artisan events:consume-team &
CONSUMER_PID=$!

trap 'kill "$RELAY_PID" "$CONSUMER_PID" 2>/dev/null || true' TERM INT

wait -n

kill "$RELAY_PID" "$CONSUMER_PID" 2>/dev/null || true
exit 1
