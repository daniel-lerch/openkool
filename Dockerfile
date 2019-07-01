FROM php:5.6-apache

RUN set -x \
# Install necessary packages
    && apt-get update \
    && apt-get install -y zip unzip libc-client-dev libkrb5-dev \
    && rm -rf /var/lib/apt/list/* \
# Install PHP extensions
    && docker-php-ext-configure mysql \
    && docker-php-ext-install mysql \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
# Remove temporary packages
    && apt-get purge -y --autoremove libc-client-dev libkrb5-dev

# Install Composer
RUN set -x \
    && cd /var/www/html \
    && curl -L -o composer-setup.php https://getcomposer.org/installer \
    && php composer-setup.php \
    && rm -f composer-setup.php

# Copy all source files to webroot
COPY . /var/www/html

# Install PHP libraries
RUN set -x \
    && php composer.phar install
