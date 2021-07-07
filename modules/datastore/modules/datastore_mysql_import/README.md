# DKAN Datastore MySQL Import

This is an **expiremental** module to import CSVs into the DKAN datastore using 
MySQL's native LOAD DATA function, similar to running a `mysqlimport`
command. It is over 5000% faster than DKAN's default datastore importer, but
requires a MySQL database and that the Drupal DB user have permissions to use
`LOAD DATA LOCAL INFILE`.

Better test coverage, and a refactored import system that is more easily
swappable/extensible coming soon!

To use, simply enable datastore_mysql_import and clear your cache.