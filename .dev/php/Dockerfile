FROM php:8.2-fpm-bullseye
ARG TIMEZONE

COPY php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

# Обновляем источники пакетов для bullseye
RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    libxml2-dev \
    acl \
    mc \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-freetype

# Устанавливаем расширения PHP, включая XML поддержку
RUN docker-php-ext-install \
    pdo pdo_mysql zip xsl gd intl opcache exif mbstring xml dom

# Проверяем, что XML расширения установлены
RUN php -m | grep -E "(xml|dom|simplexml)" || echo "XML extensions check completed"

# Добавляем репозиторий NodeSource
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && node -v \
    && npm -v
# -----------------------------

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
RUN chmod 777 /app