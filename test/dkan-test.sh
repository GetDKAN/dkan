#!/bin/bash

# Install dependencies
composer install

# Create Database
mysql -e 'drop database dkan_test; create database dkan_test;'

# Install DKAN
cd ..
drush make --prepare-install build-dkan.make --yes test/dkan
cd test/dkan
drush si dkan --sites-subdir=default --db-url=mysql://root:@127.0.0.1/dkan_test --account-name=admin --account-pass=admin  --site-name="DKAN" install_configure_form.update_status_module='array(FALSE,FALSE)' --yes
drush cc all --yes

# Run test server
drush --root=$PWD runserver 8888 &
sleep 4
cd ../

# Run selenium
wget http://selenium.googlecode.com/files/selenium-server-standalone-2.39.0.jar
java -jar selenium-server-standalone-2.39.0.jar -p 4444 &
sleep 5

bin/behat
