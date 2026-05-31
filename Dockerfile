FROM php:8.2-apache

# Habilitar extensiones necesarias
RUN docker-php-ext-install mysqli

# Habilitar cURL (normalmente ya viene incluido)
RUN apt-get update && apt-get install -y curl

# Copiar archivos del proyecto
COPY . /var/www/html/

# Dar permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80