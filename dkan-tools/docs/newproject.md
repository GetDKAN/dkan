# Starting a new project, step-by-step


1. To start a project with DKAN tools, create a project directory.

    ```
    mkdir my_project && cd my_project
    ```

1. Inside the project directory, initialize your project.

    ```
    dktl init
    ```

    !!! note "Using a specific version of DKAN"
        Define the tag or branch on this command (e.g. `dktl init --dkan=branch-name`).
        By itself, the _init_ command will use the latest release of DKAN.

1. Make a full Drupal/DKAN codebase, primarily using composer (Options are passed directly to `composer install`, see [documentation](https://getcomposer.org/doc/03-cli.md#install-i)).

    ```
    dktl make
    ```

    _make_ options:

      * `--prefer-source`
      * `--prefer-dist`
      * `--no-dev`
      * `--optimize-autoloader`


1. Install. Creates a database, installs Drupal, and enables DKAN.

    ```
    dktl install
    ```

    _install_ options:

      * `--existing-config` Add this option to preserve existing configuration.

1. Add the front end.

    ```
    dktl frontend:install
    dktl frontend:build
    ```

1. Access the site.

    ```
    dktl drush uli
    ```

1. Stop the docker-compose project, removing all containers and networks.

    ```
    dktl down
    ```

    This will keep files downloaded during the make phase, as well as any changes made to them. But any database will be removed and all content lost.

