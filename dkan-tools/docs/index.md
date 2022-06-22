# DKAN Tools

This CLI application provides tools for implementing, developing, and maintaining [DKAN](https://github.com/GetDKAN/dkan), the Drupal-based open data catalog. For Drupal 7.x projects use the 1.x branch.

## Requirements

DKAN Tools was designed with a [Docker](https://www.docker.com/)-based local development environment in mind. Current requirements are simply:

* Bash-like shell that can execute .sh files (Linux or OS X terminals should all work)
* [Docker](https://www.docker.com/get-docker)
* [Docker Compose](https://docs.docker.com/compose/)

That's it! All other dependencies are included in the Docker containers that dkan-tools will create.

## Basic usage

Once you are working in an initialized project folder, you can type `dktl` at any time to see a list of all available commands.

    Available commands:
    deploy                      Performs common tasks when switching databases or code bases.
    help                        Displays help for a command
    init                        Initialize DKAN project directory.
    install                     Perform Drupal/DKAN database installation
    list                        Lists commands
    make                        Get all necessary dependencies and "make" a working codebase.
    restore                     Restore files and database.
    dkan
    dkan:demo                   [demo] Create a new demo project.
    dkan:docs                   Build DKAN docs with doxygen.
    dkan:test-cypress           Run DKAN Cypress Tests.
    dkan:test-dredd             Run DKAN Dredd Tests.
    dkan:test-phpunit           Run DKAN PhpUnit Tests. Additional phpunit CLI options can be passed.
    dkan:test-phpunit-coverage  Run DKAN PhpUnit Tests and send a coverage report to CodeClimate.
    docker
    docker:compose              [dc] Run a docker-compose command. E.g. "dktl docker:compose ps".
    docker:proxy-connect        [proxy:connect] Connect the web container to the proxy.
    docker:proxy-kill           [proxy:kill] Kill the dktl proxy service.
    docker:url                  [url] Display the http web URL for the current project.
    exec
    exec:composer               [composer] Proxy to composer.
    exec:drush                  [drush] Run drush command on current site.
    frontend
    frontend:build              Build frontend app.
    frontend:get                Download the DKAN frontend app to src/frontend.
    frontend:install            Download frontend app if not present, and run npm install.
    frontend:test               Run cypress tests on the frontend app.
    git
    git:config                  Configure git in current environment with user name and email.
    git:deploy                  Deploy code including gitignored files to branch of same name on Acquia.
    git:remove-submodules       Recurse through docroot and vendor and delete all .git dirs.
    init
    init:dkan                   Add DKAN as a dependency to the project composer.json.
    init:drupal                 Create a new Drupal project in the current directory.
    install
    install:sample              Install DKAN sample content.
    make
    make:symlinks               Create symlinks from docroot to folders in src.
    restore
    restore:db                  Restore the database from a backup.
    restore:files               Restore a files archive to appropriate site directories.
    restore:grab-database       Create a database dump excluding devel and datastore tables.
    xdebug
    xdebug:start                Start xdebug on CLI and web containers.
    xdebug:stop                 Stop xdebug on CLI and web containers.

!!! note "Running without Docker"

    If for some reason you would like to use some of DKTL without docker, there is a mechanism to accomplish this.

    First of all, make sure that you have all of the software DKTL needs:

    1. PHP
    2. Composer
    3. Drush

    The mode in which DKTL runs is controlled by an environment variable: `DKTL_MODE`. To run DKLT without docker set the environment variable to `HOST`:

    ```
    export DKTL_MODE="HOST"
    ```

    To go back to running in docker mode, set the variable to `DOCKER` (or just delete it).


## Automated Proxy:

DKAN-tools leverages [traefik](https://docs.traefik.io/) to route traffic based on a per-environment domain name. Traefik is run as a singleton service named `dktl-proxy`.

dktl-proxy will serve your website from a constructed domain in the form of "{{dktl-slug}}.localtest.me", where dktl-slug is the per project string identifying the current environment. If your project directory is dkan, the project will be served at `//dkan.localtest.me`
