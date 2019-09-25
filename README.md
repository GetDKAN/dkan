# DKAN

DKAN for Drupal 8 - Alpha

[![CircleCI](https://circleci.com/gh/GetDKAN/dkan2.svg?style=svg)](https://circleci.com/gh/GetDKAN/dkan2)
[![Maintainability](https://api.codeclimate.com/v1/badges/7a93219b8ae65a83f095/maintainability)](https://codeclimate.com/github/GetDKAN/dkan2/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/7a93219b8ae65a83f095/test_coverage)](https://codeclimate.com/github/GetDKAN/dkan2/test_coverage)
[![GPLv3 license](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.en.html)

DKAN Open Data Portal/Catalog built on Drupal 8.

As a piece of software in its Alpha cycle, the code is continuously changing and in very active development.

## Features

- Harvesting of data from external catalogs that provide a data.json
- Dataset metadata and resources
- Web service API endpoints to work with datasets
- Integration with a decoupled [REACT front end](https://github.com/getdkan/data-catalog-frontend) 
- A datastore to store CSV files and make them queryable through an SQL endpoint.

## Requirements

1. Install [dkan-tools](https://github.com/GetDKAN/dkan-tools). 
2. Set an environment variable called ``DRUPAL_VERSION`` with a value of ``V8``.
    1. On the command line, enter ``export DRUPAL_VERSION=V8`` or set in ``.bashrc``
3. Setup and start the proxy:
    1. Add `dkan` to `/etc/hosts`
    2. Start the proxy: 
    ``docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy`` 


## Installation

1. Create a directory for your project: ``mkdir <directory-name> && cd <directory-name>``
2. Initialize your project with dkan-tools: ``dktl init``
3. Get Drupal: ``dktl get <drupal-version>``
4. Get Drupal dependencies, and install DKAN with a REACT frontend: ``dktl make --frontend``
5. Install DKAN: ``dktl install``
6. Access the site: ``dktl drush uli --uri=dkan``

## Dummy Content

If you would like some content in the catalog, run the drush command ``dkan-dummy-content:create``. This command requires Drush 9.

## Graphical User Interface (GUI)

DKAN for Drupal 8 is trying to work with independent pieces that can be used no only by us but any other open data catalog. With that goal, we are working with a decoupled React application as the frontend for DKAN.

The [React App's](https://github.com/GetDKAN/data-catalog-frontend) README contains instruction on how to work with DKAN.

### React App Embedded in Drupal

DKAN comes with an integration module that allows the React App driving the frontend to be embedded in Drupal.

To get the integration working follow these steps:
1. Place the source for the ``data-catalog-frontend`` in side of your ``docroot`` directory.
2. Follow the instructions in the README of ``data-catalog-frontend``, but instead of runnig the development server at the end, build a copy with ``npm run build``
3. Enable the integration module ``dkan_frontend``
4. Change the sites configuration to point the homepage (``/``) to ``/home``
