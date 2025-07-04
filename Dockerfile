FROM php:8.2-apache

# Copy your PHP code into the container
COPY src/ /var/www/html/
EXPOSE 80


# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite and allow .htaccess overrides
RUN a2enmod rewrite

# Update Apache config to allow .htaccess
RUN sed -i '/<Directory \/var\/www\/html\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
