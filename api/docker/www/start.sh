#!/bin/bash

php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear
php bin/console cache:warmup
chown -R www-data:www-data .
chmod -R 755 .

apache2ctl -D FOREGROUND
tail -f /dev/null
