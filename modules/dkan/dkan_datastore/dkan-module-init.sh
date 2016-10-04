
# Name of the current module.
DKAN_MODULE=`ls *.info | cut -d'.' -f1`

# DKAN branch or tag to use.
DKAN_VERSION="7.x-1.x"

COMPOSER_PATH="$HOME/.config/composer/vendor/bin"

if [[ "$PATH" != *"$COMPOSER_PATH"* ]]; then
  echo "> Composer PATH is not set. Adding temporarily.. (you should add to your .bashrc)"
  echo "PATH (prior) = $PATH"
  export PATH="$PATH:$COMPOSER_PATH"
fi

# Try to grab archived dkan to speed up bootstrap.
URL="https://s3-us-west-2.amazonaws.com/nucivic-data-dkan-archives/dkan-$DKAN_VERSION.tar.gz"
if wget -q "$URL"; then
  mv dkan-$DKAN_VERSION.tar.gz ../
  cd ..
  tar -xzf dkan-$DKAN_VERSION.tar.gz
  # We need to fix the archive process to delete this file.
  rm -rf dkan/docroot/sites/default/settings.php
  mv $DKAN_MODULE dkan/
  mv dkan $DKAN_MODULE
  cd $DKAN_MODULE
  set -e
  cd dkan
  bash dkan-init.sh dkan --skip-init --deps
  cd ..
  ahoy drush "-y --verbose si minimal --sites-subdir=default --account-pass='admin' --db-url=$DATABASE_URL install_configure_form.update_status_module=\"'array\(FALSE,FALSE\)'\""
else
  wget -O /tmp/dkan-init.sh https://raw.githubusercontent.com/NuCivic/dkan/$DKAN_VERSION/dkan-init.sh
  # Make sure the download was at least successful.
  if [ $? -ne 0 ] ; then
    echo ""
    echo "[Error] Failed to download the dkan-init.sh script from github dkan. Branch: $DKAN_BRANCH . Perhaps someone deleted the branch?"
    echo ""
    exit 1
  fi
  # Only stop on errors starting now.
  set -e
  # OK, run the script.
  bash /tmp/dkan-init.sh $DKAN_MODULE $@ --skip-reinstall --branch=$DKAN_VERSION
fi

ahoy dkan module-link $DKAN_MODULE
ahoy dkan module-make $DKAN_MODULE

# Use the backup if available.
if [ -f backups/last_install.sql ];then
  ahoy drush sql-drop -y &&
  ahoy dkan sqlc < backups/last_install.sql && \
  echo "Installed dkan from backup"
else
  ahoy dkan reinstall
fi

ahoy drush en $DKAN_MODULE -y

 #Fix for behat bug not recognizing symlinked feature files or files outside it's root. See https://jira.govdelivery.com/browse/CIVIC-1005
#cp dkan_workflow/test/features/dkan_workflow.feature dkan/test/features/.
