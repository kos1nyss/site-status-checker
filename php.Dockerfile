FROM php:8.1-fpm-alpine

RUN apk update && apk add --no-cache \
    unzip \
    libzip-dev \
    git


RUN docker-php-ext-install pdo_mysql zip sockets

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY composer.json .
RUN composer install

COPY . .

CMD ["sh", "-c", "php ./src/index.php"]
