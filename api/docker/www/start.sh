#!/bin/bash

php bin/console doctrine:migrations:migrate --no-interaction
apache2ctl -D FOREGROUND
tail -f /dev/null
