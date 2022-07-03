FROM php:8.1-cli-alpine

# create non-root user
RUN adduser -S dockeruser -u 1000

# install and configure composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer \
    && chmod 755 /usr/local/bin/composer

# insatll programs
RUN apk add --update --no-cache \
    bash \
    vim

# install dependencies for pecl installation
RUN apk add --no-cache \
    autoconf \
    build-base \
    zlib-dev

# install and enable extension
#RUN pecl install xdebug-2.6.1 \
#    && docker-php-ext-enable xdebug \
#    && docker-php-ext-install zip

# Configure bash and aliases
COPY bash/bashrc /home/dockeruser/.bashrc
COPY bash/bash_aliases /home/dockeruser/.bash_aliases
# Configure php and extensions
COPY config/php.ini-development /usr/local/etc/php/php.ini
#COPY config/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Configure xDebug server
ENV PHP_IDE_CONFIG "serverName=docker-xdebug-server"

# Create volume and default dir
RUN mkdir /volume
WORKDIR /volume
VOLUME /volume

# set default user to dockeruser
USER dockeruser
