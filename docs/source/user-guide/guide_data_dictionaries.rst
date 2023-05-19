Data Dictionaries
=================

.. _guide_data_dictionaries:

What is the purpose of data dictionaries?
-----------------------------------------

A data dictionary describes the structure and content of data elements, provides guidance on interpretation, reduces data inconsistencies, and makes data easier to analyze.

How Data Dictionaries are Used
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

* **Documentation** - provide data structure details for users, developers, and other stakeholders
* **Communication** - equip users with a common vocabulary and definitions for shared data, data standards, data flow and exchange, and help developers gage impacts of schema changes
* **Application Design** - help application developers create forms and reports with proper data types and controls
* **Systems Analysis** - enable analysts to understand overall system design and data flow, and to find where data interact with various processes or components
* **Data Integration** - clear definitions of data elements provide the contextual understanding needed when deciding how to map one data system to another, or whether to subset, merge, stack, or transform data for a specific use
* **Decision Making** - assist in planning data collection, project development, and other collaborative efforts

In DKAN, data dictionaries are used as instructions for how to store the data in the database. When you add datasets to your catalog, any distribution with a csv file will be queued for import into the database as a datastore table. At this stage, everything is imported as strings. Once the import of the file has completed, a post import job is generated to apply the data dictionary to the datastore table. Without these instructions, date and numeric data will not sort as expected.

You can check how many jobs are in each queue by running `drush queue-list`. Be sure that cron is running often enough to process the jobs to completion.

Table Schema
------------

The structure of your data dictionary should follow `Frictionless Standards table schema <https://specs.frictionlessdata.io/table-schema/>`_.

.. code-block:: json

    {
      "title": "A human readable label",
      "data": {
       "fields": [
        {
          "name": "(REQUIRED) machine name of field (e.g. column name)",
          "title": "(optional) A nicer human readable label or title for the field",
          "type": "(REQUIRED) A string specifying the type",
          "format": "(only required if NOT using default) A string specifying a format",
          "description": "(optional) A description for the field"
        }
       ]
      }
    }

name
^^^^
The "name" should match name of the column header. Spaces will be converted to underscores, uppercase will convert to lowercase, special characters will be dropped, and there is a 64 char limit, anything longer will be truncated and given a unique 4 digit hash at the end. It is the machine name that users will use when running queries on the datastore API so it is helpful to not use overly long name values.

type
^^^^
The following are acceptable values:

.. list-table::
   :widths: 25 75
   :header-rows: 1

   * - value
     - description
   * - string
     - Value MUST be a string
   * - number
     - Value MUST be a number, floating point numbers are allowed. Cannot contain non-numeric content other than "."
   * - integer
     - Value MUST be an integer, no floating point numbers are allowed. This is a subset of the number type. Cannot contain non-numeric content.
   * - boolean
     - Value MUST be a boolean.
   * - object
     - Value MUST be an object.
   * - array
     - Value MUST be an array.
   * - any
     - Value MAY be of any type including null.
   * - date
     - A date without a time, in ISO8601 format YYYY-MM-DD.
   * - time
     - A time without a date.
   * - datetime
     - A date with a time, in ISO8601 format YYYY-MM-DDThh:mm:ssZ in UTC time.
   * - year
     - A calendar year.
   * - yearmonth
     - A specific month in a specific year.
   * - duration
     - A duration of time.

format
^^^^^^
This property is important for fields where you need to specify the format of the values. See `Types & Formats <https://specs.frictionlessdata.io/table-schema/#types-and-formats>`_ for details.

If your date values are not in ISO8601 format, use this property to define the format being used so that the data will import into the datastore correctly. Month and day values must be zero-padded. Follow the date formatting syntax of C / Python `strftime <http://strftime.org/>`_ to determine the pattern to use in your format property. For example, if your dates are in mm/dd/YYYY format, use "format": "%m/%d/%Y".

Tutorial I: Catalog-wide data dictionary
----------------------------------------

Creating a data dictionary via the API
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
The simplest way to use data dictionaries on your site is to create one for the entire catalog. To do this, let's first create a new dictionary using the API. We will define a list of fields based on the example header row below.

.. list-table::
   :widths: 16 16 16 16 16 16
   :header-rows: 1

   * - project_id
     - project_name
     - start_date
     - end_date
     - cost
     - contact
   * - 94
     - Example
     - 01/16/2019
     - 05/28/2021
     - 124748.34
     - info@example.com

----

.. code-block::

    POST http://mydomain.com/api/1/metastore/schemas/data-dictionary/items
    Authorization: Basic username:password

    {
        "title": "Demo Dictionary",
        "data": {
            "fields": [
                {
                    "name": "project_id",
                    "title": "Project ID",
                    "type": "integer"
                },
                {
                    "name": "project_name",
                    "title": "Project",
                    "type": "string"
                },
                {
                    "name": "start_date",
                    "title": "Start Date",
                    "type": "date",
                    "format": "%m/%d/%Y"
                },
                {
                    "name": "end_date",
                    "title": "End Date",
                    "type": "date",
                    "format": "%m/%d/%Y"
                },
                {
                    "name": "cost",
                    "title": "Cost",
                    "type": "number"
                },
                {
                    "name": "contact",
                    "title": "Contact",
                    "type": "string",
                    "format": "email"
                }
            ]
        }
    }


We get a response that tells us the identifier for the new dictionary is `7fd6bb1f-2752-54de-9a33-81ce2ea0feb2`.

We now need to set the data dictionary mode to *sitewide*, and the sitewide data dictionary to this identifier. For now, we must do this through drush:

.. code-block::

    drush -y config:set metastore.settings data_dictionary_mode 1
    drush -y config:set metastore.settings data_dictionary_sitewide 7fd6bb1f-2752-54de-9a33-81ce2ea0feb2


Creating a data dictionary via the UI
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
1. Log in as an administrator.
2. From the DKAN menu, select Data Dictionary -> Create.
3. Enter a name for your data dictionary that will serve as its identifier.
4. Define the fields for your data dictionary
5. Click the "Save" button.
6. From the DKAN menu, select Data Dictionary -> Settings.
7. Select "Sitewide" from the Dictionary Mode options.
8. Type in the name of the data-dictionary you created in step 3.
9. Click the "Save configuration" button.

Adding indexes
^^^^^^^^^^^^^^
The same process is used for adding indexes to the datastores.
Learn more about this on :doc:`guide_indexes`
