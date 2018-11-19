# DKAN 8.x-2.x Prototype

DKAN Open Data Portal built on Drupal 8. See NOTES.md for additional information.

## Installation

### Install DKAN codebase:

* ``mkdir "dkan8"``
* ``curl https://raw.githubusercontent.com/GetDKAN/dkan2/master/composer-default.json -o composer.json``
* ``composer install``

### Developing with Docksal

We currently use [Docksal](https://docksal.io/) for local development. 

* ``fin init``
* Add the following to your docksal:

```yaml
services:
  db:
    image: docksal/db:1.2-mysql-5.7
```
* ``fin start``
* ``fin drush site:install dkan2 --db-url=mysql://user:user@db/default``

### Enable Cors

For local development you will want to enable CORS. Don't do this on production.

* `cp sites/default/default.settings.yml sites/default/settings.yml``

Add the following to the cors section:

```yml

 cors.config:
    enabled: true
    # Specify allowed headers, like 'x-allowed-header'.
    allowedHeaders: ['*']
    # Specify allowed request methods, specify ['*'] to allow all possible ones.
    allowedMethods: ['*']
    # Configure requests allowed from specific origins.
    allowedOrigins: ['*']
    # Sets the Access-Control-Expose-Headers header.
    exposedHeaders: false
    # Sets the Access-Control-Max-Age header.
    maxAge: false
    # Sets the Access-Control-Allow-Credentials header.
    supportsCredentials: false
```

## Developing with and Compiling Front End

The current demo uses the Interra catalog front-end. To setup locally:

```
git clone git@github.com:interra/catalog-generate.git --branch dkan-demo
```

Either create a new site:

```
plop
```
or use ``dkan-demo``.

To run the dev server: 

* update the "devUrl" in the config.yml file to your Drupal 8 dkan backend.
* run ``node cli.js run-dev-dll; node cli.js run-dev dkan-demo``

To build for prod:

* ``node cli.js build-site dkan-demo``

This will build the site in ``build/dkan-demo``
