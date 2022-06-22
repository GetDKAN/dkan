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