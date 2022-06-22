# Modifying the Docker containers

By default, dkan-tools will run docker containers with everything needed for a DKAN site to run.

Sometimes we need to modify those docker containers.

A common example of these kinds of modification is adding an environment variable to the web, or cli containers.

dkan-tools provides a mechanism to do just this.

The configuration that dkan-tools uses to start the docker containers is in `assets/docker` in the dkan-tools repo.

To make changes to that configuration, you can add a file named `docker-compose.overrides.yml` to the `src/docker` directory **in your project**.

Any valid docker-compose configuration can be added to that file and it will be merged with the default configuration from `assets/docker/docker-compose.common.yml` before the containers are created.

!!! note "Seeing your configuration"

    After adding your file and making configuration changes you will have to remove and recreate your containers, you can do this by running `dktl dc kill && dktl dc rm && dktl`