# Usar una imagen oficial de PHP con Apache
FROM php:8.1-apache

# Habilitar mod_rewrite de Apache (crucial para URLs amigables si las usas)
RUN a2enmod rewrite

# Instalar extensiones de PHP necesarias para bases de datos
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copiar todos los archivos de tu proyecto al directorio de Apache en el contenedor
COPY . /var/www/html/

# Dar permisos al servidor sobre los archivos
RUN chown -R www-data:www-data /var/www/html/

# Exponer el puerto 80 (el estándar web)
EXPOSE 80