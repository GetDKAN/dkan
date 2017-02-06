============================
Fast Import Option
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