# FROM php:8.2-apache

# # Copy your PHP code into the container
# COPY src/ /var/www/html/
# EXPOSE 80


# # Install PDO MySQL extension
# RUN docker-php-ext-install pdo pdo_mysql

FROM php:8.2-apache

# Change Apache listen port from 80 to 9000
RUN sed -i 's/80/9000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy application code
COPY src/ /var/www/html/

EXPOSE 9000
