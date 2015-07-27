## Running behat tests locally

These are the steps for running DKAN's [Behat tests](http://docs.behat.org/en/v2.5/) on the [NuCivic development VM](https://github.com/NuCivic/ansible-dev-vm) or other local environments. 

Once you are in your DKAN directory, type the following commands:

* `$ cd test`
* `$ mysql -e 'drop database dkan_test; create database dkan_test;'`
* `$ cd ..`
* `$ drush make --prepare-install build-dkan.make --yes test/drupal`
* `$ cd test/drupal`
* `$ drush si dkan --sites-subdir=default --db-url=mysql://db_user:db_pass@127.0.0.1/dkan_test --account-name=admin --account-pass=admin --site-name="DKAN" install_configure_form.update_status_module='array(FALSE,FALSE)' --yes`
* `$ drush cc all --yes`
* `$ drush --root=$PWD runserver 8888 &`
* `$ cd ../`
* `$ sudo apt-get install firefox`
* `$ sudo apt-get install xvfb`
* `$ Xvfb :99 -ac &`
* `$ export DISPLAY=:99`
* `$ wget http://selenium-release.storage.googleapis.com/2.46/selenium-server-standalone-2.46.0.jar`
* `$ java -jar selenium-server-standalone-2.46.0.jar -p 4444 &`
* `$ bin/behat`

Firefox and Xvfb are needed if you are using a headless machine. Also, note that other versions of Selenium present some problems with firefox, so be careful with it.
