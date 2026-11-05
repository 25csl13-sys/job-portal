#!/bin/sh
set -e

PORT_VALUE="${PORT:-8080}"
sed -i "s/Listen 8080/Listen ${PORT_VALUE}/" /etc/apache2/sites-available/000-default.conf
sed -i "s/<VirtualHost \\*:8080>/<VirtualHost *:${PORT_VALUE}>/" /etc/apache2/sites-available/000-default.conf

exec docker-php-entrypoint "$@"
