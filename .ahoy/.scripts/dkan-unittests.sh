echo "Installing dependencies.."
cd dkan
bash .ahoy/.scripts/composer-install.sh test

test/bin/phpunit --configuration test/phpunit $@
