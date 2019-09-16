FROM php:7.1-apache

ARG APP_DIR=/var/www/app
RUN sed -i 's/html/app/g' /etc/apache2/sites-available/000-default.conf

# git and wget to install stuff, mysql-client for debugging, zip for composer
RUN apt-get update && apt-get install -y git wget mariadb-client zip &&\
    apt-get autoremove -y &&\
    rm -rf /var/lib/apt/lists/* &&\
    rm -rf /var/cache/apt/*

# Need bats-core for testing
RUN git clone https://github.com/bats-core/bats-core.git /tmp/bats-core && \
  cd /tmp/bats-core && \
  ./install.sh /usr/local && \
  /bin/rm -rf /tmp/bats-core

# Need mod_rewrite
RUN a2enmod rewrite 

# Install php extensions
# RUN docker-php-ext-install mysqli
RUN docker-php-ext-install mysqli

# Install composer
COPY docker/install-composer.sh /tmp/install-composer.sh
RUN /tmp/install-composer.sh

# Add composer-installed libs to path
ENV PATH=/var/www/app/vendor/bin:$PATH

WORKDIR $APP_DIR
