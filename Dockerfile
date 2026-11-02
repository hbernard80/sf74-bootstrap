ARG PHP_VERSION=8.5.12
FROM php:${PHP_VERSION}-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libonig-dev libsqlite3-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl mbstring pdo_mysql pdo_sqlite zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/project.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/project-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/project-entrypoint \
    && chmod +x /usr/local/bin/project-entrypoint

WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1
ENTRYPOINT ["project-entrypoint"]
CMD ["apache2-foreground"]
