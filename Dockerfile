# Use an official PHP image with Apache web server
FROM php:8.2-apache

# Install the mysqli extension so PHP can talk to your database
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Raise PHP's file-upload size limits (see uploads.ini)
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Hide errors from visitors, log them instead (see php-production.ini)
COPY php-production.ini /usr/local/etc/php/conf.d/php-production.ini

# Copy your archive files into the server
COPY . /var/www/html/

# Expose port 80
EXPOSE 80