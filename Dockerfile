ARG PHP_VERSION=8.0

FROM php:${PHP_VERSION}-fpm-alpine AS dev

RUN apk add --no-cache --virtual .persistent-deps \
  openssl-dev \
  libzip-dev \
  zlib-dev \
  freetype-dev \
  libpng-dev \
  libjpeg-turbo-dev \
  nginx \
  supervisor \
  mysql-client \
  shadow \
  linux-headers

RUN apk add --no-cache --virtual .build-deps \
  oniguruma-dev \
  autoconf \
  g++ \
  make

# Install PHP extensions
RUN set -xe \
  && pecl install xdebug-3.2.0 \
  && pecl install redis \
  && docker-php-ext-enable xdebug \
  && docker-php-ext-enable redis \
  && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
  && docker-php-ext-install -j$(nproc) \
    opcache \
    mbstring \
    gd \
    pdo_mysql \
    exif \
    sockets \
    zip \
  && apk del .build-deps \
  && rm -rf /tmp/* /var/cache/apk/*

COPY cfg/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_NO_INTERACTION 1
ENV COMPOSER_HTACCESS_PROTECT 0
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN cp -f "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

ENTRYPOINT ["/usr/bin/supervisord", "-c", "/app/cfg/supervisord.conf"]

FROM dev AS prod

WORKDIR /app

RUN cp -f "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini


COPY app /app/app
COPY bootstrap /app/bootstrap
COPY cfg /app/cfg
COPY config /app/config
COPY database /app/database
COPY public /app/public
COPY resources /app/resources
COPY routes /app/routes
COPY storage /app/storage
COPY artisan /app/
COPY composer.json /app/

RUN composer install
RUN chown -R www-data:www-data /app
