FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --prefer-dist

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Configure Apache DocumentRoot
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Add htaccess support in public directory
RUN echo '<Directory /var/www/html/public>' > /etc/apache2/conf-available/instabalance.conf && \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/instabalance.conf && \
    echo '    Require all granted' >> /etc/apache2/conf-available/instabalance.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/instabalance.conf

RUN a2enconf instabalance

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
