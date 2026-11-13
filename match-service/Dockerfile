FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql zip pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY packages/contracts /var/www/packages/contracts
COPY packages/event-sourcing /var/www/packages/event-sourcing
COPY packages/observability /var/www/packages/observability

COPY match-service/composer.json match-service/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY match-service/ .
RUN composer dump-autoload --optimize

COPY match-service/docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY match-service/docker/worker.sh /usr/local/bin/worker.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/worker.sh

EXPOSE 8002

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8002"]
