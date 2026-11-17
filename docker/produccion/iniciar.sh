#!/bin/sh
# Arranque del contenedor de producción (Render).
set -e

# Render indica en $PORT el puerto donde debe escuchar la aplicación.
PUERTO="${PORT:-8080}"
sed -i "s/^Listen .*/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

# Clave de la aplicación: si el hosting no la da, se arma a partir de APP_SEMILLA, un texto al azar
# que Render genera una vez y guarda. Así la clave es siempre la misma y nunca se sube a Git.
if [ -z "$APP_KEY" ] && [ -n "$APP_SEMILLA" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(hash("sha256", getenv("APP_SEMILLA"), true));')"
    export APP_KEY
fi

# Configuración, rutas y eventos en caché: Laravel no los recalcula en cada petición.
php artisan optimize

# Crea o actualiza las tablas. --force porque en producción Laravel pide confirmación.
php artisan migrate --force

exec apache2-foreground
