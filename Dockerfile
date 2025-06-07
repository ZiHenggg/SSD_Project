FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www/html

# Configure PHP-FPM to accept all connections
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/.*listen.allowed_clients.*/;listen.allowed_clients = any/' /usr/local/etc/php-fpm.d/www.conf && \
    echo "listen.allowed_clients = 0.0.0.0/0" >> /usr/local/etc/php-fpm.d/www.conf

# Copy application files
COPY ./public /var/www/html/public
COPY ./src /var/www/html/src

# Create directories if they don't exist
RUN mkdir -p /var/www/html/public /var/www/html/src

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create a simple index.php if it doesn't exist
RUN if [ ! -f /var/www/html/public/index.php ]; then \
        echo "<?php\necho '<h1>PHP Application Running!</h1>';\necho '<p>Server Time: ' . date('Y-m-d H:i:s') . '</p>';\nphpinfo();\n?>" > /var/www/html/public/index.php; \
    fi

EXPOSE 9000

CMD ["php-fpm"]