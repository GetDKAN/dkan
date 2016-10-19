if [ $(uname) == "Darwin" ]; then
  concurrency=`sysctl -n hw.ncpu`
else
  concurrency=`grep -c ^processor /proc/cpuinfo`
fi

if [ "$AHOY_CMD_PROXY" = "DOCKER" ]; then
  working_copy="--working-copy"
else
  working_copy=""
fi

drush --root=docroot  make --concurrency=$concurrency --prepare-install dkan/drupal-org-core.make docroot --yes

drush --root=docroot -y --verbose $root si minimal --sites-subdir=default --account-pass='admin' --db-url=$1 install_configure_form.update_status_module='array(false,false)' &&
  ln -s ../../dkan docroot/profiles/dkan
