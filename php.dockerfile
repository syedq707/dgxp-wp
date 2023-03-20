FROM php:7.4-fpm-alpine

RUN touch /var/log/error_log

# RUN cd /usr/local/etc/php/conf.d/ && \
#     echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-ram-limit.ini

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

