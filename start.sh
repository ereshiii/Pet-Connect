#!/bin/bash
set -e

echo "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

echo "Installing NPM dependencies..."
npm ci

echo "Building assets..."
npm run build

echo "Creating storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p database
touch database/database.sqlite

echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache database

echo "Running migrations..."
php artisan migrate --force

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting application..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
