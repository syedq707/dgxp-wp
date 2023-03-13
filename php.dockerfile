FROM php:7.4-fpm-alpine

RUN touch /var/log/error_log

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

