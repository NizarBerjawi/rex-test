ARG PHP_VERSION=8.3.16
ARG ALPINE_VERSION=3.20
ARG NODE_VERSION=22.13.1
ARG COMPOSER_VERSION=2.6.6

############################
## Pull NODE and COMPOSER ##
############################
FROM node:${NODE_VERSION}-alpine${ALPINE_VERSION} AS node
FROM composer:${COMPOSER_VERSION} AS composer

##########################
## Install PHP packages ##
##########################
FROM php:${PHP_VERSION}-cli-alpine${ALPINE_VERSION} AS vendor

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.* /app
RUN composer install \
  --no-scripts \
  --no-suggest \
  --no-interaction \
  --prefer-dist \
  --optimize-autoloader

COPY . /app
RUN composer dump-autoload \
  --optimize \
  --classmap-authoritative

##################################
## Build BASE application image ##
##################################
FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} AS base

LABEL maintainer="Nizar El Berjawi <nizarberjawi12@gmail.com>"

RUN echo "UTC" > /etc/timezone

RUN apk --update add --no-cache \
    zip \
    curl \
    unzip \
    libzip-dev \
    postgresql-dev && \
  docker-php-ext-install \
    pgsql \
    pdo_pgsql \
    zip

WORKDIR /app  

EXPOSE 9000

CMD ["php-fpm"]

#########################################
## Build DEVELOPMENT application image ##
#########################################
FROM base AS development

ARG HOST_USER
ARG HOST_UID
ARG HOST_GID

RUN apk --update add --no-cache \
    vim \
    bash \
    make \
    htop

# Composer binary
COPY --from=composer /usr/bin/composer /usr/bin/composer
# Node/npm binaries
COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

RUN addgroup --gid ${HOST_GID} ${HOST_USER}
RUN adduser \
  --ingroup ${HOST_USER} \ 
  --gecos ${HOST_USER} \
  --uid ${HOST_UID} \
  --shell /bin/bash \
  --disabled-password ${HOST_USER}

USER ${HOST_USER}

########################################
## Build PRODUCTION application image ##
########################################
FROM base AS production

COPY --chown=www-data:www-data ./ /app

COPY --chown=www-data:www-data ./.docker/app/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --chown=www-data:www-data ./.docker/app/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY --chown=www-data:www-data ./.docker/app/php/php.ini-production /usr/local/etc/php/php.ini

COPY --chown=www-data:www-data --from=vendor /app/vendor /app/vendor/

USER www-data