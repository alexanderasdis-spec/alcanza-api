# Imagen de producción de Alcanza API (la usa Render).
# En desarrollo se sigue usando Laravel Sail; este archivo es solo para publicar en internet.
#
# Apache + PHP 8.4 en lugar de "php artisan serve": la prueba de carga mostró que el servidor
# de desarrollo era el límite. Apache atiende varias peticiones a la vez y con OPcache.

FROM php:8.4-apache

# Extensiones que usa Laravel con MySQL (TiDB) y Composer.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip opcache \
    && a2enmod rewrite headers \
    && echo 'ServerName localhost' > /etc/apache2/conf-enabled/servername.conf \
    && rm -rf /var/lib/apt/lists/*

# Apache sirve la carpeta public/ de Laravel.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# OPcache de producción: no revisa si cambian los archivos, porque en producción no cambian.
RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.memory_consumption=128'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Primero solo las dependencias, para que Docker las reutilice si el código cambia.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/produccion/iniciar.sh /usr/local/bin/iniciar
RUN chmod +x /usr/local/bin/iniciar

CMD ["iniciar"]
