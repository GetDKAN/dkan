# DKAN Datastore MySQL Import

This module will import CSVs into the DKAN datastore using MySQL's native LOAD DATA function, similar to running a `mysqlimport` command. It is over 5000% faster than DKAN's default batch datastore importer, but **requires** a MySQL database and that the Drupal DB user have permissions to use
`LOAD DATA LOCAL INFILE`.

To use, simply enable _datastore_mysql_import_ and clear your cache.

## Differences in behavior with the default DKAN importer.

* Any "blank" rows in your data file, including carriage returns, will be imported into the datastore as empty rows. The default importer ignores any blank rows.  To have the Mysql Importer behave the same as the default importer and ignore empty rows, enable the option on this page /admin/dkan/datastore/mysql_import
* If you have column headings exceeding 64 characters in your data file, these headings will be truncated to a max of 64 characters with the last 4 characters containing a hash value to insure uniqueness. This is the same for both importers as the character limit is from MySQL. However, if you already have imported data with one importer, and switch to the other importer, the hash values will be different. This may disrupt established queries depending on the previous header values.
* If your data includes a field containing the literal word NULL, it will be interpreted as empty unless you enclose it with quotes to be interpreted as a string.

## Settings

MySQL Import settings can be found at ``/admin/dkan/datastore/mysql_import``.

In some cases, creating tables may fail because the number of columns and length
of column names is too long. If you encounter this issue, you can try to check
the "Disable strict mode for creating/altering MySQL tables" setting. This will
temporarily disable innodb_strict_mode via session variables, allowing
the table to be created. This setting should be used with caution. 

If you are using MySQL 8.0+, you will need the `SESSION_VARIABLES_ADMIN`
permission to be set for the database user in order to disable strict mode.