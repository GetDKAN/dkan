#!/usr/bin/env bash

mkdir -p docroot/sites/default/files

if [ -d docroot/sites/default/files ]; then
  find docroot/sites/default/files/ -type d -exec chmod o+rwx {} \;
  find docroot/sites/default/files/ -type f -exec chmod o+rw {} \;
fi
