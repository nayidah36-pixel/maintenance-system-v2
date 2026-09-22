FROM php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && a2enmod rewrite

COPY . /var/www/html/

RUN mkdir -p /var/www/html/assets/uploads \
    && mkdir -p /var/www/html/admin/assets/uploads \
    && chown -R www-data:www-data /var/www/html/assets/uploads \
    && chown -R www-data:www-data /var/www/html/admin/assets/uploads

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && sed -i \"s/:80/:${PORT}/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
