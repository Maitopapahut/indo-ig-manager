FROM php:8.2-apache

# Install curl dependencies
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config && docker-php-ext-install curl

# Set Apache to run on port 8080 instead of 80
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf && \
    sed -i 's/80/8080/g' /etc/apache2/sites-enabled/000-default.conf

# Copy all project files
COPY . /var/www/html/

# Expose 8080
EXPOSE 8080

# Start apache in foreground
CMD ["apache2-foreground"]
