At NuCivic we use [Behat](http://behat.org) for behavioral testing, both locally and in a [continuous integration using CircleCI](https://circleci.com/gh/NuCivic/dkan).

## Running behat tests locally

The `behat.yml` file that ships with DKAN is intended for use on CircleCI. Do run tests locally, we recommend making a copy of `behat.local.demo.yml` and overriding Behat's default profile there. 

We currently use Selenium and the Chrome driver for javascript tests. Our development environment is in Vagrant using our own [NuCivic development VM](https://github.com/NuCivic/ansible-dev-vm). We execute the tests inside the VM, but run Selenium and Chrome on our "host" machines (in most cases, a Mac).

Obviously, steps will differ depending on your development environment. 

Assuming you have a working DKAN installation you wish to test on:

1. Download [Selenium standalone server v 2.48.2](http://selenium-release.storage.googleapis.com/2.48/selenium-server-standalone-2.48.2.jar) anywhere to your local Mac/Linux machine.
2. Download the [Chrome Driver for Selenium](https://code.google.com/p/selenium/wiki/ChromeDriver) to the same machine.
3. `$ java -jar /path/to/selenium-server-standalone-2.48.2.jar -p 4444 -Dwebdriver.chrome.driver="/path/to/chromedriver"`
4. Make a copy of `behay.local.demo.yml` called `behat.local.yml`. Edit it to point the files path to the absolute path to your test/files directory as accessed on your host/local machine (probably within the folder you share with vagrant).
5. `bin/behat --config=behat.local.yml`

Your tests should run from the VM and use your host machine as a Selenium server, meaning any Selenium tests will run in an instance of Chrome on your machine.
