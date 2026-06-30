FROM php:8.2-apache
WORKDIR /var/www/html

# Copiar tus carpetas del proyecto
COPY frontend/ /var/www/html/
COPY api/ /var/www/html/api/

# Instalación de extensiones para base de datos
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Otorgar permisos de lectura y ejecución
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80