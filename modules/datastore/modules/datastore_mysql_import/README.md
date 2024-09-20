# DKAN Datastore MySQL Import

This module will import CSVs into the DKAN datastore using MySQL's native LOAD DATA function, similar to running a `mysqlimport` command. It is over 5000% faster than DKAN's default batch datastore importer, but **requires** a MySQL database and that the Drupal DB user have permissions to use
`LOAD DATA LOCAL INFILE`.

To use, simply enable _datastore_mysql_import_ and clear your cache.

## Differences in behavior with the default DKAN importer.

* Any "blank" rows in your data file, including carriage returns, will be imported into the datastore as empty rows. The default importer will ignore these rows.
* If you have column headings exceeding 64 characters in your data file, these headings will be truncated to a max of 64 characters with the last 4 characters containing a hash value to insure uniqueness. This is the same for both importers as the character limit is from MySQL. However, if you already have imported data with one importer, and switch to the other importer, the hash values will be different. This may disrupt established queries depending on the previous header values.
* If your data includes a field containing the literal word NULL, it will be interpreted as empty unless you enclose it with quotes to be interpreted as a string.
