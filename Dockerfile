FROM php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && a2enmod rewrite

COPY . /var/www/html/

RUN mkdir -p /var/www/html/assets/uploads \
    && mkdir -p /var/www/html/admin/assets/uploads \
    && chown -R www-data:www-data /var/www/html/assets/uploads \
    && chown -R www-data:www-data /var/www/html/admin/assets/uploads

COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

ENV PORT=8080
EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
