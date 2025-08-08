#!/usr/bin/env bash

echo "Updating mcy-shop programs..."
rsync -avh /opt/mcy-shop-app/ /var/www/html/

docker-php-entrypoint $@
