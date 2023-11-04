#!/bin/bash

php bin/console doctrine:migrations:migrate --no-interaction
tail -f /dev/null
