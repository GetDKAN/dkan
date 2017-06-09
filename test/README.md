At NuCivic we use [Behat](http://behat.org) for behavioral testing, both locally and in a [continuous integration using CircleCI](https://circleci.com/gh/NuCivic/dkan). PHPUnit is also used for functional  Tests are run on [CircleCI](https://circleci.com/gh/NuCivic/dkan) on every push and merge, but can also be run locally.

## Behat tests

The `behat.yml` file that ships with DKAN is intended for use on CircleCI. When running tests locally, we recommend making a copy of `behat.local.demo.yml` and overriding Behat's default profile there. 

Obviously, steps will differ depending on your development environment. 

Assuming you have a working DKAN installation you wish to test on:

1. Download [Selenium standalone server v 2.48.2](http://selenium-release.storage.googleapis.com/2.48/selenium-server-standalone-2.48.2.jar) anywhere to your local Mac/Linux machine.
2. Download the [Chrome Driver for Selenium](https://code.google.com/p/selenium/wiki/ChromeDriver) to the same machine.
3. `$ java -jar /path/to/selenium-server-standalone-2.48.2.jar -p 4444 -Dwebdriver.chrome.driver="/path/to/chromedriver"`
4. Make a copy of `behay.local.demo.yml` called `behat.local.yml`. Edit it to point the files path to the absolute path to your test/files directory as accessed on your host/local machine (probably within the folder you share with vagrant).
5. `bin/behat --config=behat.local.yml`

Your tests should run from the VM and use your host machine as a Selenium server, meaning any Selenium tests will run in an instance of Chrome on your machine.

### Behat Tags
- **@add_ODFE** Enables ODFE
- **@ahoyRunMe** no code
- **@api** Enables the Drupal API Driver
- **@customizable** Exclude scenario on client sites when testing customizable functionality
- **@datastore** Drops the table after testing
- **@deleteTempUsers** Delete any tempusers that were created outside of 'Given users'
- **@disablecaptcha** Disables captcha config if it is enabled, then restores config after the test
- **@dkanBug** no code
- **@enableFastImport** Enables fast import
- **@enableDKAN_Workflow** Enables dkan_workflow
- **@fixme** no code
- **@globalUser** Populates the global user with the current user
- **@javascript** Create screen shots on fails
- **@mail** Setup the testing mail system, then restore original mail system
- **@no-main-menu** used to skip tests that requires a link in the main menu
- **@noworkflow** no code
- **@ok** no code
- **@pod_json_valid** no code
- **@pod_json_odfe** no code
- **@remove_ODFE** Disables ODFE
- **@testBug** no code
- **@timezone** Sets the timezone for tests and restores the timezone afterwards.
- **@Topics** no code

## PHPUnit tests

Starting from 1.13 PHPUnit tests were added into DKAN core. All tests can be found inside the `/phpunit` directory separated in different test suites, one per DKAN module.
  
Follows the steps that are needed for running PHPUnit tests locally:
  
1. Edit the configuration on `boot.php` if needed. The `$dir` variable needs to point to the actual DKAN working directory.
2. If you are using the [DKAN Starter docker/ahoy environment](http://dkan-starter.readthedocs.io/en/latest/docker-dev-env/installation.html), get to the Docker cli prompt: `ahoy docker exec bash`.
3. Go to `/test` folder.
4. Run `composer install`.
5. You can execute all available PHPUnit by running `bin/phpunit --configuration phpunit`

PHPUnit will load the configuration from `/test/phpunit/phpunit.xml`.

### Running specific tests

PHPUnit allows you to easily filter tests that are going to be executed by using the `--filter <pattern>` option.
 
For example:

```sh
# Execute all tests inside "TestCaseClass":
bin/phpunit --configuration phpunit --filter TestCaseClass
# Execute only "testMethod":
bin/phpunit --configuration phpunit --filter testMethod
```

For additional options or more detailed information on how to use PHPUnit please check the [PHPUnit Documentation]( https://phpunit.de/manual/current/en/textui.html)
 
