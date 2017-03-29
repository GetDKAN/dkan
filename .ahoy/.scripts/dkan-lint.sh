echo "Installing dependencies.."
cd dkan
bash .ahoy/.scripts/composer-install.sh test

test/bin/phpcs --config-set installed_paths test/vendor/drupal/coder/coder_sniffer
DRUPAL_FILES='\.*\(.php\|.inc\|.module\|.install\|.profile\|.info\)$'

if [ ! -z "$1" ]; then
  files="$files $@"
fi

if [ -z "$files" ]; then
  files=`git diff --name-only | grep "$DRUPAL_FILES"`
fi

if [ "$CIRCLE_COMPARE_URL" ]; then
  files=`curl -s "$CIRCLE_COMPARE_URL".diff | grep "^+++" | sed 's/+++ b\///g' | grep "$DRUPAL_FILES"`
fi

if [ ! -z "$files" ]; then
  echo "Linting: $files"
  # Split up linting.
  for file in $files
  do
    test/bin/phpcs --standard=Drupal,DrupalPractice -n $file
  done
else
  echo "No Drupal file changes available for linting."
fi
