
cd dkan
echo "Installing dependencies.."
bash .ahoy/.scripts/composer-install.sh test

test/bin/phpcs --config-set installed_paths test/vendor/drupal/coder/coder_sniffer

if [ ! -z "$1" ]; then
  files="$files $@"
fi

if [ ! -z "$files" ]; then
  files=`git diff --name-only`
fi

if [ $CI ]; then
  files=`curl -s  $CIRCLE_REPOSITORY_URL/compare/7.x-1.x...$CIRCLE_SHA1.diff | grep +++ | sed 's/+++ b\///g'`
fi

if [ ! -z "$files" ]; then
  echo "Linting: $files \n"
  test/bin/phpcs --standard=Drupal,DrupalPractice -n $files
fi
