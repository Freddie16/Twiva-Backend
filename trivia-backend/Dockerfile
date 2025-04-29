FROM php:8.2-apache-bookworm

WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/local/bin/composer

# Copy only composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install dependencies without running scripts

RUN composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

# Copy the rest of the application files
COPY . .

# Create user and set permissions
RUN useradd -G www-data,root -u 1000 -d /home/user user && \
    mkdir -p /home/user/.composer && \
    chown -R user:user /home/user && \
    chown -R user:user /var/www/html

USER user

# Run post-install scripts after all files are copied
RUN composer run-script post-autoload-dump

USER root

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Apache configuration
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    a2enmod rewrite
# Copy the entrypoint script into the container
COPY entrypoint.sh /usr/local/bin/

# Make the script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set the entrypoint
ENTRYPOINT ["entrypoint.sh"]
