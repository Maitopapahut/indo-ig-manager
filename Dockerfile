FROM php:8.2-apache

# Required PHP extensions (add more if needed)
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config && docker-php-ext-install curl

# Copy all project files to web root
COPY . /var/www/html/

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]
