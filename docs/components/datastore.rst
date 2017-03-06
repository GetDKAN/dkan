##############
DKAN Datastore
##############

DKAN features a Datastore for uploaded files.

Any type of file can be uploaded to DKAN through the "Add Resources" form or through the API. CSV and XML files, however, can also parsed and inserted into unique tables in the DKAN database.

===================
Drupal Architecture
===================

The DKAN Datastore's importer is a wrapper around the `Feeds <https://www.drupal.org/project/feeds>`_ module. The custom `Feeds Flatstore Processor <https://github.com/NuCivic/feeds_flatstore_processor>`_ and `Feeds Field Fetcher <https://www.drupal.org/project/feeds_field_fetcher>`_ plugins were created  the file uploaded to the resource form a feed item.

The `Data <https://www.drupal.org/project/data>`_ module is used to manage datastore tables' schema.

The Datastore API uses the `Services <https://www.drupal.org/project/services>`_ module to provide an endpoint, although nearly all the underlying functionality is overridden and provided directly by the `DKAN Datastore API <https://www.drupal.org/project/services>`_ module.

===========
Basic Usage
===========

DKAN provides UI for managing the Datastore. Management activities include:

* Importing items
* Deleting items
* Editing the schema (see below)
* Edit Views integration

Drush commands are also included, described below.

If you have successfully created a dataset with resources, you now have data in DKAN which you can display and store in several ways. However, DKAN is still reading this data directly from the file or API you added as a resource.

To get the fullest functionality possible out of your datasets, including a public API that can be used to develop 3rd party applications, you must complete the final step of adding your resources to DKAN's own datastore. (At the moment, a DKAN datastore is simply a table in the main database.)

If you are exploring a resource that is not yet in the datastore, you will see a message advising you of this. Click the "Manage Datastore" button at the top of the screen. On the "Manage Datastore" page, use the "Import" button at the bottom of the page to import the data from your file or API into DKAN's local datastore.

Notification to import resource to datastore:

![Manage Datastore: Notification](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-04-03%20at%206.19.09%20PM.png)

Importing the resource:

 ![Manage Datastore: Import](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-04-03%20at%206.19.26%20PM.png)

Notification of a successful import:

![Manage Datastore: Success](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-04-03%20at%206.19.53%20PM.png)

Your data is now ready to use via the API! Click the "Data API" button at the top of the resource screen for specific instructions. DKAN datastores can also be created and updated from files using the following Drush commands:

To create a datastore from a local file: ``drush dsc [path-to-local-file]``

To update a datastore from a local file: ``drush dsu [datastore-id] [path-to-local-file]``

To delete a datastore file (imported items will be deleted as well): ``drush dsfd (datastore-id)`` To get the URL of the datastore file: ``drush dsfuri [datastore-id]``

******************
Processing Options
******************

Files are parsed and inserted in batches. The user has the option of parsing them upon form submission. If the user chooses to parse the file manually they are able to see the progress of the processing through a batch operations screen similar to the one below.

![Drupal batch operation](http://drupal.org/files/images/computed_field_tools_drupal7_batch.png)

Files that are not processed manually are processed in pieces during cron.

--------
Geocoder
--------

DKAN's native Datastore can use the Drupal `Geocoder <https://www.drupal.org/project/geocoder>`_ module to add latitude/longitude coordinates to resources that have plain-text address information. This means that datasets containing plain-text addresses can be viewed on a map using the `Data Preview`_ or more easily used to build map-based data visualizations.

============================
Using the Fast Import Option
============================

DKAN Datastore's "fast import" allows for importing huge CSV files into the datastore at a fraction of the time it would take using the regular import.

When a CSV is imported using the regular import, this is what it happens under the hood:

1. PHP interpreter reads the file line-by-line from the disk
2. Each time a line is parsed it sends a query to the database
3. The database receives the query and parses it
4. The database creates a query execution plan
5. The database excecutes the plan (i.e., inserts a new row)

.. note::

  Steps 3, 4 and 5 are executed for *each row* in the CSV.

The Datastore Fast Import was designed to remove as many steps as possible from the previous list. It performs the following steps:

1. PHP interpreter sends a LOAD DATA query to the database
2. The database receive the query and parses it
4. The database reads and imports the whole file in a table

Only one query is executed, so the amount of time required to import a big dataset is drastically reduced. On a several-hundred-megabyte file, this could mean the difference between an import time of hours and about one minute.

************
Requirements
************

- A MySQL / MariaDB database
- MySQL database should support `PDO::MYSQL_ATTR_LOCAL_INFILE` and `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` flags.
- Cronjob or similar to execute periodic imports.
- Drush

.. note::

  Because of the above requirements, which may not be available on all hosting environments, this module is *disabled* by default in DKAN.

************
Installation
************

- Inside your settings.php add a `pdo` element to your database configuration. For example:
.. code-block:: php

  <?php
  $databases['default']['default'] = array (
    'database' => 'drupal',
    'username' => 'drupal',
    'password' => '123',
    'host' => '172.17.0.11',
    'port' => '',
    'driver' => 'mysql',
    'prefix' => '',
    'pdo' => array(
       PDO::MYSQL_ATTR_LOCAL_INFILE => 1,
       PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 1,
     )
  );

- Go to **/admin/modules**, turn on DKAN Datastore Fast Import and press **Save configuration**. Alternatively you can use drush to enable this module: ``drush en dkan_datastore_fast_import``.
- Make sure this message **did not** show up at the top of the page:
.. code-block:: bash

  Required PDO flags for dkan_datastore_fast_import were not found. This module requires PDO::MYSQL_ATTR_LOCAL_INFILE and PDO::MYSQL_ATTR_USE_BUFFERED_QUERY

- Set up this command to run periodically using a cronjob or similar: ``drush queue-run dkan_datastore_queue``

*************
Configuration
*************

To configure how Fast Import behaves go to **admin/dkan/datastore**.

There are 3 basic configurations that controls the **Use fast import** checkbox in the **Manage Datastore** page:

:Use regular import as default: **Use Fast Import** checkbox is uncheked by default on the resource's datastore import form so files are imported using the normal dkan datastore import. However you can still enable fast import for any resource by clicking that checkbox.

:Use fast import as default: **Use Fast Import** checkbox is cheked by default so files are imported using DKAN Fast Import. Like the previous setting, you can uncheck **Use Fast Import** on the resource-specific datastore import form to use the normal import instead.

:Use fast import for files with a weight over: From this setting you obtain a refined control about when **Use Fast Import** should be checked. This option reveals an additional setting: **"File size threshold."** "Use Fast Import" will be checked on the datastore import form for all the files over this size threshold. A size expressed as a number of bytes with optional SI or IEC binary unit prefix (e.g. 2, 3K, 5MB, 10G, 6GiB, 8 bytes, 9mbytes)

Either of the two "Use fast import" options will also reveal the following additional settings:

:Load Data Statement: Some hostings doesn't support ``LOAD DATA LOCAL INFILE``. If that's your case you can switch to ``LOAD DATA INFILE``.
:Queue Filesize Threshold: If a file is small enough, you can avoid waiting until the drush queue runs by configuring this threshold. Files with a size under this value won't be queued and will rather imported during the request. The time to perform the import should fit into the php request timeout, or your import could be aborted.


**********************
Usage
**********************

To import a resource using Fast Import:

- Create a resource using a CSV file (**node/add/resource**) or edit an existing one.
- Click on **Manage Datastore**
- Make sure **No imported items.** legend shows up.
- Check **Use Fast Import** checkbox
- Press **import**

=============
Datastore API
=============

Once processed, Datastore information is available via the Datastore API. For more information, see the `Datastore API page <../apis/datastore-api.rst>`_.
