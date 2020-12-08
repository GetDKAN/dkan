@page datastore Datastore

The DKAN **Datastore** module provides integration with the [datastore](https://github.com/GetDKAN/datastore) library. With it you are able to parse CSV files and save the data in database tables. This allows users to query the data through a public API. Note that the CSV must be in UTF-8 format to parse correctly.

When a dataset is added to the catalog, the mimetype value of each distribution object is checked to determine if the distribution is a CSV file or not. CSV distributions will be added to the import queue to be parsed and imported into the datastore.

The import will happen in batches during cron runs. The import will itterate over the file, if it does not finish, the job will be returned to the queue. Be sure to have cron running on a regular basis so that large file imports will complete. If there is a change to the metadata it will trigger a new import to the datastore.

You can manually import file data into the datastore via drush with the identifier of the distribution. Use the [API](guide-dataset-api.html#identifiers) to get the identifier of the file you want to import.

## Drush Commands

| Command | Args | Notes |
| -- | -- | -- |
| dkan-datastore:import | $uuid | import file to the datastore |
| dkan-datastore:drop   | $uuid | drop the datastore table |
| dkan-datastore:list   |       | lists available datastores and status of the import |
| queue:run | datastore_import | process all of the datastore import jobs |

## Datastore API

Your data is now available via the Datastore API!
`api/1/datastore/sql?query=`

**Parameters**
- resource_identifier (mixed) – identifier of the resource to be searched against
- properties (mixed) – array or string of matching conditions to select
- conditions (string) - retrieve only objects with properties of certain values
- sort (string) – comma separated field names with ordering
- order (string) – comma separated field names
- limit (int) – maximum number of rows to return
- offset (int) – offset this number of rows
- count (boolean) - mark query as a count query

**Example**

```
http://domain.com/api/1/datastore/sql?query=[SELECT * FROM ${resource_identifier}][WHERE state = "OK"][ORDER BY county ASC][LIMIT 5 OFFSET 100]
```

**Configuration**
If your server's resources are limited you can adjust the default number of rows returned with the configuration page located at `admin/config/dkan/sql_endpoint`.
