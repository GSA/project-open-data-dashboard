FROM php:7.1-apache

ARG APP_DIR=/var/www
RUN sed -i 's/html//g' /etc/apache2/sites-available/000-default.conf

RUN apt-get update && apt-get install -y \
  git unzip wget curl \
    jq mysql-client &&\
    apt-get autoremove -y &&\
    rm -rf /var/lib/apt/lists/* &&\
    rm -rf /var/cache/apt/*
#  libbz2-dev libcurl4-openssl-dev libmcrypt-dev \
#      libwebp-dev libjpeg-dev libpng-dev \

# Need mod_rewrite
RUN a2enmod rewrite 

# Install php extensions
# RUN docker-php-ext-install mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql 

# Install composer
COPY docker/install-composer.sh /tmp/install-composer.sh
RUN /tmp/install-composer.sh

# Add composer-installed libs to path
ENV PATH=/var/www/vendor/bin:$PATH

WORKDIR $APP_DIR
