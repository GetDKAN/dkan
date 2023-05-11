How to add indexes to your datastore tables
============================================
.. _guide_indexes:

To improve the performance of datastore queries, you should add indexes.

Indexes can be defined within the data dictionary form, or via the API.
You must first define any column you want to index under Dictionary Fields.
Then under Dictionary Indexes, you will list the field, a length (default is 50),
the index type (index, or fulltext), and a description for reference.

Any time the datastore is updated, the data dictionary definitions will be reapplied when the post_import queue runs.

API
---

In the example below we are defining two fields, and adding a standard index for the first field and a fulltext index for the second.

.. code-block::

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

GUI
---

  1. Log in as an administrator.
  2. From the DKAN menu, select Data Dictionary -> Create.
  3. Enter a name for your data dictionary that will serve as its identifier.
  4. Define the fields in the 'Dictionary Fields' section.
  5. Define the indexes in the 'Dictionary Indexes' section.
  6. Enter the column name into the 'Name' field.
  7. Use the 'Add one' button to add more fields if desired.
  8. Select the index type: standard or fulltext
  9. Add a description to name the index if desired.
  10. Click the “Save” button.

