FROM php:8.0-apache
WORKDIR /var/www/html

# 1. Copiar el contenido de la carpeta frontend en la raíz del servidor
COPY frontend/ /var/www/html/

# 2. Copiar la carpeta api completa (con su propia subcarpeta y archivos) dentro del servidor
COPY api/ /var/www/html/api/

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Otorga permisos de lectura y ejecución a los archivos
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80