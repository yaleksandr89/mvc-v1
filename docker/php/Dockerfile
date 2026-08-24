FROM php:8.5-fpm-bookworm

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        git \
        libpq-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql \
    && pecl install xdebug-3.5.3 \
    && pecl clear-cache \
    && docker-php-ext-enable xdebug \
    && php -r 'exit(extension_loaded("mbstring") ? 0 : 1);' \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.10.2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/conf.d/*.ini /usr/local/etc/php/conf.d/

RUN groupadd --gid "${APP_GID}" app \
    && useradd --uid "${APP_UID}" --gid app --create-home --shell /bin/bash app \
    && mkdir -p /var/www/html /home/app/.composer \
    && chown app:app /var/www/html /home/app/.composer \
    && sed -ri 's/^[;[:space:]]*user[[:space:]]*=.*/user = app/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -ri 's/^[;[:space:]]*group[[:space:]]*=.*/group = app/' /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

CMD ["php-fpm"]
