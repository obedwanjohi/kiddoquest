#!/bin/bash
set -e

# Ensure SQLite database exists if DB_CONNECTION is sqlite (or not set)
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Ensure storage & database directories have correct permissions
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache

# Run database migrations automatically
php artisan migrate --force --graceful || true

# Execute Apache in foreground
exec apache2-foreground
