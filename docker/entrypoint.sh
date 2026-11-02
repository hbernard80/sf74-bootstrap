#!/bin/sh
set -eu

if [ "${1:-}" = "apache2-foreground" ]; then
    composer install --prefer-dist --no-interaction --no-progress
    mkdir -p var/cache var/log var/share
    chown -R www-data:www-data var
fi

exec docker-php-entrypoint "$@"
