# DKAN Datastore MySQL Import

This module will import CSVs into the DKAN datastore using MySQL's native LOAD DATA function, similar to running a `mysqlimport` command. It is over 5000% faster than DKAN's default batch datastore importer, but **requires** a MySQL database and that the Drupal DB user have permissions to use
`LOAD DATA LOCAL INFILE`.

To use, simply enable _datastore_mysql_import_ and clear your cache.
