FROM php:8.0-apache
WORKDIR /var/www/html

# Copia el contenido de la carpeta frontend a la raíz pública de Apache
COPY frontend/ /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80