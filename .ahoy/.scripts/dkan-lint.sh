
dkan/test/bin/phpcs --config-set installed_paths dkan/test/vendor/drupal/coder/coder_sniffer

if [ $CI ]; then
  files=curl -s  $CIRCLE_REPOSITORY_URL/compare/7.x-1.x...$CIRCLE_SHA1.diff | grep +++ | sed 's/+++ b\///g'
fi


if [ $files ]; then
  dkan/test/bin/phpcs --standard=Drupal,DrupalPractice -n $files
fi
