#!/bin/bash

ORIGINALDIR=`pwd`

# help ( exit_code = 0 )
help () {
    echo
    echo "## Fix directories rights for WebDocBook"
    echo
    usage
    echo
    echo "options:"
    echo "    chmod         : CHMOD value defined on writable directories"
    echo "    help (-h)     : get this help message"
    echo
    echo "If the script fails, try to run it as a 'sudoer'."
    exit ${1:-0}
}

# usage ( exit = false , exit_status = 1 )
usage () {
    echo "usage:  $0  [chmod = 0775]  [-h / help]"
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


CHMOD="${1:-0775}"

echo "> setting rights '${CHMOD}' on directories '${ORIGINALDIR}/{var|user}/'"

mkdir -p "$ORIGINALDIR"/{var/{cache,i18n,log},user/config}
chmod -R "$CHMOD" "$ORIGINALDIR"/var
chmod -R "$CHMOD" "$ORIGINALDIR"/user

echo "_ ok"

# Endfile
