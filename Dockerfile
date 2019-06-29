FROM php:5.6-apache

RUN set -x \
    && apt-get update \
    && apt-get install -y libc-client-dev libkrb5-dev \
    && rm -rf /var/lib/apt/list/* \
    && docker-php-ext-configure mysql \
    && docker-php-ext-install mysql \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
    && apt-get purge -y --autoremove libc-client-dev libkrb5-dev

COPY . /var/www/html
