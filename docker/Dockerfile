# FROM php:7-alpine
FROM alpine:latest

RUN apk --no-cache upgrade
RUN apk add --no-cache apache2 \
    bash \
    curl \
    git \
    jq \
    mariadb \
    openrc \
    php7 \
    php7-apache2 \
    php7-curl \
    php7-iconv \
    php7-json \
    php7-mbstring \
    php7-mysqli \
    php7-openssl \
    php7-pcntl \
    php7-pdo \
    php7-phar \
    php7-posix \
    php7-session \
    php7-simplexml \
    php7-sodium \
    php7-sqlite3 \
    php7-tokenizer \
    php7-xml \
    php7-xmlreader \
    php7-xmlwriter \
    php7-zlib \
    wget \
    zip

# Need bats-core for testing
RUN git clone https://github.com/bats-core/bats-core.git /tmp/bats-core && \
  cd /tmp/bats-core && \
  ./install.sh /usr/local && \
  /bin/rm -rf /tmp/bats-core

# Enable mod_rewrite and allow overrides
RUN sed -i '/LoadModule rewrite_module/s/^#//g' /etc/apache2/httpd.conf && \
    sed -i 's#AllowOverride [Nn]one#AllowOverride All#' /etc/apache2/httpd.conf && \
    sed -i 's/\#ServerName www.example.com:80/ServerName localhost/' /etc/apache2/httpd.conf

# Install composer
COPY docker/install-composer.sh /tmp/install-composer.sh
RUN /tmp/install-composer.sh

# Add composer-installed libs to path
ENV PATH=/var/www/app/vendor/bin:$PATH

COPY docker/apache2-foreground /usr/local/bin/

ARG APP_DIR=/var/www/app
RUN sed -i 's#localhost/htdocs#app/public#g' /etc/apache2/httpd.conf

WORKDIR $APP_DIR

CMD ["apache2-foreground"]
