#!/bin/bash

sleep 3
php bin/console doctrine:migrations:migrate --no-interaction
apache2-foreground
