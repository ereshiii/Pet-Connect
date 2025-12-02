#!/bin/bash

# Exit on error
set -e

echo "Starting PetConnect deployment..."

# Decode Firebase credentials if base64 encoded
if [ ! -z "$FIREBASE_CREDENTIALS_BASE64" ]; then
    echo "Decoding Firebase credentials from base64..."
    mkdir -p /var/www/html/storage/app
    echo "$FIREBASE_CREDENTIALS_BASE64" | base64 -d > /var/www/html/storage/app/firebase-credentials.json
    echo "Firebase credentials created at /var/www/html/storage/app/firebase-credentials.json"
fi

# Run migrations
php artisan migrate --force

# Create storage symlink for public file access
php artisan storage:link

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
