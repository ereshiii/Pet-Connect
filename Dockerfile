FROM php:8.2-apache

# Install system dependencies (removed nodejs and npm since build is pre-compiled)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Create storage directories first
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && mkdir -p database \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database

# Install dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Create .env file from example (Railway will override with environment variables)
RUN cp .env.example .env 2>/dev/null || echo "APP_KEY=" > .env

# Generate APP_KEY for build process (will be overridden by Railway env vars)
RUN php artisan key:generate --force || echo "Key generation skipped"

# Note: Assets are pre-built locally and committed to public/build
# No need to run npm install or npm run build on Railway

# Clear any cached config from build
RUN php artisan config:clear || true
RUN php artisan cache:clear || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache database

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure Apache to allow .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Enable error logging
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/error-reporting.ini \
    && echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/error-reporting.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/error-reporting.ini

# Configure Apache to use PORT environment variable from Railway
RUN echo "Listen \${PORT:-80}" > /etc/apache2/ports.conf

# Expose port 80 (Railway will map to their PORT)
EXPOSE 80

# Start script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
