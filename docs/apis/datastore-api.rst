Datastore API
=============

DKAN offers a Datastore API as a custom endpoint for the Drupal Services
module.

This API is designed to be as compatible as possible with the [CKAN
Datastore API](http://ckan.readthedocs.org/en/latest/maintaining/datastore.html).

Parameters
----------

-  **resource\_id** (*mixed*) – id (string) or ids (array) of the
   resource(s) to be searched against.
-  **filters** (*mixed*) – array or string of matching conditions to
   select
-  **q** (*string*) – full text query
-  **offset** (*int*) – offset this number of rows
-  **limit** (*int*) – maximum number of rows to return (default: 100)
-  **fields** (*array or comma separated string*) – fields to return
   (default: all fields in original order)
-  **sort** (*string*) – comma separated field names with ordering
-  **join** (*array*) – array of fields to join from multiple tables
-  **group\_by** (*array*) – array of fields to group by

Aggregation functions
---------------------

-  **sum** (*string*) – field to compute the sum
-  **avg** (*string*) – field to compute the average
-  **min** (*string*) – field to compute the maximum
-  **max** (*string*) – field to compute the minimum
-  **std** (*string*) – field to compute the standard deviation
-  **variance** (*string*) – field to compute the variance

URL format
----------

Parameters passed by URL share a common format:

::

   param_name[resource_alias][field_name]=value,value1

-  **param\_name**: the param you are using (e.g. offset)
-  **resource\_alias(optional)**: an alias to reference an specific
   resource in further params.
-  **field\_name(optional)**: a field name used by the param name.
-  **value**: a list of values divided by commas

Note that ``resource_alias`` and ``field_name`` arguments are optional
and depend on what you want to query. For example, if you need to limit
the number of records, you need to use the limit parameter. However, it
doesn't make sense to specify an alias or a field in such a case. You
only need to provide the number of records you need to retrieve:

::

    ...&limit=5

There is one exception: Even when the ``sort`` parameter shares the
above syntax, it also accepts an alternative format:

::

    ...&sort=field1,field2 desc

Multiple queries
----------------

Sometimes you want to do mutiple datastore queries in one network
request (e.g., to feed a data dashboard). In that case you can post a
JSON object to http://EXAMPLE.COM/api/action/datastore/search.json with
all the queries to perform.

The request body should have a format similar to this:

Request body
~~~~~~~~~~~~

.. code:: javascript

    {
      "my_query": {
        "resource_id": {
          "states": "d2142282-9838-4cca-972f-f1741410417b",
          "gold_prices":"d3c099c6-1340-4ee5-b030-8faf22b4b424"
        },
        "limit": 5
      },
      "my_query1": {
        "resource_id": {
          "gold_prices": "d3c099c6-1340-4ee5-b030-8faf22b4b424"
        },
        "limit": 5
      }
    }

Response
~~~~~~~~

.. code:: javascript

    {
      "my_query": {
        "help": "Search a datastore table. :param resource_id: id or alias of the data that is going to be selected.",
        "success": true,
        "result": {
          "fields": [
            {
              "id": "nombre",
              "type": "text"
            },
            {
              "id": "state_id",
              "type": "int"
            }
          ],
          "resource_id": {
            "states": "d2142282-9838-4cca-972f-f1741410417b",
            "gold_prices": "d3c099c6-1340-4ee5-b030-8faf22b4b424"
          },
          "limit": 1,
          "total": 5,
          "records": [
            {
              "nombre": "Alabama",
              "state_id": "1",
              "feeds*flatstore_entry*id": "1",
              "timestamp": "1466096874",
              "feeds*entity*id": "13"
            }
          ]
        }
      },
      "my_query1": {
        "help": "Search a datastore table. :param resource_id: id or alias of the data that is going to be selected.",
        "success": true,
        "result": {
          "fields": [
            {
              "id": "date",
              "type": "datetime"
            },
            {
              "id": "price",
              "type": "float"
            },
            {
              "id": "state_id",
              "type": "int"
            }
          ],
          "resource_id": {
            "gold_prices": "d3c099c6-1340-4ee5-b030-8faf22b4b424"
          },
          "limit": 1,
          "total": 748,
          "records": [
            {
              "date": "1950-01-01",
              "price": "34.73",
              "state_id": "1",
              "feeds*flatstore_entry*id": "1",
              "timestamp": "1466036208",
              "feeds*entity*id": "12"
            }
          ]
        }
      }
    }7

Response formats
----------------

Requests can be sent over HTTP. Data can be returned as JSON, XML, or
JSONP. To retrieve data in a different format, change the extension in
the url.

Instead of using this::

    http://EXAMPLE.COM/api/action/datastore/search.json

Use this::

    http://EXAMPLE.COM/api/action/datastore/search.xml

Or this::

    http://EXAMPLE.COM/api/action/datastore/search.jsonp

Limitations
-----------

-  The ``q`` parameter doesn't work in combination with the ``join``
   parameter.
-  Filters don't work with float (decimals) values

Examples
--------

The following is a simple example with two resources that contain four
records each. Note that the resource ``id`` would be a UUID not
single digit number in real scenario.

**Resource 1:**

+---------+-------------+----+------------+
| country | population  | id | timestamp  |
+=========+=============+====+============+
| US      | 315,209,000 |  1 | 1359062329 |
+---------+-------------+----+------------+
| CA      | 35,002,447  |  2 | 1359062329 |
+---------+-------------+----+------------+
| AR      | 40,117,096  |  3 | 1359062329 |
+---------+-------------+----+------------+
| JP      | 127,520,000 |  4 | 1359062329 |
+---------+-------------+----+------------+

**Resource 2:**

+---------+-----------+----+------------+
| country | squarekm  | id | timestamp  |
+=========+===========+====+============+
| US      | 9,629,091 |  1 | 1359062713 |
+---------+-----------+----+------------+
| CA      | 9,984,670 |  2 | 1359062713 |
+---------+-----------+----+------------+
| AR      | 2,780,400 |  3 | 1359062713 |
+---------+-----------+----+------------+
| JP      | 377,930   |  4 | 1359062713 |
+---------+-----------+----+------------+

Simple query example
~~~~~~~~~~~~~~~~~~~~

::

    http://EXAMPLE.COM/api/dataset/search?resource_id=d3c099c6-1340-4ee5-b030-8faf22b4b424&filters[country]=AR,US&fields=country,population,timestamp&sort[country]=asc

Returns the country, population, and timestamp fields for US and AR from
dataset 1 sorting by the country in ascending order.

Text Search
~~~~~~~~~~~

Requests with the 'query' argument will search the listed fields within
the dataset::

    http://example.com/api/dataset/search?resource_id=d3c099c6-1340-4ee5-b030-8faf22b4b424&&fields=country,population&query=US

This will return the country and population from US.

Joins
~~~~~

If you wish to query multiple tables, indicate the table as an array key
in the following fields::

    http://example.com/api/dataset/search?resource_id[pop]=d3c099c6-1340-4ee5-b030-8faf22b4b424&resource_id[size]=d3c099c6-1340-4ee5-b030-8faf22b4b424&filters[pop][country]=US,AR&join[pop]=country&join[size]=country

Returns the ``country``, ``population``, ``squarekm`` and ``id`` for "US" and "AR" from
datasets 11 and 13.

Caching
~~~~~~~

GET and POST request are cached by Drupal. The params passed through the
request are used to create a cache id to store the data to be retrieved
on further requests.

Since Datastore API uses the Drupal cache system under the hood, the
Datastore API cache will be cleared at the same time as the rest of the Drupal cache. This
coule be when the cache is wiped manually, or when the cache lifetime ends.

All this options can be configured at
``admin/config/development/performance``
