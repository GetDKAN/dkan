API Examples
=============

Each dataset page displays live examples for basic API calls for that dataset.
This guide will show additional options for more advanced usage.

How to set the moderation state through the API.
------------------------------------------------

The available moderation states are: draft, published, hidden, orphaned, and archived.
Learn more about :term:`Moderation State` here.

1. Get the current moderation state and confirm there is at least one revision.


    .. code-block::

      GET https://[site-domain]/api/1/metastore/schemas/dataset/items/[identifier]/revisions


2. Let's say the returned result says the revision is published "true" and state "published", here is how we change the state to hidden.

    .. code-block::

       POST https://[site-domain]/api/1/metastore/schemas/dataset/items/[identifier]/revisions HTTP/1.1

       Authorization: Basic [base64 encoded 'user:password' string]

       {
           "state": "hidden",
           "message": "Testing state change"
       }


3. Run the GET again to confirm the state is now "hidden".


How to run a query against multiple tables with JOIN.
-------------------------------------------------------

This query will require the distribution_uuid. You can get this id by viewing
the dataset metadata API ``/api/1/metastore/schemas/dataset/items/[datasetID]?show-reference-ids``
or running this drush command, passing in the dataset ID:

    .. code-block::

      drush dkan:dataset-info [datasetID]

Define the tables you want to query and give each an alias under "resources".
List the properties you want returned, if the properties you want returned are
using different column headings (in this example "postal_code" and "zip"),
set up an alias to collect the values to a single property in the results.
Add any conditions you like to filter the data. Then add the join, defining
the property and value to match.

    .. code-block::

      POST https://[site-domain]/api/1/datastore/query/[identifier]/0 HTTP/1.1

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
                   "on": [
                     {
                       "resource": "a",
                       "property": "id"
                     },
                     {
                       "resource": "b",
                       "property": "id"
                     }
                   ]
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

