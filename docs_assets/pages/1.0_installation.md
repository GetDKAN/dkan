@page installation Installation

@note
  <i class="fas fa-toolbox" style="color: #42b983"></i>
  [DKAN Tools](https://github.com/GetDKAN/dkan-tools) is a project
  containing commands and tools that [CivicActions](https://civicactions.com/dkan/)
  uses for our own implementations and deployments.

For either working on a project locally or working on the core DKAN software and libraries, using a standardized, [docker](https://www.docker.com/)-based local environment is recommended. Our DKAN Tools command-line utility will work on Mac or Linux to create containers for the various services needed by DKAN. This will ensure you have the same setup as DKAN's core developers, and that your environment is very close to that of our continuous integration tools.

It is possible, though not reccomended, to use DKAN Tools [without docker](https://github.com/GetDKAN/dkan-tools/tree/master#running-without-docker) and using your system's native webserver, [PHP](https://www.php.net), and database tools; see the DKAN Tools [documentation](https://github.com/GetDKAN/dkan-tools).

## Requirements

DKAN is based on Drupal software and -- generally -- runs anywhere Drupal is supported. For the most common setup, we reccomend:

-  MySQL: minimum version 5.5.3+ with PDO
-  PHP: minimum version 7.2.x
-  Apache: minimum version 2.x
-  Drush: minimum version 9.x.
-  Node: minimum version 8.10 (if using the decoupled frontend)

## Starting a new project

Follow the instructions on the DKAN Tools [README](https://github.com/getdkan/dkan-tools) to generate new Drupal site with DKAN installed on your system.

## Installing DKAN

If you already have an existing Drupal site, install DKAN with [composer](https://www.drupal.org/node/2718229). You can find the [latest DKAN release here](https://github.com/GetDKAN/dkan/releases). Composer will download the module and all of the  dependencies it requires for the backend. For more details [click here](https://github.com/GetDKAN/dkan-tools/tree/master#adding-dkan-to-an-existing-drupal-site).

```
composer require 'getdkan/dkan:2.1.0'
```

## Sample content

To populate your site with example content, enable the ``sample_content`` module:

```
drush en sample_content
```

to add and then remove the content use the following commands:

```
drush dkan:sample-content:create
drush dkan:sample-content:remove
```

## Decoupled front end

 DKAN 2.x works with a decoupled frontend so there are two pieces for getting started:

1. **[Data Catalog Frontend](https://github.com/GetDKAN/data-catalog-frontend)**

   This is a React app that will use our [Data Catalog Components](https://github.com/GetDKAN/data-catalog-components) library to build the frontend. It will serve as a starting point for your own customizations. If you are **not** using DKAN Tools, follow the instructions on the [README file](https://github.com/GetDKAN/data-catalog-frontend/blob/master/README.md) for manual installation.

2. **DKAN Frontend**

   This is an integration module that allows the React app driving the frontend to be embedded in Drupal. Be sure that it is enabled.

```
drush en frontend
```
