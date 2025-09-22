# syntax=docker/dockerfile:1.7
FROM php:8.3-cli-alpine

# Install tools and build deps for PECL extensions
RUN apk add --no-cache git bash unzip $PHPIZE_DEPS \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apk del --no-cache $PHPIZE_DEPS

# Enable PCOV for CLI and scope it to only the source directory
RUN printf "pcov.enabled=1\npcov.directory=/app/src\n" > /usr/local/etc/php/conf.d/pcov.ini

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_ROOT_VERSION=1.0.0
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

WORKDIR /app
COPY . /app
RUN composer install --no-interaction --prefer-dist --no-progress
CMD ["bash"]
