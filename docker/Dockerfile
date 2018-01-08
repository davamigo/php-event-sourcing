# See https://hub.docker.com/_/php/
FROM php:7.1-cli

RUN apt-get update \
 && apt-get -y install \
        zip \
        unzip \
        git \
        curl \
        libcurl4-openssl-dev \
        pkg-config \
        libssl-dev

RUN docker-php-ext-install \
        pdo_mysql \
        bcmath

RUN pecl install xdebug-2.5.0 \
 && docker-php-ext-enable xdebug

RUN pecl install mongodb \
 && docker-php-ext-enable mongodb

RUN useradd -m -u 1000 -U david

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && php -r "unlink('composer-setup.php');"

WORKDIR /var/www/html

