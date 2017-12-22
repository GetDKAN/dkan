DKAN uses [Behat](http://behat.org) for behavioral testing, both locally and in a [continuous integration using CircleCI](https://circleci.com/gh/NuCivic/dkan). PHPUnit is also used for functional  Tests are run on [CircleCI](https://circleci.com/gh/NuCivic/dkan) on every push and merge, but can also be run locally.

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
 - **@ahoyRunMe** label only
 - **@api** Enables the Drupal API Driver
 - **@customizable** Exclude scenario on client sites when testing customizable functionality
 - **@datastore** Drops the table after testing
 - **@deleteTempUsers** Delete any tempusers that were created outside of 'Given users'
 - **@disablecaptcha** Disables captcha config if it is enabled, then restores config after the test
 - **@dkanBug** label only
 - **@enableFastImport** Enables fast import
 - **@enableDKAN_Workflow** Enables dkan_workflow
 - **@fixme** label only
 - **@globalUser** Populates the global user with the current user
 - **@harvest** Creates harvest sources for testing then rolls back to pre-harvest state
 - **@javascript** switches the current Mink session to Selenium2
 - **@mail** Setup the testing mail system, then restore original mail system
 - **@no-group** Tests content with no group assignment (skip on sites where group is required)
 - **@no-main-menu** used to skip tests that requires a link in the main menu
 - **@noworkflow** label only
 - **@ok** label only
 - **@pod_json_valid** label only
 - **@pod_json_odfe** label only
 - **@remove_ODFE** Disables ODFE
 - **@testBug** label only
 - **@timezone** Sets the timezone for tests and restores the timezone afterwards.
 - **@Topics** label only

 **Unique tag per scenario pattern**

 To allow customized sites to skip specific tests we are adding a unique tag to every scenario. The pattern is the feature name followed by a two digit numerical value. So the pod.feature scenarios are tagged like this: @pod_01, @pod_02, @pod_03, etc.

## PHPUnit tests

Starting from 1.13 PHPUnit tests were added into DKAN core. All tests can be found inside the `/phpunit` directory separated in different test suites, one per DKAN module.

*Running PHPUnit tests locally:*

Using Ahoy:

```sh
# To run all the tests:
ahoy dkan unittests

# To run an individual test:
ahoy dkan unittests dkan_harvest/HarvestCacheTest.php
```

Manually:
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

## Tips

Failing builds or tests can be very frustrating and occasionally make small improvements or bugfixes take much longer to complete than expected. This section is a collection of techniques that have proved useful in debugging and solving stubborn test issues in both DKAN core and individual projects.

### CircleCI Screenshots

Click on the Artifacts tab, go to the container where the error happened and then click on the html/png file to see what Behat saw during the run of that step.

### Running tests locally
Running tests in circleCI is a time consuming task. Every time you run a test in circleCI a whole new build process is triggered. Also, there is a limited number of container that can run at the same time so if your team is doing a heavy use of them you might experience several delays until your container runs.

It's highly recommend to avoid delays run test in the local environment. For example:

```
ahoy dkan test features/resource.author.feature
```

### Test isolation
To speed up the debug process it would be useful to isolate the failing tests so you are not running working tests all the time.

In order to target an specific step you pass its name from the command line:

```
ahoy dkan test features/resource.author.feature --name="Edit own resource as content creator"
```

If your tests are passing locally but failing in circleCI then you would want to take an screenshoot of the page during the step.

### Enabling screenshoots
By default Behat uses the guzzle driver so if the failing step doesn't have the @javascript just a html file will be captured. Sometimes this is enough to figure out the issue but since javascript plays a big role within drupal, enabling screenshoot could provide meaningful information about the bug.

To enable screenshoots in a given step the @javascript tag needs to be added to the problematic step.

```
@javascript
Scenario: As a site manager I should be able to add a harvest source.
....
```

### Test is working locally but fails in CircleCI

To troubleshoot this it is advisable to build the site from scratch and run the test right after the build. Some tests fails just after the build but passes in a second intent.

### Proxy CircleCI built site

So your tests are failing just in CircleCI but passing locally. Screenshots weren't helpful and you don't have any clue what's happening.

Because UI tests are intended to mimic the behavior of a real user, you can do the oposite, try to mimic the UI test as a real user.

In order to do that you will need to access to the same Dkan instance where tests are run (i.e CircleCI webserver instance).

**How to**

- Rebuild tests with ssh support
- Wait until the build finishes
- Create a ssh tunnel `ssh -p 64640 ubuntu@52.14.31.200 -L 8888:localhost:8888`
- Open in your browser http://127.0.0.1:8888

At this point http://127.0.0.1:8888 is pointing to the CircleCI server instance so you can troubleshoot using the same instance against which tests are run.

### Watch tests running in CircleCI
Most of times you won't need to do this but there are some cases where the above procedures are not enough.

Configure this is very similar to configure the proxy site.

**How to**
- Rebuild tests with ssh support
- Wait until the build finishes
- Create a ssh tunnel `ssh -p 64640 ubuntu@52.14.31.200 -L 5000:localhost:5900`
- Install RealVNC viewer. It's free and it's more performant.
- Connect using the local ip and port http://127.0.0.1:5000

The local port is the 5000 and the remote port is 5900. Notice you can pick and free local port but remote port always will be 5900.
