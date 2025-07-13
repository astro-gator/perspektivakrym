FROM php:8.2-fpm

# Set Environment Variables
ENV DEBIAN_FRONTEND noninteractive

# Default PHP Extensions
RUN apt-get clean; \
  rm -rf /var/lib/apt/lists/*; \
  apt-get clean; \
  apt-get update --fix-missing; \
  apt-get upgrade -y; \
  apt-get install -y \
    apt \
    apt-transport-https \
    apt-utils \
    ca-certificates \
    curl \
    dirmngr \
    git \
    gnupg2 \
    inetutils-ping \
    libc-client-dev \
    libicu-dev \
    libmcrypt-dev \
    libmemcached-dev \
    libonig-dev \
    libpq-dev \
    libxslt-dev \
    libzip-dev \
    lsb-release \
    make \
    unzip \
    wget \
    zip \
    libjpeg-dev \
    libpng-dev \
    libxpm-dev \
    libfreetype6-dev \
    libz-dev \
    libgmp-dev \
    libwebp-dev; \
  rm -rf /var/lib/apt/lists/*

# Development php.ini
RUN if [ ${DEVELOP} = true ]; then \
        mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"; \
    else \
        mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    fi

# Install the PHP gd library
RUN docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-webp \
            --with-xpm \
            --with-freetype; \
    docker-php-ext-install gd

RUN docker-php-ext-install soap
# Zip
RUN docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Exif
RUN docker-php-ext-install exif
RUN docker-php-ext-configure exif --enable-exif

# Redis
RUN pecl install -o -f redis; \
    rm -rf /tmp/pear; \
    docker-php-ext-enable redis
# Install MySQL
RUN docker-php-ext-install pdo pdo_mysql

# MbString
RUN docker-php-ext-install mbstring

RUN docker-php-ext-install pcntl
RUN docker-php-ext-configure pcntl --enable-pcntl

# Memcached
RUN pecl install memcached; \
  docker-php-ext-enable memcached

# BcMath
RUN docker-php-ext-install bcmath

# Sockets
#RUN docker-php-ext-install sockets
RUN docker-php-source extract ; curl -fsSL https://raw.githubusercontent.com/php/php-src/6a6c8a60965c6fc3f145870a49b13b719ebd4a72/ext/sockets/config.m4 -o /usr/src/php/ext/sockets/config.m4 ; docker-php-ext-install sockets

# FileInfo
RUN docker-php-ext-install fileinfo

# Ext Intl
RUN docker-php-ext-install intl

# GMP
RUN docker-php-ext-install gmp

# PCNTL
RUN docker-php-ext-install pcntl

# Curl
RUN apt-get clean; \
  apt-get update --fix-missing; \
  apt-get install -y curl libcurl4-gnutls-dev; \
  rm -rf /var/lib/apt/lists/*; \
  wget -P /etc/ssl/certs https://curl.haxx.se/ca/cacert.pem; \
  sed -i '/;curl.cainfo =/curl.cainfo = /etc/ssl/certs/cacert.pem' /usr/local/etc/php/php.ini

# Install yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - ; \
  echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list ;\
  apt-get update --fix-missing; \
  apt-get install -y yarn; \
  rm -rf /var/lib/apt/lists/*

# Install supervisor
RUN apt-get update && apt-get install -y supervisor \
    && rm -rf /var/lib/apt/lists/*

# Copy supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN wget --no-check-certificate -q -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/1.10/gosu-amd64" \
    && chmod +x /usr/local/bin/gosu

# Configure non-root user.
ARG PUID=1006
ENV PUID ${PUID}
ARG PGID=1006
ENV PGID ${PGID}

RUN groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    chown www-data:www-data /var/log/xdebug.log \
;fi

RUN mkdir -p /var/tmp/bk/images && \
  chmod 0777 -R /var/tmp/bk/images/

# Configure locale.
ARG LOCALE=POSIX
ENV LC_ALL ${LOCALE}

WORKDIR /var/www

EXPOSE 9000
