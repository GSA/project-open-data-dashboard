FROM php:7.1-fpm

ARG APP_DIR=/var/www

RUN apt-get update && apt-get install -y \
  git \
  unzip \
  wget

# Install php extensions
RUN docker-php-ext-install mysqli

# Copy app
COPY . $APP_DIR

# Install composer
RUN $APP_DIR/docker/app/install-composer.sh

# Install php dependencies
# Use COMPOSER_PROCESS_TIMEOUT to avoid timing out on the slow subversion
# checkout for outdated packages. See
# https://github.com/GSA/datagov-deploy/issues/612#issuecomment-508871678
RUN cd $APP_DIR && composer install --no-dev

WORKDIR $APP_DIR
