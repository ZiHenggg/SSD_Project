FROM php:8.2-apache

# Copy your PHP code into the container
COPY src/ /var/www/html/
EXPOSE 80


# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql