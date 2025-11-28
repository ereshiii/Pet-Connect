#!/bin/bash

# Exit on error
set -e

echo "Starting PetConnect deployment..."

# Run migrations
php artisan migrate --force

# Seed production data (creates admin, clinics, demo user with pets, appointments, medical records, and reviews)
php artisan db:seed --class=ProductionSeeder --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database

echo "Deployment complete!"
echo "Demo User: demo@petconnect.com / password123"

# Execute the main container command
exec "$@"
