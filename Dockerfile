FROM php:8.2-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    libcurl4-gnutls-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    zlib1g-dev \
    libonig-dev \
    git \
    unzip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    soap \
    xml \
    curl \
    gd \
    mbstring \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    calendar \
    zip

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY src/composer.json src/composer.lock /var/www/html/

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY src/ /var/www/html/

USER www-data