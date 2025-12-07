#!/bin/sh
set -e

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor (which runs nginx + php-fpm)
exec "$@"
