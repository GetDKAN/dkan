Datastore
===============

When you create a dataset with resources, DKAN is reading the data directly from the resource file or API.

The Datastore component provides an option for you to parse **CSV** or **TSV** files and save the data into database tables. This allows users to query the data through a public API.

So by adding your CSV resources to the datastore, you are getting the fullest functionality possible out of your datasets.

Importing Resources
-------------------

When viewing a resource you will see the "Manage Datastore" button among the local task options.

Click the "Manage Datastore" button. The top of this screen will give you useful information about the current status of the resource and the datastore.

:Importer: The manager chosen.
:Records Imported: The number of rows from the CSV that have been imported to the datastore.
:Data Importing: The current state of the importing process. The following states are possible: ready, in progress, done, or error.

Step 1: Configure the Manager
*****************************

DKAN provides two ways to manage data imports.

:Simple Import: this is the default manager and will be the only option unless you enable *Fast Import*.
:Fast Import: this is disabled by default as it is still experimental, you can enable it from the modules UI or with ``drush en dkan_datastore_fast_import``. See :ref:`how to set up fast import here <fast_import_manager>`

Step 2: Configure the import option settings for proper parsing
***************************************************************

Adjust the defaults if necessary.

:delimiter: the character that separates the values in your file.
:quote: the character that encloses the fields in your file.
:escape: the character used to escape other characters in your file.

Step 3: Import
**************

Click the "Import" button at the bottom of the page. Most files will complete the import process right away. Larger files will be processed in 'chunks' during cron runs. Be sure you have [cron](https://www.drupal.org/docs/7/setting-up-cron/overview) running at regular intervals.

Once the import is complete the Data Importing state will display 'done'. The Records Imported should match the number of rows in your file.


Datastore API
---------------
Your data is now available via the Datastore API! For more information, see the :doc:`Datastore API page <../apis/datastore-api>`.

Click the "Data API" button at the top of the resource screen for specific instructions.


Dropping the Datastore
----------------------

To remove all records from the datastore:

1. Visit the resource page.
2. Click the "Manage Datastore" button.
3. Click the "Drop" button.
4. Confirm by clicking the "Drop" button.


.. _fast_import_manager:

DKAN Fast Import Manager
------------------------
.. warning::
  The *FastImport* Manager only works with files hosted in the web server and with a properly configured mysql client and server.

DKAN provides a second manager: *FastImport*.

This manager allows the importing of huge CSV files into the datastore at a fraction of the time it would take using the regular import.

Requirements
************

- A MySQL / MariaDB database
- MySQL database should support `PDO::MYSQL_ATTR_LOCAL_INFILE` and `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` flags.
- Cronjob or similar to execute periodic imports.

.. note::

  Because of the above requirements, which may not be available on all hosting environments, this module is *disabled* by default in DKAN.

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
*****

To import a resource using Fast Import, follow the instructions previously given in *"Importing Resources"*.

Troubleshoot
************

- If you get an error like

.. code-block:: php

  SQLSTATE[28000]: invalid authorization specification: 1045 access denied for user
  'drupal'@'%' (using password: yes)

you will need to grant FILE permissions to your MYSQL user. To do so use this command: ``GRANT FILE ON *.* TO 'user-name'``


Important notes when upgrading DKAN to 7.x-1.16 from previous versions
----------------------------------------------------------------------
.. warning::
  Be sure to read the following breaking changes if you have code that depends on the datastore API.

Field names
***********

Prior to the DKAN 7.x-1.16 release, the datastore field names matched the column headers exactly as they
were in the CSV file used to create the datastore. This has changed in the 7.x-1.16 version of
the Datastore, now the field names are transformed into lower case and spaces are replaced with underscores.
For example, if your CSV file has a column name titled ``School Year``, after importing the file
to the datastore you will need to access it using ``school_year`` as the field name.

Empty values
************

On previous versions of DKAN, when there was a cell with an empty value in a CSV
file, that value would be imported as NULL into the datastore. After importing a file
to the datastore with DKAN 7.x-1.16 or later, those empty values are kept as empty strings
and not as NULL values.

Use of feeds
************

Use of the feeds module as a method for importing data into the datastore has been deprecated in DKAN 7.x-1.16.
It is important that if you have any implementations that still rely on feeds, you will need to refactor that
code to use the new 'Simple Import', or add the patched version of feeds from 7.x-1.15.5 to your
`/src/make/drupal.make` file.

Also, it is important to note that when you upgrade from a previous version of DKAN, DKAN Datastore Fast Import
will not be enabled by default. If you were previously using that option you will need to re-enable the module.

DKAN Datastore tables
*********************

On previous versions of DKAN, the import process was done via the feeds module, which produced
tables with the field `feeds_flatstore_entry_id` as the primary key. Starting in 7.x-1.16, 
the feeds module has been replaced by **DKAN Datastore Simple Import**, and the new primary key for the DKAN datastore tables 
is a serial field called `entry_id`. 

NOTE: If you are upgrading, existing datastore tables will NOT be updated automatically. 

You have two options:

1. You can drop the datastore for each resource and re-import: this will delete the corresponding table and importing will create the table using the new schema.
2. Create a hook update in a custom module and add the following code:

.. code-block:: php

  $tables = db_query("show tables like 'dkan_datastore_%'")->fetchAll();
  foreach($tables as $key => $value) {
    $table_name = $value->{'Tables_in_drupal (dkan_datastore_%)'};
    db_drop_primary_key($table_name);
    db_drop_field($table_name, 'feeds_flatstore_entry_id');
    db_add_field($table_name, 'entry_id',
      [
        'type' => 'serial',
        'not null' => TRUE,
        'unsigned' => TRUE,
      ],
      ['primary key' => ['entry_id']]
    );
  }

This code will look for all of the dkan_datastore tables in your database, drop the primary key,
drop the field `feeds_flatstore_entry_id` and add the new field `entry_id` as the primary key
for each table.
