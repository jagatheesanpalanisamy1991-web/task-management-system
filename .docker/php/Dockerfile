FROM php:8.3-fpm

# Install system dependencies and PHP extensions required for Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip

# Install Redis PHP extension for caching and session management
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer for laravel dependency management
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Setting the working directory
WORKDIR /var/www/html

# Setting proper permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]