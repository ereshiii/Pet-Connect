#!/bin/bash

echo "Starting PetConnect deployment..."
echo "PHP Version: $(php -v | head -n 1)"

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
php artisan config:cache || echo "Config cache failed"
php artisan route:cache || echo "Route cache failed"
php artisan view:cache || echo "View cache failed"

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database

echo "Deployment complete!"
echo "Demo User: demo@petconnect.com / password123"

# Configure Apache to listen on Railway's PORT
export APACHE_PORT=${PORT:-80}

# Update ports.conf
echo "Listen $APACHE_PORT" > /etc/apache2/ports.conf

# Update VirtualHost to use the correct port AND ensure DocumentRoot is correct
cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:$APACHE_PORT>
    ServerName petconnect.up.railway.app
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

echo "Apache configured to listen on port: $APACHE_PORT"
cat /etc/apache2/sites-available/000-default.conf

# Execute the main container command
exec "$@"
