# Use an official PHP image with Apache web server
FROM php:8.2-apache

# Install the mysqli extension so PHP can talk to your database
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Raise PHP's file-upload size limits (see uploads.ini)
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Hide errors from visitors, log them instead (see php-production.ini)
COPY php-production.ini /usr/local/etc/php/conf.d/php-production.ini

# Block private files, folder listings and version banners (see apache-security.conf)
COPY apache-security.conf /etc/apache2/conf-enabled/zz-security.conf

# Don't tell visitors which PHP version runs the site
RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/hide-version.ini

# Copy your archive files into the server (minus everything in .dockerignore)
COPY . /var/www/html/

# The server writes uploaded images into images/, so it has to own that folder
RUN chown -R www-data:www-data /var/www/html/images

# Expose port 80
EXPOSE 80
