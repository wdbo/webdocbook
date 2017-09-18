#!/usr/bin/env sh

chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} ${WORK_DIR};

if [ ! -f "${WORK_DIR}/composer.lock" ]; then
    cd "${WORK_DIR}"
    /usr/local/bin/composer install --no-dev
    /usr/local/bin/composer wdb-init --no-dev
fi

exec "$@"
