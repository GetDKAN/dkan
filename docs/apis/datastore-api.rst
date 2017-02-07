#############
Datastore API
#############

DKAN offers a Datastore API as a custom endpoint for the Drupal Services module.

This API is designed to be as compatible as possible with the `CKAN Datastore API <http://ckan.readthedocs.org/en/latest/maintaining/datastore.html>`_.

Requests can be sent over HTTP. Data can be returned as JSON, XML, or JSONP. The Datastore API supports both simple GET parameters and POST requests containing a JSON object specifying one or multiple queries.

*****************
Datastore API URL
*****************

Datastores can be queried at:  ``/api/action/datastore/search``

The default return format is XML. JSON can be retrieved with ``.json`` at the end::

  /api/action/datastore/search.json

...as can JSONP or making XML more explicit::

  /api/action/datastore/search.jsonp
  /api/action/datastore/search.xml

******************
Request Parameters
******************

:resource_id: id (string) or ids (array) of the resource to be searched against.
:filters: array or string of matching conditions to select
:q: full text query
:offset: offset this number of rows
:limit: maximum number of rows to return (default: 100)</li>
:fields: array or comma-separated string of fields to return (default: all fields in original order)
:sort: comma-separated field names with ordering
:join: array of fields to join from multiple tables

Parameter Format
================

While the above can be passed as simple GET parameters (i.e. ``?offset=1&limit=10``),  queries that join multiple tables require an extended syntax on some fields, following the pattern::

  param_name[resource_alias][field_name]=value,value1

Even in a join query, this syntax will not be necessary for all parameters. For example, if you need to limit the number of records then you need to use the limit parameter. However it doesn't make sense to specify an alias or a field in such case, even if you are submitting a join query. See below for examples.

*************
Return Values
*************

:fields: list of fields/columns and metadata
:offset: query offset value
:limit: query limit value
:count: number of total matching records
:records: list of matching results


********
Examples
********

The following is a simple example with two resources that contain 4 records each.

**Resource 1** (UUID: ``d2142282-9838-4cca-972f-f1741410417b``) **:**

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

**Resource 2** (UUID: ``d3c099c6-1340-4ee5-b030-8faf22b4b424``) **:**

+---------+-----------+----+------------+
| country | squarekm  | id | timestamp  |
+---------+-----------+----+------------+
| US      | 9,629,091 |  1 | 1359062713 |
+---------+-----------+----+------------+
| CA      | 9,984,670 |  2 | 1359062713 |
+---------+-----------+----+------------+
| AR      | 2,780,400 |  3 | 1359062713 |
+---------+-----------+----+------------+
| JP      | 377,930   |  4 | 1359062713 |
+---------+-----------+----+------------+

Simple query example
====================

::

  /api/dataset/search?resource_id=d2142282-9838-4cca-972f-f1741410417b&filters[country]=AR,US&fields=country,population,timestamp&sort[country]=asc


Returns the country, population, and timestamp fields for US and AR from dataset 1 sorting by the country in ascending order.

Text Search
===========

Paths with the 'query' argument will search the listed fields within the dataset.

::

/api/dataset/search?resource_id=d2142282-9838-4cca-972f-f1741410417b&fields=country,population&query=US


This will return the country and population from US.

Joins
=====

If you wish to query multiple tables, indicate the table as an array key in the following fields:

::

  /api/dataset/search?resource_id[pop]=d2142282-9838-4cca-972f-f1741410417b&resource_id[size]=d3c099c6-1340-4ee5-b030-8faf22b4b424&filters[pop][country]=US,AR&join[pop]=country&join[size]=country

Returns the country, population, squarekm and id for US and AR from datasets 11 and 13.

Multiple queries
================

Sometimes you may want to do mutiple queries in one request. This use-case has come up particularly when building `dashboard applications <https://github.com/NuCivic/react-dashboard>`_ off the Datastore API. You can post a json object to ``/api/action/datastore/search.json`` with all the queries to perform in a single request.

Example request
---------------

.. code-block:: json

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
--------

.. code-block:: json

  {
    "my_query": {
      "help": "Search a datastore table. :param resource_id: id or alias of the data that is going to be selected.",
      "success": true,
      "result": {
        "fields": [
          {
            "id": "name",
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
            "name": "Alabama",
            "state_id": "1",
            "feeds*flatstore*entry_id": "1",
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
            "feeds*flatstore*entry_id": "1",
            "timestamp": "1466036208",
            "feeds*entity*id": "12"
          }
        ]
      }
    }
  }
