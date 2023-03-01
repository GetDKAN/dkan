# How to add indexes to your datastore tables {#guide_adding_indexes}

Indexes can be defined within the data dictionary form, or via the API. You must first define any column you want to index under Dictionary Fields. Then under Dictionary Indexes, you will list the field, a length (default is 50), the index type (index, or fulltext), and a description for reference.

In the example below we are defining two fields, and adding a standard index for the first field and a fulltext index for the second.

```http
POST http://mydomain.com/api/1/metastore/schemas/data-dictionary/items
Authorization: Basic username:password

{
    "title": "sample indexes",
    "data": {
        "fields": [
            {
                "name": "sample_id",
                "title": "ID",
                "type": "integer"
            },
            {
                "name": "description",
                "title": "Description",
                "type": "string"
            }
        ],
        "indexes": [
            {
              "fields": [
                {
                  "name": "sample_id",
                  "length": 15
                }
              ],
              "type": "index",
              "description": "idx1"
            },
            {
              "fields": [
                {
                  "name": "description"
                }
              ],
              "type": "fulltext",
              "description": "idx2"
            }
        ]
    }
}
```
