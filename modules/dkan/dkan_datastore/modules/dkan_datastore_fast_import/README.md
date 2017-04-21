## DKAN Fast Import

DKAN Fast Import allows import huge CSV files into the DKAN Datastore in a fraction of the time it would take using the regular import.

## How it works

When a CSV it's imported by using the regular import this is what it happens under-the-hood:

1. PHP interpreter read the file and CSV line by line from the disk
2. Each time a line it's parsed it sends a query to the database
  3. The database receive the query and parses it
  4. The database creates a query execution plan 
  5. The database excecutes the plan (i.e. inserts a new row)

It is important to note that steps 3,4,5 are executed as many times as rows in the CSV.

DKAN Fast Import was designed to remove as many steps as possible from the previous list.

In contrast DKAN Fast Inport performs the following steps:

1. PHP interpreter sends a LOAD DATA query to the database
2. The database receive the query and parses it
4. The database reads and imports the whole file in a table

Note only one query its executed. In consequence the amount of time required to import a big datasets it's drastically reduced.

It's hard to perceive an improvement on small files (less than 100 rows) but as file size grows you'll note the differece.

## Requirements
- A MySQL / MariaDB database
- MySQL database should support `PDO::MYSQL_ATTR_LOCAL_INFILE` and `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` flags.
- Cronjob or similar to execute periodic imports.
- Drush

## Setup
- Inside your settings.php add this to your database configuration:
```
 array (
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
 ),
```
- Go to **/admin/modules**, turn on DKAN Datastore Fast Import and press **Save configuration**. Alternatively you can use drush to enable this module: `drush en dkan_datastore_fast_import`.
- Make sure this message **did not** show up at the top of the page: `Required PDO flags for dkan_datastore_fast_import were not found. This module requires PDO::MYSQL_ATTR_LOCAL_INFILE and PDO::MYSQL_ATTR_USE_BUFFERED_QUERY`
- Set up this command to run periodically using a cronjob or similar `drush queue-run dkan_datastore_queue`

## Import a resource using DKAN Fast Import
- Create a resource using a CSV file (**node/add/resource**) or edit an existing one.
- Click on **Manage Datastore**
- Make sure **No imported items.** legend shows up.
- Check **Use Fast Import** checkbox
- Press **import**

## Configuration
To configure how fast import behaves go to **admin/dkan/datastore**. 

There are 3 basic configurations that controls the **Use fast import** checkbox in the **Manage Datastore** page: 

### Use regular import as default
**Use Fast Import** checkbox it's uncheked by default so files are imported using the normal dkan datastore import. However you can still enable fast import for any resource by clicking in that checkbox.

### Use fast import as default (LOAD DATA)
**Use Fast Import** checkbox it's cheked by default so files are imported using DKAN Fast Import. Like the previous setting, you can unchek **Use Fast Import** to use the normal import instead.

### Use fast import for files with a weight over
From this setting you obtain a refined control about when **Use Fast Import** should be checked.
    - **File size threshold:** **Use Fast Import** will be checked for all the files over the threshold. A size expressed as a number of bytes with optional SI or IEC binary unit prefix (e.g. 2, 3K, 5MB, 10G, 6GiB, 8 bytes, 9mbytes)
    - **Load Data Statement:** some hostings doesn't support `LOAD DATA LOCAL INFILE`. If that's your case you can switch to `LOAD DATA INFILE`.
    - **Queue Filesize Threshold:** If a file it's small enough you can avoid to wait until the drush queue it's ran by configuring this threshold. All the files with a size under this value won't be enqueued and will be imported during the request. The time to perform the import should fit into the php request timeout. Otherwise your import could be aborted.

## Why DKAN Fast Import is not enabled by default?
This module relies on some specific hosting configurations. Implement this tweaks it's not always an option. 

Also, regular imports allow module developers to hook into the import process to perform any kind of tasks like data transformations, geolocalization, etc.
