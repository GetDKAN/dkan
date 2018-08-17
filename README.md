# DKAN 8.x-2.x Prototype

DKAN Open Data Portal built on Drupal 8. See NOTES.md for additional information.

## Installation

This is a Drupal 8 _profile_. You will need to create a new composer project in your local environment and add DKAN as a dependency. Here is an example composer.json:

```json
{
    "minimum-stability": "dev",
    "description": "DKAN Test",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/getdkan/dkan2"
        },
        {
            "type": "composer",
            "url": "https://packages.drupal.org/8"
        },
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    ],
    "require": {
        "composer/installers": "^1.2",
        "oomphinc/composer-installers-extender": "^1.1",
        "drupal-composer/drupal-scaffold": "^2.0.0",
        "drush/drush": "^9.3",
        "getdkan/dkan2": "dev-master",
        "cweagans/composer-patches": "^1.5.0"
    },
    "require-dev": {
      "burdamagazinorg/thunder-dev-tools": "dev-master",
      "drupal/coder": "8.2"
    },
    "extra": {
        "enable-patching": true,
        "installer-paths": {
          "docroot/core": ["type:drupal-core"],
          "docroot/libraries/{$name}": ["type:drupal-library"],
          "docroot/modules/contrib/{$name}": ["type:drupal-module"],
          "docroot/themes/contrib/{$name}": ["type:drupal-theme"],
          "docroot/profiles/{$name}": ["type:drupal-profile"],
          "drush/contrib/{$name}": ["type:drupal-drush"]
       }
    }
}
```
Create an empty folder and add a composer.json file like this one, run `composer install`, and you should have a working docroot. You can now run a normal Drupal installation using the "dkan2" profile.


By the time we do a stable release, this project will be merged into the main DKAN repository and the profile will just be called "dkan." For now, we are using "dkan2" to avoid ambiguity. See the NOTES.md for additional information on this initial phase of development.

## Development Environment

DKAN has the same minimum requirements as any Drupal 8 project, with one exception: it requires MySQL 5.7.

### Developing with Docksal

We currently use [Docksal](https://docksal.io/) for local development. If you have Docksal installed locally, navigate to your project root (not your `/docroot`) and run `fin init`. Now edit your `/.docksal/docksal.yml` file and add the following lines:

```yaml
services:
  db:
    image: docksal/db:1.2-mysql-5.7
```

You should now be able to bring up the correct containers with `fin start`. Install by running `fin drush site:install dkan2 --db-url=mysql://user:user@db/default`.
