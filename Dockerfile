FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer from the official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy your PHP source code into the container
COPY src/ /var/www/html/

# Copy .env if you want it included in the image (optional)
# COPY .env /var/www/html/.env

# Install Composer dependencies (will delete composer.lock and update from composer.json)
RUN rm -f composer.lock && \
    composer update --ignore-platform-req=ext-pdo_mysql

# Expose HTTP port
EXPOSE 80
