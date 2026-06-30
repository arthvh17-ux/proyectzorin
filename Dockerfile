FROM php:8.0-apache
WORKDIR /var/www/html

# 1. Deshabilitar los MPM en conflicto y habilitar solo mpm_prefork
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# 2. Copiar las carpetas de tu proyecto por separado
COPY frontend/ /var/www/html/
COPY api/ /var/www/html/api/

RUN docker-php-ext-install mysqli pdo pdo_mysql

# 3. Otorgar permisos de lectura y ejecución
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80