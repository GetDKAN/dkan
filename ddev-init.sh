#!/bin/bash
# A script to initialize a DKAN project with DDEV. Check out the DKAN repo and
# run this script from the root of the repo.
#
# h/t @markdorison https://github.com/ddev/ddev-drupal-contrib/issues/15
#
# Usage: ddev-init [DRUPAL_VERSION]
# Example: ddev-init 10.5
DRUPAL_VERSION=${1:-10.4}
set -ex

# Validate semver format (X.Y or X.Y.Z)
if [[ ! $DRUPAL_VERSION =~ ^[0-9]+\.[0-9]+(\.[0-9]+)?$ ]]; then
  echo "Error: DRUPAL_VERSION must follow semver format (e.g., 10.4 or 10.4.1)" >&2
  exit 1
fi

DRUPAL_MAJOR_VERSION=$(echo "$DRUPAL_VERSION" | cut -d'.' -f1)
ddev config --project-type=drupal$DRUPAL_MAJOR_VERSION --docroot=web --corepack-enable
ddev add-on get ddev/ddev-drupal-contrib
ddev dotenv set .ddev/.env.web --drupal-core $DRUPAL_VERSION \
  set .ddev/.env.web --drupal-root "/var/www/html/web"

# Set up the ddev project. See https://github.com/ddev/ddev-drupal-contrib
ddev start
ddev poser
ddev select2
ddev symlink-project
ddev config --update
ddev restart
