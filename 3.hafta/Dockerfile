FROM php:8.1-apache


RUN apt-get update && apt-get install -y libsqlite3-dev && docker-php-ext-install pdo pdo_sqlite


WORKDIR /var/www/html


COPY ./src/ ./


RUN chown -R www-data:www-data /var/www/html
RUN a2enmod rewrite

