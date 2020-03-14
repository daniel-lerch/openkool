FROM php:7.4-apache

RUN set -x \
# Install necessary packages
    && apt-get update \
    && apt-get install -y zip unzip less vim libc-client-dev libkrb5-dev libpng-dev libjpeg-dev \
    && rm -rf /var/lib/apt/list/* \
# Install PHP extensions
    && docker-php-ext-configure mysqli \
    && docker-php-ext-install mysqli \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
    && docker-php-ext-configure gd --with-jpeg-dir=/usr/include \
    && docker-php-ext-install gd \
# Configure environment
    && a2enmod rewrite \
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

LABEL org.label-schema.name="OpenKool" \
      org.label-schema.description="Open source fork of kOOL - Online church organization tool" \
      org.label-schema.vcs-url="https://github.com/daniel-lerch/openkool" \
      org.label-schema.schema-version="1.0"