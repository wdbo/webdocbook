#!/usr/bin/env bash

openssl req -x509 -sha256 -nodes -newkey rsa:2048 -days 365 \
    -keyout ./docker/certificates/website.key \
    -out ./docker/certificates/website.crt \
    -subj "/O=WebDocBook/CN=webdocbook.docker.local/emailAddress=webdocbook@docker.local" ;
