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

# based on distributed PHP5.6-Apache
FROM php:5.6-apache

# ClubMed proxy
ENV HTTP_PROXY="http://172.26.2.2:3128" \
    HTTPS_PROXY="http://172.26.2.2:3128" \
    http_proxy="http://172.26.2.2:3128" \
    https_proxy="http://172.26.2.2:3128"

# env vars
ENV WORK_DIR="/var/www/webdocbook" \
    COMPOSER_HOME="/var/www/.composer" \
    APACHE_RUN_USER="www-data" \
    APACHE_RUN_GROUP="www-data" \
    TZ="Europe/Paris" \
    DEFAULT_UID=1000 \
    DEFAULT_GID=1000

# install some required cmds
RUN set -ex; \
    apt-get update; \
    apt-get install -y \
        git \
        unzip \
    ; \
    rm -rf /var/lib/apt/lists/*;

# set the correct timezone
RUN ln -snf "/usr/share/zoneinfo/${TZ}" /etc/localtime; \
    echo "${TZ}" > /etc/timezone;

# set UID=1000 & GID=1000 to server user
RUN usermod -u ${DEFAULT_UID} "${APACHE_RUN_USER}"; \
    groupmod -g ${DEFAULT_GID} "${APACHE_RUN_GROUP}"; \
    find / -path /proc -prune -o -user "${APACHE_RUN_USER}" -printf '%p' -exec chown -h ${DEFAULT_UID} {} \; ; \
    find / -path /proc -prune -o -group "${APACHE_RUN_GROUP}" -printf '%p' -exec chgrp -h ${DEFAULT_GID} {} \; ;

# enable Apache mods
RUN a2enmod rewrite ssl

# update the default apache site with the config we created
ADD ./docker/apache2.conf /etc/apache2/apache2.conf
ADD ./docker/vhosts/default-host.conf /etc/apache2/sites-enabled/000-default.conf
ADD ./docker/certificates /etc/ssl/server
ADD ./docker/vhosts/ssl-host.conf /etc/apache2/sites-enabled/ssl-default.conf

# expose apache 80 & 443 ports
EXPOSE 80/tcp 443/tcp

# keep the default CMD
CMD ["apache2-foreground"]

# install basic PHP extensions
RUN set -ex; \
    apt-get update; \
    apt-get install -y \
        libicu-dev \
        libmcrypt-dev \
        libjpeg-dev \
        libpng-dev \
    ; \
    rm -rf /var/lib/apt/lists/*; \
    docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
    docker-php-ext-install -j$(nproc) \
        mcrypt \
        intl \
        gettext \
        gd \
    ;

# install Composer
RUN curl -sS https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin --filename=composer ;

# add wdbo sources
ADD . ${WORK_DIR}
# define it as the default work directory
WORKDIR ${WORK_DIR}

# entrypoint to install the app the first time
ADD ./docker/entrypoint.sh /docker-entrypoint.sh
RUN chmod a+x /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

# fix working directory permissions
RUN mkdir -p ${COMPOSER_HOME}; \
    chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} ${WORK_DIR}; \
    chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} ${COMPOSER_HOME}; \
    usermod -a -G ${APACHE_RUN_GROUP} root;

# install the app using server user
USER ${APACHE_RUN_USER}
RUN cd "${WORK_DIR}" && /usr/local/bin/composer install --no-dev; \
    cd "${WORK_DIR}" && /usr/local/bin/composer wdb-init;
USER root
