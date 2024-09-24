FROM php:8.2-apache AS base
    # Potencjalnie przydatne: libzip-dev zip unzip
    RUN apt-get update -yqq && \
        apt-get install -yqq --no-install-recommends libpq-dev libzip-dev zip unzip git && \ 
        docker-php-ext-install pdo pdo_pgsql && \
        a2enmod rewrite headers remoteip && \
        usermod -u 1000 www-data && \
        chown www-data:www-data /var/www/html

FROM base AS buildable
    RUN curl -o /usr/local/bin/composer https://getcomposer.org/composer.phar && \
        chmod +x /usr/local/bin/composer

FROM buildable AS dev
    RUN pecl install xdebug && \
        docker-php-ext-enable xdebug && \
        curl -sS https://get.symfony.com/cli/installer | bash && \
        mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

FROM buildable AS build-prod
    ENV APP_ENV=prod
    ENV SYMFONY_ENV=prod
    RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
        docker-php-ext-install opcache
    COPY --chown=www-data:www-data . /var/www/html
    USER www-data
    RUN composer install --no-cache --prefer-dist --no-progress --no-dev --no-scripts --optimize-autoloader && \
        php bin/console cache:clear && \
        php bin/console cache:warmup

FROM base AS prod
    ENV APP_ENV=prod
    ENV SYMFONY_ENV=prod
    RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
        docker-php-ext-install opcache
    COPY docker/www/vhost.conf /etc/apache2/sites-enabled/000-default.conf
    COPY docker/www/remoteip.conf /etc/apache2/conf-enabled/
    COPY docker/www/prod/entrypoint.sh /entrypoint.sh
    COPY docker/www/prod/security.conf /etc/apache2/conf-enabled/
    RUN chmod +x /entrypoint.sh
    USER www-data
    COPY --from=build-prod /var/www/html /var/www/html    
    ENTRYPOINT ["/entrypoint.sh"]
