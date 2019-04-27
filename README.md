# DKAN for Drupal 8 - [Alpha](https://en.wikipedia.org/wiki/Software_release_life_cycle)

DKAN Open Data Portal/Catalog built on Drupal 8.

As a piece of software in its Alpha cycle, the code is continuously changing and in very active development.

## Features

- Harvesting of data from external catalogs that provide a data.json
- Dataset metadata and resources
- Web service API endpoints to work with datasets
- Integration with a decoupled front end: Provided by [Interra](https://github.com/interra) 
- A datastore to store CSV files and make them queryable through an SQL endpoint.

## Requirements

1) Install [dkan-tools](https://github.com/GetDKAN/dkan-tools). 
1) Set an environment variable called ``DRUPAL_VERSION`` with a value of ``V8``.
    1) On the command line, enter ``export DRUPAL_VERSION=V8`` or set in ``.bashrc``
1) Setup and start the proxy:
    1) Add `dkan` to `/etc/hosts`
    1) Start the proxy: 
    ``docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy`` 


## Installation

1) Create a directory for your project: ``mkdir <directory-name> && cd <directory-name>``
1) Initialize your project with dkan-tools: ``dktl init``
1) Get Drupal: ``dktl get <drupal-version>``
1) Get Drupal dependencies, and install DKAN: ``dktl make``
1) Install DKAN: ``dktl install``
1) Access the site: ``dktl drush uli --uri=dkan``

## Dummy Content

If you would like some content in the catalog, run the drush command ``dkan-dummy-content:create``. This command required Drush 9.

## Graphical User Interface (GUI)

DKAN for Drupal 8 is trying to work with independent pieces that can be used no only by us but any other open data catalog. With that goal, we are working with a decoupled React application as the frontend for DKAN.

The [React App's](https://github.com/interra/data-catalog-frontend) README contains instruction on how to work with DKAN.

### React App Embedded in Drupal

DKAN comes with an integration module that allows the React App driving the frontend to be embedded in Drupal.

To get the integration working follow these steps:
1) Place the source for the Interra ``data-catalog-frontend`` in side of your ``docroot`` directory.
1) Follow the instructions in the README of ``data-catalog-frontend``, but instead of runnig the development server at the end, build a copy with ``npm run build``
1) Enable the integration module ``interra_frontend``
1) Change the sites configuration to point the homepage (``/``) to ``/home``
