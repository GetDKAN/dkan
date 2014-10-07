#!/bin/bash

while getopts ":t:d:" opt; do
  case "$opt" in
    t) TAG=$OPTARG ;;
    d) MYSITE_DIR=$OPTARG ;;
  esac
done
shift $(( OPTIND - 1 ))

if [ ! "$TAG" ]; then
  echo "-t identifying the tag is required"
  exit
fi
if [ ! "$MYSITE_DIR" ]; then
  echo "-d identifying the site directory is required"
  exit
fi

TMP_DIR="/tmp"
DKAN_TMP_DIR="$TMP_DIR/dkan-$TAG"
MYSITE_TMP_DIR="$TMP_DIR/mysite"
TMP_BACKUP_FILE=$TMP_DIR/mysite-`date +%s`.tar.gz

echo Updating to DKAN version $TAG
cd ~

echo "Backing up current docroot to $TMP_BACKUP_FILE"
tar pczfP $TMP_DIR/mysite.tar.gz $MYSITE_DIR
mv $MYSITE_DIR $MYSITE_TMP_DIR

echo Downloading DKAN
git clone https://github.com/NuCivic/dkan.git $DKAN_TMP_DIR
cd $DKAN_TMP_DIR
git checkout $TAG

echo Making DKAN
drush make build-dkan.make docroot

echo Moving git and sites directories
if [ -d "$DIRECTORY" ]; then
  mv $MYSITE_TMP_DIR/.git docroot/.
fi
rm -rf docroot/sites
mv $MYSITE_TMP_DIR/sites docroot/.
mv docroot $MYSITE_DIR

echo Removing git ignore files
rm $MYSITE_DIR/.gitignore
rm $MYSITE_DIR/profiles/dkan/.gitignore
rm $MYSITE_DIR/profiles/dkan/libraries/ARC2/arc/.gitignore
rm $MYSITE_DIR/profiles/dkan/libraries/chosen/.gitignore
rm $MYSITE_DIR/profiles/dkan/libraries/font_awesome/.gitignore
rm $MYSITE_DIR/profiles/dkan/libraries/Leaflet/.gitignore
rm $MYSITE_DIR/profiles/dkan/libraries/Leaflet.draw/.gitignore
rm $MYSITE_DIR/profiles/dkan/libraries/recline/.gitignore
rm $MYSITE_DIR/profiles/dkan/themes/contrib/nuboot/.gitignore

echo DKAN code updated to $TAG. Run necessary database updates.
