FROM php:8.0-apache
WORKDIR /var/www/html

# Copia todo el contenido de tu proyecto a la raíz de Apache
COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Otorga permisos de lectura y ejecución al servidor
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80