How to configure datastore settings
===================================

Datastore settings can be found at ``/admin/dkan/datastore``

Rows limit
----------
This sets the maximum number of rows the datastore endpoints can return in a single request.
The default is 500 rows. Caution: setting too high can lead to timeouts or memory issues,
values above 20,000 not recommended. It is advised to run load tests to determine how high
you can safely set this value.

Use the *limit* and *offset* parameters to iterate through result sets that are larger than
the row limit when running queries against the datastore API.

Datastore triggering properties
-------------------------------
By default the only way trigger a "refresh" to an existing datastore is a change to the
data file name. If the data in your file is updated but file name remains the same, you
can select other "triggers" to ensure that the old datastore is dropped and a new one is generated.

For example, if you select "Last Update (modified)", any change to this value will add
jobs to the queue when the dataset is saved. These jobs will drop the existing datastore
table and generate a new datastore when cron runs or when the queue is run directly.

.. _mysql-import-settings:

MySQL Import settings
---------------------

The MySQL importer is an optional module that leverages MySQL's native LOAD DATA INFILE
functionality to import data into the datastore. This can be significantly faster than
the default DKAN importer.

MySQL Import settings can be found at ``/admin/dkan/datastore/mysql_import``.

In some cases, creating tables may fail because the number of columns and length
of column names is too long. If you encounter this issue, you can try to check
the "Disable strict mode for creating/altering MySQL tables" setting. This will
temporarily disable innodb_strict_mode via session variables, allowing
the table to be created. This setting should be used with caution. It may also
require the `SESSION_VARIABLES_ADMIN` permission to be set for the database user.