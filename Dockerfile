FROM php:8.0-apache
WORKDIR /var/www/html
RUN docker-php-ext-install mysqli pdo pdo_mysql
EXPOSE 80