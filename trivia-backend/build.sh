#!/bin/bash

# 1. Install production dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# 2. Generate application key if missing
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# 3. Database migrations
php artisan migrate --force

# 4. Clear all caches (safely)
php artisan cache:clear
php artisan view:clear

# 5. Rebuild optimized cache
php artisan config:cache
php artisan route:cache
php artisan event:cache
