FROM php:8.2-apache

# Install the necessary PHP extensions
RUN apt-get update && apt-get install -y libpq-dev  \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Enable Apache
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Set Apache to point to my public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/webb/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy the files
COPY webb /var/www/html