FROM php:8.0-apache
WORKDIR /var/www/html

# Copia absolutamente todo el repositorio (tanto el frontend como tu carpeta api) a la raíz del servidor Apache
COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Otorga permisos de lectura y ejecución
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80