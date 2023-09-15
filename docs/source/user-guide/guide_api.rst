API Examples
=============

When visiting a dataset page, you will see dataset specific API examples
for that dataset that do not require authentication.

This guide will show additional options for more advanced usage.

Authentication
--------------

Drupal uses Basic Authentication, this is a method for an HTTP user agent (e.g., a web browser)
to provide a username and password when making a request.

When employing Basic Authentication, users include an encoded string in the Authorization
header of each request they make. The string is used by the request's recipient to verify
users' identity and rights to access a resource.

  -  Key = Authorization
  -  Value = Basic + base 64 encoding of a user ID and password separated by a colon

Identifiers
-----------

Every dataset has an identifier, a.k.a. UUID. The dataset identifier is used in the dataset URL (/dataset/datasetID),
and does not change when edits are made to the dataset. So if you are creating an automated
script it is better to use APIs that utilize the dataset ID and the index of the distribution.
The index for the first distribution is 0, the index for the second distribution is 1, etc.

Distributions also have their own identifiers. The distribution identifier will
change each time there is a change to the distribution resource or any change to
:doc:`triggering properties <guide_datastore_settings>`. You can get the distribution identifier by viewing
the dataset metadata API ``/api/1/metastore/schemas/dataset/items/[datasetID]?show-reference-ids``
or by running this drush command, passing in the dataset ID:

    .. code-block::

      drush dkan:dataset-info [datasetID]


How to set the moderation state through the API.
------------------------------------------------

The available moderation states are: draft, published, hidden, orphaned, and archived.
Learn more about :term:`Moderation State` here.

1. Get the current moderation state and confirm there is at least one revision.


    .. code-block::

      GET https://[site-domain]/api/1/metastore/schemas/dataset/items/[datasetID]/revisions


2. Let's say the returned result says the revision is published "true" and state "published", here is how we change the state to hidden.

    .. code-block::

       POST https://[site-domain]/api/1/metastore/schemas/dataset/items/[datasetID]/revisions HTTP/1.1

       Authorization: Basic [base64 encoded 'user:password' string]

       {
           "state": "hidden",
           "message": "Testing state change"
       }


3. Run the GET again to confirm the state is now "hidden".


How to run a query against multiple tables with JOIN.
-------------------------------------------------------

This query will require the distribution identifier (uuid) to define the resource ids
in your query. See the Identifiers section above.

Define the tables you want to query and give each an alias under "resources".
List the properties you want returned, if the properties you want returned are
using different column headings (in this example "postal_code" and "zip"),
set up an alias to collect the values to a single property in the results.
Add any conditions you like to filter the data. Then add the join, defining
the property and value to match.

    .. code-block::

      POST https://[site-domain]/api/1/datastore/query HTTP/1.1

      {
        "resources": [
          {
            "id": "07eaa697-694d-5aa9-a105-1dad5509fc47",
            "alias": "a"
          },
          {
            "id": "2fde366a-7026-54bc-bda5-63b5435afbd0",
            "alias": "b"
          }
        ],
        "properties": [
          {
            "resource": "a",
            "property": "first_name"
          },
          {
            "resource": "a",
            "property": "last_name"
          },
          {
            "resource": "b",
            "property": "state"
          },
          {
            "resource": "b",
            "property": "county"
          },
          {
            "alias": "postal_code",
            "expression": {
              "operator": "*",
              "operands": [
                {
                   "resource": "a",
                   "property": "postal_code"
                },
                {
                  "resource": "b",
                  "property": "zip"
                }
              ]
            }
          }
        ],
        "conditions": [
           {
             "resource": "a",
             "property": "carrier",
             "value": "75573",
             "operator": "="
           }
        ],
        "joins": [
          {
            "resource": "b",
            "condition": {
              "resource": "a",
              "property": "mid",
              "operator": "=",
              "value": {
                "resource": "b",
                "property": "mid"
              }
            }
          }
        ]
      }

How to run a fulltext query on multiple columns.
------------------------------------------------

Make sure that you have created :doc:`fulltext indexes <guide_indexes>` for the columns in the table.
The default table alias is "t", if you are only querying one table, you can
leave this line out "resource":"t".
Below would give you the first 5 results for service_type = "General" AND
matches any word that starts with "knee" OR equals "ankle" in either the
description or notes column.

    .. code-block::

      POST https://[site-domain]/api/1/datastore/query/[identifier]/0 HTTP 1.1

       {
         "offset":0,
         "limit":5,
         "rowIds":true,
         "conditions":[
           {
             "resource":"t",
             "property":"service_type",
             "value":"General",
             "operator":"="
           },
           {
             "groupOperator":"or",
             "conditions": [
               {
                 "resource":"t",
                 "property":"description, notes",
                 "value":"knee*",
                 "operator":"match"
               },
               {
                 "resource":"t",
                 "property":"description, notes",
                 "value":"ankle",
                 "operator":"match"
               }
             ]
           }
         ],
         "sorts":[
           {
             "property":"decision_date",
             "order":"desc"
           }
         ]
       }

