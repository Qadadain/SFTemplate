FROM php:8.3-fpm-bullseye AS base

ENV APP_ENV prod


RUN apt-get update --assume-yes && apt-get install --no-install-recommends --assume-yes \
    ca-certificates \
    curl \
    libcurl4-openssl-dev \
    libicu-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libwebp-dev \
    nginx \
    uuid-dev \
    zlib1g-dev \
    weasyprint \
    fontconfig \
    libfreetype6 \
    fonts-dejavu \
 && update-ca-certificates \
 && docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-webp \
 && docker-php-ext-install curl gd intl pdo_mysql opcache mbstring \
 && pecl install uuid \
 && docker-php-ext-enable uuid \
 && rm -rf /var/www/html/ \
 && mkdir /var/www/html/ && chown www-data /var/www/html/ \
 && cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini \
 && sed -i 's|listen = 9000|listen = /var/server/php-fpm.sock|' /usr/local/etc/php-fpm.d/zz-docker.conf \
 && echo 'listen.owner = www-data' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
 && echo 'listen.group = www-data' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
 && echo 'listen.mode = 0600' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
 && sed -i 's/expose_php = */expose_php = Off/g' /usr/local/etc/php/php.ini \
 && sed -i '/^user = *\|^group = */d' /usr/local/etc/php-fpm.d/www.conf

COPY ./build/local.ini ${PHP_INI_DIR}/conf.d/999-local.ini
COPY ./build/nginx.conf /etc/nginx/nginx.conf
COPY ./build/gzip.conf /etc/nginx/gzip.conf
COPY ./build/mime.types /etc/nginx/mime.types

########################################################################################################################

FROM base AS composer

WORKDIR /code

RUN apt-get update --assume-yes && apt-get install --no-install-recommends --assume-yes git libzip-dev openssl unzip zip \
 && docker-php-ext-install zip \
 && chown -R www-data:www-data .

USER www-data

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY --chown=www-data:www-data composer.* .

ARG COMPOSER_AUTH

RUN export COMPOSER_AUTH=${COMPOSER_AUTH} && composer install --prefer-dist --no-dev --no-autoloader --no-progress --no-scripts --no-interaction --no-cache

COPY --chown=www-data:www-data src/ ./src/

RUN composer dump-autoload --strict-psr --no-dev --optimize --classmap-authoritative --no-interaction --no-cache

########################################################################################################################

FROM base AS final

ENV APP_ENV=prod

WORKDIR /var/www/html

RUN docker-php-ext-install opcache \
 && echo 'opcache.enable=1' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
 && echo 'opcache.preload=/var/www/html/config/preload.php' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
 && echo 'opcache.preload_user=www-data' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
 && echo 'opcache.memory_consumption=256' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
 && echo 'opcache.max_accelerated_files=20000' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
 && echo 'opcache.validate_timestamps=0' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
 && echo 'realpath_cache_size=4096K' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
 && echo 'realpath_cache_ttl=6000' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini

COPY --chown=www-data:www-data ./ /var/www/html/
COPY --from=composer --chown=www-data:www-data /code/vendor /var/www/html/vendor
COPY --chown=www-data:www-data ./build/entrypoint.sh /entrypoint.sh

RUN mkdir /var/server \
 && touch /var/server/nginx.pid \
 && touch /var/server/php-fpm.sock \
 && chown -R www-data:www-data /etc/nginx/ \
 && chown -R www-data:www-data /var/server \
 && chown -R www-data:www-data /var/www \
 && nginx -t \
 && chmod u+x /entrypoint.sh

USER www-data

RUN bin/console cache:warmup

CMD [ "sh", "/entrypoint.sh" ]

########################################################################################################################

FROM final as dev

USER root

ENV APP_ENV=dev

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN cp ${PHP_INI_DIR}/php.ini-development ${PHP_INI_DIR}/php.ini \
 && sed -i 's|variables_order = "GPCS"|variables_order = "EGPCS"|' ${PHP_INI_DIR}/php.ini \
 && apt-get update --assume-yes \
 && apt-get install --no-install-recommends --assume-yes git gnupg libzip-dev unzip zip \
 && mkdir -p /etc/apt/keyrings \
 && apt-get update --assume-yes \
 && docker-php-ext-install zip \
 && rm -rf /var/lib/apt/lists/* \
 && sed -i 's/opcache.enable=1/opcache.enable=0/' ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
 && sed -i 's/#user www-data;/user www-data;/' /etc/nginx/nginx.conf \
 && sed -i "s/style-src [^;]*/style-src * 'unsafe-inline'/" /etc/nginx/nginx.conf \
 && sed -i "s/script-src [^;]*/script-src * 'unsafe-inline'/" /etc/nginx/nginx.conf \
 && sed -i "s/img-src \([^;]*\)/img-src \1 http:\/\/localhost:*/" /etc/nginx/nginx.conf \
 && sed -i "s/listen 8080 default_server http2;/listen 8080 default_server;/" /etc/nginx/nginx.conf \
 && sed -i "s/listen \[::\]:8080 default_server http2;/listen [::]:8080 default_server;/" /etc/nginx/nginx.conf \
 && sed -i "s/listen 8080 http2;/listen 8080;/" /etc/nginx/nginx.conf \
 && sed -i "s/listen \[::\]:8080 http2;/listen [::]:8080;/" /etc/nginx/nginx.conf \
 && sed -i 's/session.name="__Secure-PHPSESSID"//' ${PHP_INI_DIR}/conf.d/999-local.ini \
 && echo 'user = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
 && echo 'group = www-data' >> /usr/local/etc/php-fpm.d/www.conf

RUN pecl install xdebug-3.3.1 \
 && docker-php-ext-enable xdebug

COPY ./build/xdebug.ini ${PHP_INI_DIR}/conf.d/999-xdebug.ini
