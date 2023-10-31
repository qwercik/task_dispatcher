FROM php:8.2-apache AS common
RUN apt-get update -yqq && apt-get install -yqq --no-install-recommends libpq-dev
RUN docker-php-ext-install pdo_pgsql
RUN curl -o /usr/local/bin/composer https://getcomposer.org/composer.phar && chmod +x /usr/local/bin/composer
COPY 000-default.conf /etc/apache2/sites-available/
RUN a2enmod rewrite headers
RUN usermod -u 1000 www-data
RUN chown www-data /var/www

FROM common AS prod
COPY . .
RUN composer install && php bin/console doctrine:migrations:migrate

FROM common as dev
RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN pecl install xdebug && docker-php-ext-enable xdebug
