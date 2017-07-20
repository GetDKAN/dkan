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

drush --root=docroot -y --verbose si minimal --sites-subdir=default --account-pass='admin' --db-url=$1 install_configure_form.update_status_module='array(false,false)' &&
  ln -s ../../dkan docroot/profiles/dkan
chmod +w docroot/sites/default/settings.php

if [ "$AHOY_CMD_PROXY" = "DOCKER" ]; then
  printf "// Docker Database Settings\n\$databases['default']['default'] = array(\n  'database' => 'drupal',\n  'username' => 'drupal',\n  'password' => '123',\n  'host' => 'db',\n  'port' => '',\n  'driver' => 'mysql',\n  'prefix' => '',\n);\n" >> docroot/sites/default/settings.php
fi
printf "// DKAN Datastore Fast Import options.\n\$databases['default']['default']['pdo'] = array(\n  PDO::MYSQL_ATTR_LOCAL_INFILE => 1,\n  PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 1,\n);\n" >> docroot/sites/default/settings.php

chmod -w docroot/sites/default/settings.php
