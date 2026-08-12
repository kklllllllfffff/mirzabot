FROM php:8.2-cli

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
    default-mysql-client \
    build-essential \
    autoconf \
    pkg-config \
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
        opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
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

ENV PORT=8080

EXPOSE 8080

# apc.enable_cli=1 و opcache.enable_cli=1 ← کش APCu و OPcache در وب‌سرور داخلی فعال شود
CMD ["sh", "-c", "php -d apc.enable_cli=1 -d apc.ttl=60 -d opcache.enable_cli=1 -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
