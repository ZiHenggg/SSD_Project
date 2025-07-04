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

# Hide Apache version and port info in error pages
RUN echo "ServerTokens Prod\nServerSignature Off" >> /etc/apache2/conf-available/security.conf \
 && ln -s ../conf-available/security.conf /etc/apache2/conf-enabled/security.conf

# Copy application code
COPY src/ /var/www/html/

EXPOSE 9000
