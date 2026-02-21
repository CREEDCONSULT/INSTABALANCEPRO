FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (all in one RUN to avoid conflicts)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    zip \
    curl \
    json \
    bcmath \
    ctype \
    tokenizer && \
    docker-php-ext-enable pdo pdo_mysql mysqli gd zip curl json bcmath ctype tokenizer

# Enable Apache modules
RUN a2enmod rewrite && \
    a2enmod headers && \
    a2enmod ssl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Create logs directory
RUN mkdir -p /var/www/html/logs && \
    chmod 755 /var/www/html/logs

# Create .env file from .env.example if it exists
RUN if [ -f .env.example ]; then cp .env.example .env; else \
    echo "APP_ENV=production" > .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "APP_URL=https://instabalancepro.railway.app" >> .env && \
    echo "DB_HOST=db" >> .env && \
    echo "DB_PORT=3306" >> .env && \
    echo "DB_NAME=instagram_unfollower" >> .env && \
    echo "DB_USER=root" >> .env && \
    echo "DB_PASS=root_password" >> .env && \
    echo "SESSION_LIFETIME=2592000" >> .env; fi

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/logs

# Configure Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/public>|' /etc/apache2/sites-available/000-default.conf

# Disable HTTP/2 to prevent DPI-related issues
RUN a2dismod http2 2>/dev/null || true

# Configure Apache for proper .htaccess handling
RUN cat > /etc/apache2/conf-available/instabalance.conf << 'EOF'
<Directory /var/www/html>
    AllowOverride All
    Require all granted
</Directory>

<Directory /var/www/html/public>
    AllowOverride All
    Require all granted
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php?/$1 [QSA,L]
    </IfModule>
</Directory>

# Set security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
EOF

RUN a2enconf instabalance

# Create PHP configuration for production
RUN cat > /usr/local/etc/php/conf.d/instabalance.ini << 'EOF'
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
session.gc_maxlifetime = 2592000
EOF

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache in foreground
CMD ["apache2-foreground"]
