#!/bin/bash

# Exit immediately if any command fails
set -e

# Run migrations and optimizations
php artisan migrate --force
php artisan optimize:clear
php artisan optimize

# Start Apache in the foreground
exec apache2-foreground
