#
# This file is part of the WebDocBook package.
#
# Copyleft (ↄ) 2008-2017 Pierre Cassat <me@picas.fr> and contributors
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.
#
# The source code of this package is available online at
# <http://github.com/wdbo/webdocbook>.
#

# based on distributed PHP5.6-Apache image
FROM php:5.6-apache

# build arguments
# you can customize these values when building the image with `--build-arg <var>=<val>`
ARG NO_PROXY=''
ARG HTTP_PROXY=''
ARG HTTPS_PROXY=''
ARG WORK_DIR="/var/www/webdocbook"
ARG SERVER_ADMIN="webdocbook@docker.local"
ARG TIMEZONE="Europe/Paris"
ARG DEFAULT_UID=1000
ARG DEFAULT_GID=1000
ARG SSL_Subject="/O=WebDocBook/CN=webdocbook.docker.local/emailAddress=webdocbook@docker.local"
ARG PHPINI_FILE="./docker/php.ini-production"

# environment variables
# these are used when building the image and accessible as environment variables in the final container
ENV COMPOSER_HOME="/var/www/.composer" \
    APACHE_RUN_USER="www-data" \
    APACHE_RUN_GROUP="www-data" \
    APACHE_LOG_DIR="/var/log/apache2/" \
    SSL_CertificateFile="/etc/ssl/certs/webdocbook.docker.local.crt" \
    SSL_CertificateKeyFile="/etc/ssl/private/webdocbook.docker.local.key" \
    WORK_DIR="${WORK_DIR}" \
    SERVER_ADMIN="${SERVER_ADMIN}" \
    TIMEZONE="${TIMEZONE}" \
    DEFAULT_UID=${DEFAULT_UID} \
    DEFAULT_GID=${DEFAULT_GID} \
    SSL_Subject="${SSL_Subject}" \
    NO_PROXY="${NO_PROXY}" \
    HTTP_PROXY="${HTTP_PROXY}" \
    HTTPS_PROXY="${HTTPS_PROXY}" \
    no_proxy="${NO_PROXY}" \
    http_proxy="${HTTP_PROXY}" \
    https_proxy="${HTTPS_PROXY}"

# update packages database
RUN set -e; \
    apt-get update;

# install some required commands
RUN set -e; \
    apt-get install -y \
        curl \
        openssl \
        git \
        unzip \
    ;

# set the correct timezone
RUN set -e; \
    ln -snf "/usr/share/zoneinfo/${TIMEZONE}" /etc/localtime; \
    echo "${TIMEZONE}" > /etc/timezone;

# set UID & GID to defaults for the server user
RUN set -e; \
    usermod -u ${DEFAULT_UID} "${APACHE_RUN_USER}"; \
    groupmod -g ${DEFAULT_GID} "${APACHE_RUN_GROUP}"; \
    find / -path /proc -prune -o -user "${APACHE_RUN_USER}" -printf '%p' -exec chown -h ${DEFAULT_UID} {} \; ; \
    find / -path /proc -prune -o -group "${APACHE_RUN_GROUP}" -printf '%p' -exec chgrp -h ${DEFAULT_GID} {} \; ;

# enable some Apache modules
RUN set -e; \
    a2enmod \
        mime \
        rewrite \
        expires \
        ssl \
    ;

# generate SSL self-signed certificates
RUN set -e; \
    openssl req -x509 -sha256 -nodes -newkey rsa:2048 -days 365 \
        -keyout "${SSL_CertificateKeyFile}" \
        -out "${SSL_CertificateFile}" \
        -subj "${SSL_Subject}" ;

# update the default Apache config & hosts with local ones
ADD ./docker/apache2.conf /etc/apache2/apache2.conf
ADD ./docker/vhosts /etc/apache2/sites-enabled

# update the default PHP config with local one
ADD "${PHPINI_FILE}" /usr/local/etc/php/php.ini

# PHP logs should go to stderr
RUN set -e; \
    touch "${APACHE_LOG_DIR}/php_errors.log"; \
    chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} "${APACHE_LOG_DIR}/php_errors.log"; \
    ln -sfT /dev/stderr "${APACHE_LOG_DIR}/php_errors.log";

# install basic PHP extensions
RUN set -e; \
    apt-get install -y \
        libicu-dev \
        libmcrypt-dev \
        libjpeg-dev \
        libpng-dev \
    ; \
    docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
    docker-php-ext-install -j$(nproc) \
        mcrypt \
        intl \
        gettext \
        gd \
    ;

# install Composer & prepare its home directory
RUN set -e; \
    curl -sS https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin --filename=composer ; \
    mkdir -p ${COMPOSER_HOME}; \
    chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} ${COMPOSER_HOME};

# flush packages database
RUN set -e; \
    rm -rf /var/lib/apt/lists/*;

# add wdbo sources
ADD . ${WORK_DIR}

# fix working directory permissions
RUN set -e; \
    chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} ${WORK_DIR};

# define it as the default work directory
WORKDIR ${WORK_DIR}

# install the app using the server user
USER ${APACHE_RUN_USER}
RUN set -e; \
    cd "${WORK_DIR}" && /usr/local/bin/composer install --no-dev; \
    cd "${WORK_DIR}" && /usr/local/bin/composer wdb-init;
USER root

# expose 80 & 443 ports
EXPOSE 80/tcp 443/tcp

# keep the default CMD
CMD ["apache2-foreground"]
