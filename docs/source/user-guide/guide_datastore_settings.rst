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

Degraded service mode
----------------------
Degraded service mode blocks requests to the datastore API that contain conditions,
joins, groupings, sorts, and offsets. The datastore API allows people to make potentially
very complex and expensive queries against the database. Partiularly when a DKAN
site become the target of a large amount of bot traffic, even when not intentionally
malicious, MySQL servers can become overwhelmed. Placing the site in degraded
service mode can help the server to "cool down", and return to a state where
the database can be responsive, without having to take the site offline entirely.

Toggle degraded service mode on the datastore settings page, or use the Drush command
``drush dkan:datastore:degraded-mode 1`` to turn on, and 
``drush dkan:datastore:degraded-mode 0`` to turn off. Run 
``drush dkan:datastore:degraded-mode`` with no arguments to check the current status.
