#!/bin/bash

ORIGINALDIR=`pwd`

# help ( exit_code = 0 )
help () {
    echo
    echo "## Flush temporary files of WebDocBook"
    echo
    usage
    echo
    echo "options:"
    echo "    cache         : flush all 'tmp/cache/*' files"
    echo "    i18n          : flush all 'tmp/i18n/*' files"
    echo "    all           : flush both above directories (default)"
    echo "    help (-h)     : get this help message"
    echo
    exit ${1:-0}
}

# usage ( exit = false , exit_status = 1 )
usage () {
    echo "usage:  $0  [cache / i18n / ALL]  [-h / help]"
    if [ $# -gt 0 ]; then exit ${2:-1}; fi
}

# error ( error_string = 'unknown error' )
error () {
    echo "!! > ERROR:"
    echo "     ${1:-unknown error}"
    echo
    usage 1
}

if [ $# -gt 0 ] && [[ "$*" = *-h* || "$*" = *help* ]]; then
    help
fi

if [ -d $ORIGINALDIR ]; then
    ORIGINALDIR_VERIF="${ORIGINALDIR}/composer.json"
    if [ -f $ORIGINALDIR_VERIF ]; then
        MYROOT=$PRODROOT
        MYTYPE='PROD'
    else
        error "unknown root directory ; you may run this script from WebDocBook root directory"
    fi;
else
    error "unknown root directory ; you may run this script from WebDocBook root directory"
fi;

if [ $# -gt 0 ] && [ "$1" = 'cache' ]; then
    FLUSH_CACHE=true
    FLUSH_I18N=false
elif [ $# -gt 0 ] && [ "$1" = 'i18n' ]; then
    FLUSH_CACHE=false
    FLUSH_I18N=true
elif [ $# -gt 0 ] && [ "$1" = 'all' ]; then
    FLUSH_CACHE=true
    FLUSH_I18N=true
elif [ $# -gt 0 ]; then
    error "unknown parameters '$*'"
else
    FLUSH_CACHE=true
    FLUSH_I18N=true
fi

echo "> flushing WebDocBook temporary files in '$ORIGINALDIR'"

if $FLUSH_CACHE; then
    echo "var/cache/* ..."
    rm -rf var/cache/*
fi
if $FLUSH_I18N; then
    echo "var/i18n/* ..."
    rm -rf var/i18n/*
fi

echo "_ ok"

# Endfile
