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

if [ "$CI_PULL_REQUEST" ]; then
  echo Diff URL: "$CI_PULL_REQUEST".diff
  files=`curl -sL "$CI_PULL_REQUEST".diff | grep "^+++" | sed 's/+++ b\///g' | grep "$DRUPAL_FILES"`
fi

if [ ! -z "$files" ]; then
  echo "Linting: $files"
  test/bin/phpcs --standard=Drupal,DrupalPractice -n $files --ignore=test/dkanextension/*,patches/*,+linkchecker.module
else
  echo "No Drupal file changes available for linting."
fi
