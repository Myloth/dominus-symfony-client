FROM php:8.4-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip gnupg iputils-ping curl \
    && docker-php-ext-install intl opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN git config --global user.email "asensijoel@gmail.com" \
    && git config --global user.name "Myloth" \
    && git config --global --add safe.directory /var/www/dominus-admin

RUN mkdir - p /var/www/.composer && chmod a+rwx /var/www/.composer

RUN usermod -u 1000 www-data

WORKDIR /var/www/dominus-admin
RUN chown -R www-data:www-data ./

USER www-data

EXPOSE 9000
