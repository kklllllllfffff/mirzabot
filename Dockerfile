FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        intl \
        bcmath \
        xml \
        soap \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

COPY . .

RUN find /etc/apache2/mods-enabled -maxdepth 1 \
        \( -name 'mpm_*.load' -o -name 'mpm_*.conf' \) \
        -delete \
    && find /etc/apache2 -type f \
        \( -name '*.conf' -o -name '*.load' \) \
        -exec sed -i \
        -e '/^[[:space:]]*LoadModule[[:space:]]\+mpm_event_module/d' \
        -e '/^[[:space:]]*LoadModule[[:space:]]\+mpm_worker_module/d' \
        -e '/^[[:space:]]*LoadModule[[:space:]]\+mpm_prefork_module/d' \
        {} \; \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.load \
        /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.conf \
        /etc/apache2/mods-enabled/mpm_prefork.conf \
    && a2enmod rewrite \
    && a2enmod headers \
    && apache2ctl configtest

ENV PORT=8080

CMD ["sh", "-c", "find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.load' ! -name 'mpm_prefork.load' -delete && find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.conf' ! -name 'mpm_prefork.conf' -delete && sed -i \"s/Listen 80/Listen ${PORT:-8080}/\" /etc/apache2/ports.conf && sed -i \"s/:80>/:${PORT:-8080}>/g\" /etc/apache2/sites-enabled/000-default.conf && apache2ctl configtest && apache2-foreground"]
