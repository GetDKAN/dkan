Datastore
===============

The Datastore component allow users to parse and save CSV files into database tables, allowing users to query them through a public API.

When you create a dataset with resources DKAN is reading this data directly from the file or API you added as a resource.

To get the fullest functionality possible out of your datasets, you should add your CSV resources to the datastore.

Importing Resources
---------------

When viewing a resource you will see the "Manage Datastore" button at the top of the screen.

Click the "Manage Datastore" button.

Configure the Manager
^^^^^^^^^^^^^^^

There are many ways to accomplish data querability. DKAN out of the box provides two ways to manage the process. The default manager is called **Simple Import**.

The first step on using the datastore is to configure which manager you would like to use for the resource.

On the "Manage Datastore" page, choose a datastore manager, and click save.

The **Simple Import** Manager will then need some information regarding the CSV format for proper parsing.

- **delimiter**: the character that encloses the fields in your CSV file.
- **escape**: the character used to escape other characters in your CSV file.

Enter the character that are used as delimeter, quote and escape characters in your file, then use the "Import" button at the bottom of the page to import the data from your file or API into the datastore.

Importing
^^^^^^^^^^^^^^^

Your data is now ready to use via the API! Click the "Data API" button at the top of the resource screen for specific instructions.

Datastore Status
---------------

Aftere a Manager has been chosen, the system will give you useful information about the curent status of the resource and the datastore.

* *Importer:* The manager chosen.
* *Records Imported:* The number of rows from the CSV that have been imported to the datastore.
* *Storage:* The state of the mechanism for storing the CSV records. The following states are possible: uninitialized and initialized.
* *Data Importing:* The current state of the importing process. The following states are possible: uninitialized, ready, in progress, done, error.

Drop the Datastore
---------------

To remove all records from the datastore:

* Visit the resource.
* Click the "Drop datastore" button.
* Confirm by clicking the "Drop" button.

Datastore API
---------------

Once processed, Datastore information is available via the Datastore API. For more information, see the :doc:`Datastore API page <../apis/datastore-api>`.

Alternative Manager
---------------
.. warning::
  The *FastImport* Manager only works with files hosted in the web server and with a properly configured mysql client and server.

DKAN provides a second Manager: *FastImport*.

This manager allows the importing of huge CSV files into the datastore at a fraction of the time it would take using the regular import.

Requirements
^^^^^^^^^^^^^^

- A MySQL / MariaDB database
- MySQL database should support `PDO::MYSQL_ATTR_LOCAL_INFILE` and `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` flags.
- Cronjob or similar to execute periodic imports.

.. note::

  Because of the above requirements, which may not be available on all hosting environments, this module is *disabled* by default in DKAN.

Installation
^^^^^^^^^^^^^^

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

- Make sure you **do not** see this message at the top of the page:

  .. code-block:: bash

    Required PDO flags for dkan_datastore_fast_import were not found. This module requires PDO::MYSQL_ATTR_LOCAL_INFILE and PDO::MYSQL_ATTR_USE_BUFFERED_QUERY

.. note::

  If you are using the docker-based development environment `described in the DKAN Starter documentation <https://dkan-starter.readthedocs.io/en/latest/docker-dev-env/index.html>`_, you will need to execute the following commands (take note that admin123 is the password of the admin user in that mysql environment):

  .. code-block:: bash

    ahoy docker exec db bash
    mysql -u root -padmin123
    GRANT FILE ON *.* TO 'drupal';

Usage
^^^^^^^^^^^^^^

To import a resource using Fast Import, follow the instructions previously given in *"Importing Resources"*.

Troubleshoot
^^^^^^^^^^^^^^^

- If you get an error like ``SQLSTATE[28000]: invalid authorization specification: 1045 access denied for user 'drupal'@'%' (using password: yes)`` you will need to grant FILE permissions to your MYSQL user. To do so use this command: ``GRANT FILE ON *.* TO 'user-name'``