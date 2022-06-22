#!/bin/sh

if [ -z "$1" ]; then
    echo "DOCROOT is a required argument"
    exit 1
fi


if [ -z "$2" ]; then
    echo "ENV is a required argument"
    exit 1
fi

DOCROOT=$1
ENV=$2

drush --root="$DOCROOT" cr
drush --root="$DOCROOT" updb -y

if [ -f "$DOCROOT"/../src/script/deploy.custom.sh ]; then
  "$DOCROOT"/../src/script/deploy.custom.sh "$DOCROOT" "$ENV"
else
  echo "No custom deployment script."
fi
