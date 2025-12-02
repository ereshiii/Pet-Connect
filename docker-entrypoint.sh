#!/bin/bash

echo "Starting PetConnect deployment..."
echo "PHP Version: $(php -v | head -n 1)"

# Check if APP_KEY is set, if not generate one
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:placeholder" ]; then
    echo "WARNING: APP_KEY not set or is placeholder, generating new key..."
    php artisan key:generate --force --show > /tmp/appkey.txt
    export APP_KEY=$(cat /tmp/appkey.txt)
    echo "Generated APP_KEY: $APP_KEY"
fi

# Decode Firebase credentials if base64 encoded
if [ ! -z "$FIREBASE_CREDENTIALS_BASE64" ]; then
    echo "Decoding Firebase credentials from base64..."
    mkdir -p /var/www/html/storage/app
    echo "$FIREBASE_CREDENTIALS_BASE64" | base64 -d > /var/www/html/storage/app/firebase-credentials.json
    echo "Firebase credentials created"
fi

# Ensure database file exists
echo "Checking database file..."
touch /var/www/html/database/database.sqlite
chmod 664 /var/www/html/database/database.sqlite
chown www-data:www-data /var/www/html/database/database.sqlite

# Run migrations
echo "Running migrations..."
php artisan migrate --force || {
    echo "Migration failed, but continuing..."
}

# Create storage symlink for public file access (ignore error if exists)
echo "Creating storage symlink..."
php artisan storage:link 2>/dev/null || echo "Storage link already exists"

# Seed production data (creates admin, clinics, demo user with pets, appointments, medical records, and reviews)
echo "Seeding production data..."
php artisan db:seed --class=ProductionSeeder --force || {
    echo "Seeding failed, but continuing..."
}

# Clear and cache config
echo "Caching configuration..."
php artisan config:clear || echo "Config clear failed"
php artisan config:cache || echo "Config cache failed"
php artisan route:cache || echo "Route cache failed"
php artisan view:cache || echo "View cache failed"

# Test if Laravel is working
echo "Testing Laravel..."
php artisan --version || echo "Laravel check failed"

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database

echo "Deployment complete!"
echo "Demo User: demo@petconnect.com / password123"

# Configure Apache to listen on Railway's PORT (defaults to 80 if not set)
export APACHE_PORT=${PORT:-80}
echo "Listen $APACHE_PORT" > /etc/apache2/ports.conf
echo "Apache will listen on port: $APACHE_PORT"

# Execute the main container command
exec "$@"
