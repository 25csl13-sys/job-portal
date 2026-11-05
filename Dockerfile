FROM php:8.2-apache-bookworm

RUN docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite headers

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/job-portal-entrypoint
RUN chmod +x /usr/local/bin/job-portal-entrypoint

COPY . /var/www/html

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/job-portal-entrypoint"]
CMD ["apache2-foreground"]
