#!/bin/bash

# cleanup var/ & user/
rm -rf var/cache/*
rm -rf var/i18n/*
rm -rf var/log/*
rm -rf user/config/*

# 644 for all files
find . -type f -exec chmod 0644 {} \;

# 755 for all dirs
find . -type d -exec chmod 0755 {} \;

# +x for bins
find . -type f -name "*.sh" -exec chmod 0644 {} \;
find bin/ -type f \( -name "README.md" -prune \) -o -exec chmod a+x {} \;

# 775 for var/ and user/
chmod -R 0775 var
chmod -R 0775 user

