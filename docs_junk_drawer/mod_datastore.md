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
If your server's resources are limited you can adjust the default number of rows returned with the configuration page located at `admin/config/dkan/sql-endpoint`.
