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

drush --root=docroot -y make --no-core --contrib-destination=./ dkan/drupal-org.make --no-recursion --no-cache --verbose $working_copy --concurrency=$concurrency docroot/profiles/dkan $@
