FROM php:8.2-apache

# Install system dependencies first
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    unzip \
    git \
    curl

# Now enable PHP curl extension
RUN docker-php-ext-install curl

# Copy your PHP files to Apache root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port
EXPOSE 80
