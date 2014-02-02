#!/bin/bash

ORIGINALDIR=`pwd`

# help ( exit_code = 0 )
help () {
    echo
    echo "## Track logs of DocBook"
    echo
    usage
    echo
    echo "options:"
    echo "    error         : see error log files"
    echo "    history       : see history log files"
    echo "    all           : see all log files (default)"
    echo "    help (-h)     : get this help message"
    echo
    exit ${1:-0}
}

# usage ( exit = false , exit_status = 1 )
usage () {
    echo "usage:  $0  [error / history / ALL]  [-h / help]"
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
        error "unknown root directory ; you may run this script from DocBook root directory"
    fi;
else
    error "unknown root directory ; you may run this script from DocBook root directory"
fi;

if [ $# -gt 0 ] && [ "$1" = 'error' ]; then
    FILESMASK="*error*"
elif [ $# -gt 0 ] && [ "$1" = 'history' ]; then
    FILESMASK="*history*"
elif [ $# -gt 0 ] && [ "$1" = 'all' ]; then
    FILESMASK=''
elif [ $# -gt 0 ]; then
    error "unknown parameters '$*'"
else
    FILESMASK=''
fi

echo "> tailing log files from '${ORIGINALDIR}/tmp/log/'"
if [ "$FILESMASK" != '' ]; then
    tail -f tmp/log/$FILESMASK
else
    tail -f tmp/log/*
fi

echo "_ ok"

# Endfile
