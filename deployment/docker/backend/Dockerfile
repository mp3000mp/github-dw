FROM php:8.5-apache AS base

# install utilities
RUN apt update \
    && apt install -y --no-install-recommends locales libicu-dev;

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen

# install ext
RUN docker-php-ext-install iconv pdo_mysql intl

# config apache
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo 'alias ll="ls -la"' >> ~/.bashrc

WORKDIR /var/www/html

# dev: adds composer and pcov for code coverage (also builds the prod vendor/, see local_build.sh)
FROM base AS dev

RUN apt update \
    && apt install -y --no-install-recommends curl zip unzip apt-utils vim git;

# install composer
RUN curl -sSk https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install pcov \
    && docker-php-ext-enable pcov

USER 1000:1000

# prod: last stage so it is the default target; vendor/ is shipped by deploy_back, no composer here
FROM base AS prod

USER 1000:1000
