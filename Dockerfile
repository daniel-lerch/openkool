FROM php:5.6-apache

RUN set -x \
# Install necessary packages
    && apt-get update \
    && apt-get install -y zip unzip less vim libc-client-dev libkrb5-dev libpng-dev libjpeg-dev \
    && rm -rf /var/lib/apt/list/* \
# Install PHP extensions
    && docker-php-ext-configure mysql \
    && docker-php-ext-install mysql \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
    && docker-php-ext-configure gd --with-jpeg-dir=/usr/include \
    && docker-php-ext-install gd \
# Remove temporary packages
    && apt-get purge -y --autoremove libc-client-dev libkrb5-dev
    # libpng-dev and libjpeg-dev contain shared files and must not be removed

# Install Composer
RUN set -x \
    && cd /var/www/html \
    && curl -L -o composer-setup.php https://getcomposer.org/installer \
    && php composer-setup.php \
    && rm -f composer-setup.php

# Copy all source files to webroot
COPY . /var/www/html

# Install PHP libraries and run setup
RUN set -x \
    && php composer.phar install \
    && bash /var/www/html/install/setup.sh --docker-build
