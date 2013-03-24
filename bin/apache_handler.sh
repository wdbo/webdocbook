#!/bin/bash
#
########################################################################
# DocBook                                                              #
# Copyright (c) 2012 Pierre Cassat                                     #
# http://www.ateliers-pierrot.fr - contact@ateliers-pierrot.fr         #
########################################################################

# Config
CHARSET='utf-8'
OPTIONS=''
REQ="$PATH_TRANSLATED"
if [ ! -z "$EMD_CHARSET" ]; then CHARSET="$EMD_CHARSET"; fi
if [ ! -z "$EMD_CONSOLE_OPTIONS" ]; then OPTIONS="$EMD_CONSOLE_OPTIONS"; fi
if [ ! -z "$*" ]; then REQ="$PATH_TRANSLATED/$*"; fi
PLAIN=${REQ:(-5)}

# Process 
CONSOLE=$(pwd)/docbook_console
DOCBOOK_RESULT=$(php "$CONSOLE" "$OPTIONS" "$REQ")

# Start with outputting the HTTP headers.
if [ "plain" = "$PLAIN" ]
then
    echo "Content-type: text/plain;charset=$CHARSET"
else
    echo "Content-type: text/html;charset=$CHARSET"
fi
echo

# debug
#echo "query : $QUERY_STRING"
#echo "console : $CONSOLE"
#echo "PATH_INFO : $PATH_INFO"
#echo "PATH_TRANSLATED : $PATH_TRANSLATED"
#echo "REDIRECT_HANDLER : $REDIRECT_HANDLER"
#echo "EMD_TPL : $EMD_TPL"
#exit 0

# Start HTML content.
if [ ! -z "$DOCBOOK_RESULT" ]
then 
	echo "$DOCBOOK_RESULT";
else
	 cat "$PATH_INFO";
fi

# Endfile
