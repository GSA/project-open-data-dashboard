FROM php:7.0-fpm

ARG APP_DIR=/var/www

RUN apt-get update && apt-get install -y \
  git \
  unzip \
  wget

# Install php extensions
RUN docker-php-ext-install mysqli

# Install composer
COPY docker/install-composer.sh /tmp/install-composer.sh
RUN /tmp/install-composer.sh

# Install php dependencies
COPY composer.json $APP_DIR
COPY composer.lock $APP_DIR
RUN cd $APP_DIR && composer install --no-dev

WORKDIR $APP_DIR
