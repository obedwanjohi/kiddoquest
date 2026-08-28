#!/bin/bash
set -e

# Ensure SQLite database exists if DB_CONNECTION is sqlite (or not set)
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Ensure storage framework directories and log file exist
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs
touch /var/www/html/storage/logs/laravel.log

# Ensure public storage symlink exists
php artisan storage:link --force || true

# Run all database migrations automatically
php artisan migrate --force || true

# Seed default starter accounts and curriculum if database is fresh
php artisan db:seed --force || true

# Set full www-data permissions AFTER all artisan commands have finished
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache
chmod 666 /var/www/html/storage/logs/laravel.log

# Execute Apache in foreground
exec apache2-foreground
