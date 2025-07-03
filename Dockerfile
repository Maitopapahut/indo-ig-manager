FROM php:8.2-apache

# Enable curl
RUN docker-php-ext-install curl

# Copy files to Apache root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port (Koyeb uses 80)
EXPOSE 80
