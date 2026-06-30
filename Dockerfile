FROM php:8.0-apache
WORKDIR /var/www/html

# 1. Eliminar configuraciones previas de MPM que causan el conflicto
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    /etc/apache2/mods-enabled/mpm_event.load \
    /etc/apache2/mods-enabled/mpm_worker.conf \
    /etc/apache2/mods-enabled/mpm_worker.load

# 2. Asegurar que prefork esté habilitado
RUN a2enmod mpm_prefork

# 3. Copiar tus archivos
COPY frontend/ /var/www/html/
COPY api/ /var/www/html/api/

# 4. Instalación de extensiones y permisos
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80