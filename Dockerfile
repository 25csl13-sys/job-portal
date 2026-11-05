FROM php:8.2-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install mysqli pdo_mysql pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite headers

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/job-portal-entrypoint
RUN chmod +x /usr/local/bin/job-portal-entrypoint

COPY . /var/www/html

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/job-portal-entrypoint"]
CMD ["apache2-foreground"]
