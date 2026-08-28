#!/bin/bash
set -e

# Ensure SQLite database exists if DB_CONNECTION is sqlite (or not set)
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Ensure storage framework directories exist
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs

# Ensure storage & database directories have correct permissions
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache

# Run all database migrations automatically
php artisan migrate --force

# Seed default starter accounts and curriculum if database is fresh
php artisan db:seed --force || true

# Execute Apache in foreground
exec apache2-foreground
