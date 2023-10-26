API Examples
=============

This section provides examples for developers who want to write external code that can interact with DKAN catalogs and
the data within, or utilize core functionality without engaging the web interface.

Identifiers
-----------

Every dataset has an identifier, a.k.a. UUID. The dataset identifier is used in the dataset URL (/dataset/datasetID),
and does not change when edits are made to the dataset. So if you are creating an automated
script it is better to use APIs that utilize the dataset ID and the index of the distribution.
Commonly datasets have a single distribution, in this case, the index will always be 0. For datasets that have several
distributions, the index corresponds to the order in which they were entered. The first distribution would be 0, the
second would be 1, and so on.

Distributions also have their own identifiers. The distribution identifier will
change each time there is a change to the distribution resource or any change to
:doc:`triggering properties <guide_datastore_settings>`. You can get the distribution identifier by viewing
the dataset metadata API ``/api/1/metastore/schemas/dataset/items/{datasetID}?show-reference-ids``
or by running this drush command, passing in the dataset ID:

    .. prompt:: bash $

      drush dkan:dataset-info {datasetID}

In the following examples, we will use {datasetID} to represent the dataset identifier, {distributionID} to
represent the distribution identifier, and {index} to represent which distribution is being referenced on a
dataset, the index starts at 0.

Datastore: Query Data
---------------------

The API allows read operations of datasets without authentication via a browser (GET requests) or with an HTTP Client.

How to run a simple query against a dataset.
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To pull dataset data via the API, the recommended method is to use the datastore query endpoint.

    .. http:post:: https://{site-domain}/api/1/datastore/query/{datasetID}/{index}


The datastore query endpoint takes two arguments: the dataset ID, which can be obtained from the URL of
the dataset and does not change between data refreshes, and the index of the distribution.

To apply conditions, include them in the request body. For example, to return all results where the
product_category_code column was equal to '022':

    .. sourcecode:: http

      POST https://{site-domain}/api/1/datastore/query/{datasetID}/{index} HTTP/1.1

       {
         "conditions": [
           {
             "property": "product_category_code",
             "value": "022",
             "operator": "="
           }
         ]
       }

You can convert the above json into to a query string to be used with a simple GET request by pasting the request body above
into a service such as `Convert Online <https://www.convertonline.io/convert/json-to-query-string>`_.
Doing so results in the following query string:

    .. code-block::

      conditions[0][property]=product_category_code&conditions[0][value]=022&conditions[0][operator]==

The query string can be appended to the datastore query endpoint url and return results directly in the browser.
i.e.

    .. sourcecode:: http

      GET https://{site-domain}/api/1/datastore/query/{datasetID}/0?conditions[0][property]=product_category_code&conditions[0][value]=022&conditions[0][operator]== HTTP/1.1

Additional query options include:

  -  **properties**: which columns to return in results; defaults to all columns if not specified
  -  **sorts**: how to order the results; defaults to row ID in the database if not specified
  -  **limit**: number of results to return; only limited by the overall row limit (generally 500) if not specified
  -  **offset**: how many rows to skip before displaying results; defaults to zero if not specified

See below for examples of how to use these options.

How to return result sets larger than the row limit (usually 500).
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The number of results per query are limited for performance reasons with the default maximum number being 500 rows.
However, you can pull more than 500 results by doing the requests in batches of 500 with an offset of 500, 1000,
etc for each subsequent pull.

For example, when using the datastore query endpoint against a result set that has a row limit of 500:

    .. sourcecode:: http

      GET https://{site-domain}/api/1/datastore/query/{datasetID}/{index} HTTP/1.1

The above will return the first 500 rows of the dataset along with the total number of results labeled as 'count'.

If the count is between 1000 and 1500 rows, you could pull all the results in 3 batches, each with a maximum of
500 rows, by using the offset parameter. When not passed as a parameter, offset defaults to zero.

    .. code-block::

      https://{site-domain}/api/1/datastore/query/{datasetID}/{index}
      https://{site-domain}/api/1/datastore/query/{datasetID}/{index}?offset=500
      https://{site-domain}/api/1/datastore/query/{datasetID}/{index}?offset=1000

If using an HTTP Client, you can set the offset in the request body:

    .. sourcecode:: http

      POST https://{site-domain}/api/1/datastore/query/{datasetID}/{index} HTTP/1.1

       {
         "offset":500
       }

How to run a query against multiple tables with JOIN.
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This query will require the distribution identifier (uuid) to define the resource ids
in your query. See the Identifiers section above.

Define the tables you want to query and give each an alias under "resources".
List the properties you want returned, if the properties you want returned are
using different column headings (in this example "postal_code" and "zip"),
set up an alias to collect the values to a single property in the results.
Add any conditions you like to filter the data. Then add the join, defining
the property and value to match.

  **Create a join:**

  .. sourcecode:: http

    POST https://{site-domain}/api/1/datastore/query HTTP/1.1
    content-type: application/json

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
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Make sure that you have created :doc:`fulltext indexes <guide_indexes>` for the columns in the table.
The default table alias is "t", if you are only querying one table, you can
leave this line out "resource":"t".
Below would give you the first 5 results for service_type = "General" AND
matches any word that starts with "knee" OR equals "ankle" in either the
description or notes column.

    .. sourcecode:: http

      POST https://{site-domain}/api/1/datastore/query/{datasetID}/0 HTTP/1.1
      content-type: application/json

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

Metastore: Search
-----------------

.. http:get:: /api/1/search

The DKAN search endpoint can be used to return a filtered list of datasets - for
example all datasets tagged with a given keyword or where the title and/or description contain a given search term.

Filter options are passed as query parameters to the endpoint. For example, to find all the datasets with a theme of
'Supplier directory', you would use:

    .. sourcecode:: http

      GET https://{site-domain}/api/1/search?theme=Supplier%20directory HTTP/1.1

Note that '%20' is inserted for the spaces between words in a theme or keyword. Separate multiple query parameters with
ampersands.

The default result limit - if page-size is not provided - is 10. The API will not return more than 100 results at one
time. If you want the next batch of results, you can increment the page number by passing the 'page' query parameter.
E.g.

    .. sourcecode:: http

      GET https://{site-domain}/api/1/search?page-size=100&page=2 HTTP/1.1

Search endpoint options include:

  -  **page-size**: how many results to return; maximum number supported is 100; defaults to 10 if not specified
  -  **page**: which page of results (divided by page-size) to return; defaults to 1 if not specified
  -  **theme**: return datasets associated with a given theme
  -  **keyword**: return datasets associated with a given keyword/tag
  -  **fulltext**: return datasets that contain a given text string in the title or description of the dataset

Metastore: Create, Edit, Delete
-------------------------------

Some API functions require authorization. Any user that has dataset CRUD permissions will be able to perform those
functions via the API.

.. _authentication:

Authentication
^^^^^^^^^^^^^^

Drupal uses Basic Authentication, this is a method for an HTTP user agent (e.g., a web browser)
to provide a username and password when making a request.

When employing Basic Authentication, users include a base 64 encoded string in the Authorization
header of each request they make. The string is used by the request's recipient to verify
users' identity and rights to access a resource.

  -  Key = Authorization
  -  Value = Basic + base 64 encoding of a user ID and password separated by a colon

You can obtain the base 64 encoded string from the command line by running the following (replace admin:admin with your username:password):

.. code-block::

    echo -n 'admin:admin' | base64
    // Result
    YWRtaW46YWRtaW4=

    // When using basic auth via REST API
    content-type: application/json
    Authorization: Basic YWRtaW46YWRtaW4=

How to set the moderation state through the API.
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The available moderation states are: draft, published, hidden, orphaned, and archived.
Learn more about :term:`Moderation State` here.

1. Get the current moderation state and confirm there is at least one revision.


    .. sourcecode:: http

      GET https://{site-domain}/api/1/metastore/schemas/dataset/items/{datasetID}/revisions HTTP/1.1


2. Let's say the returned result says the revision is published "true" and state "published", here is how we change the state to hidden.

    .. sourcecode:: http

       POST https://{site-domain}/api/1/metastore/schemas/dataset/items/{datasetID}/revisions HTTP/1.1

       Authorization: Basic [base64 encoded 'user:password' string]

       {
           "state": "hidden",
           "message": "Testing state change"
       }


3. Run the GET again to confirm the state is now "hidden".


