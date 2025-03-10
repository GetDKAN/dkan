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
      "data": {
       "title": "A human readable label for the dictionary",
       "fields": [
        {
          "name": "(REQUIRED) machine name of the field that matches the datastore column header.",
          "title": "(optional) A human readable label (usually the column header from the data file.)",
          "type": "(REQUIRED) A string specifying the type",
          "format": "(only required if NOT using default) A string specifying a format",
          "description": "(optional) A description for the field"
        }
       ]
      }
    }

name
^^^^
The "name" should match the datastore column name. These are derived from the column headings of the data file: spaces will be converted to underscores, uppercase will convert to lowercase, special characters will be dropped, and there is a 64 char limit, anything longer will be truncated and given a unique 4 digit hash at the end. It is the machine name that users will use when running queries on the datastore API so it is helpful to not use overly long column headings in your data file. To view the column names of the datastore table, visit `/api/1/datastore/query/{dataset-uuid}/0?results=false&schema=true&keys=true&format=json&rowIds=false` and check the "properties" section.

title
^^^^^
This is usually the column header from the data file, but if the data file uses abbreviated column headings, this is where you can supply a more human readable and clear display title. Depending on your :ref:`configuration <guide_data_dictionary_config>`, this value may also be used for column headings when users export a filtered subset of results as a csv file.

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

.. Note::
  The "Download full dataset (CSV)" button will download the original source file. The "Download filtered data (CSV)" button will generate a new file, using the data dictionary title values (if present) for the column headings, otherwise the column headings from the source file will be used.

How to create a data dictionary
-------------------------------

Creating a data dictionary via the API
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

We will define a list of fields based on the example header row below.

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

.. http:post:: /api/1/metastore/schemas/data-dictionary/items

  **Example request**:

  .. sourcecode:: http

      POST /api/1/metastore/schemas/data-dictionary/items HTTP/1.1
      Host: mydomain.com
      Accept: application/json
      Authorization: Basic username:password

      {
          "data": {
              "title": "Demo Dictionary",
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

  **Example response**:

  .. sourcecode:: http

      HTTP/1.1 201 Created

      {
        "endpoint": "\/api\/1\/metastore\/schemas\/data-dictionary\/items\/7fd6bb1f-2752-54de-9a33-81ce2ea0feb2",
        "identifier": "7fd6bb1f-2752-54de-9a33-81ce2ea0feb2"
      }

We get a response that tells us the identifier for the new dictionary is `7fd6bb1f-2752-54de-9a33-81ce2ea0feb2`.

Creating a data dictionary via the UI
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
1. Log in as a user with the *Data: Create new content* permission.
2. From the DKAN menu, select Data Dictionary -> Create.
3. Enter a human readable title for your data dictionary.
4. In the **Dictionary Fields** section, click the "Add one" button.
5. Fill the form to define your field. Reference the Table Schema section above if needed.
6. Repeat steps 4 and 5 for each field you want in your data dictionary.
7. Click the "Save" button.
8. See a list of your data dictionaries at `/api/1/metastore/schemas/data-dictionary/items/`
9. Edit your data dictionary by going to `/admin/dkan/data-dictionaries`.
10. Click the "Edit" link in the right-hand column next to the data dictionary you want to edit.


Adding indexes
^^^^^^^^^^^^^^
Data dictionaries can be used to describe indexes that should be applied when importing to a database.
Learn more about this on :doc:`guide_indexes`

How to set the Dictionary Mode
-----------------------------------

In the section above we created a data dictionary
with ID `7fd6bb1f-2752-54de-9a33-81ce2ea0feb2`.
We will use this ID when setting either of the data dictionary modes.

Sitewide
^^^^^^^^
The simplest way to use data dictionaries on your site is to create one for the entire catalog.
In this mode, any datastore table that contains any of the defined fields in it's header row will
be altered according to the sitewide data dictionary.

To set the data dictionary mode to **sitewide**:

1. Go to admin/dkan/data-dictionary/settings
2. Set "Dictionary Mode" to "Sitewide".
3. Set "Sitewide Dictionary ID" to `7fd6bb1f-2752-54de-9a33-81ce2ea0feb2`.
4. Click "Save configuration".

.. image:: images/dictionary-settings.png
  :alt: Data dictionary settings admin page, with select input for "Dictionary Mode" set to "Sitewide" and text
        input for Sitewide Dictionary ID containing the identifier 7fd6bb1f-2752-54de-9a33-81ce2ea0feb2.


Distribution reference
^^^^^^^^^^^^^^^^^^^^^^
Datasets can reference specific data dictionaries in this mode. Distribution reference mode means that DKAN will look for links to data dictionaries in the
"Data Dictionary" (describedBy) field of the distribution that a data file is described in. It will look for a URL to a data dictionary
in the metastore. The "Data Dictionary Type" (describedByType) must also be *application/vnd.tableschema+json* to signal the correct data
dictionary format.

To set the data dictionary mode to **distribution reference**:

1. Go to admin/dkan/data-dictionary/settings
2. Set "Dictionary Mode" to "Distribution reference".

.. NOTE::
   Assigning data dictionaries to datasets can be done on the dataset form. Enter the API endpoint of the data dictionary into the "Data Dictionary" field of the distribution section. Set the "Data Dictionary Type" field to *application/vnd.tableschema+json*.

Or, use the API to link a new dataset to the data dictionary.
Look closely at the distribution property in the example below, this is using the data dictionary uuid from the example above.

.. http:post:: /api/1/metastore/schemas/dataset/items

   **Example**:

   .. sourcecode:: http

      POST https://mydomain.com/api/1/metastore/schemas/data-dictionary/items HTTP/1.1
      Accept: application/json
      Authorization: Basic username:password

      {
        "@type": "dcat:Dataset",
        "accessLevel": "public",
        "contactPoint": {
          "fn": "Jane Doe",
          "hasEmail": "mailto:data.admin@example.com"
        },
        "title": "Project list",
        "description": "Example dataset.",
        "distribution": [
          {
            "@type": "dcat:Distribution",
            "downloadURL": "https://example.com/projects.csv",
            "mediaType": "text\/csv",
            "format": "csv",
            "title": "Projects",
            "describedBy": "http://mydomain.com/api/1/metastore/schemas/data-dictionary/items/7fd6bb1f-2752-54de-9a33-81ce2ea0feb2",
            "describedByType": "application/vnd.tableschema+json"
          }
        ],
        "issued": "2016-06-22",
        "license": "http://opendatacommons.org/licenses/by/1.0/",
        "modified": "2016-06-22",
        "publisher": {
          "@type": "org:Organization",
          "name": "Data publisher"
        },
        "keyword":["tag1"]
      }

The API endpoint for the data dictionary can be found at the top of the data dictionary edit form.

This data dictionary will now be used to modify the datastore table after import. If we were to
request the dataset back from the API, it would show us the absolute URL as well.

.. NOTE::
  If you have set the dictionary mode to *distribution reference*, any time you update the data file in the distribution, the datastore will be dropped, re-imported, and any data typing defined in the data dictionary will be applied to the table.

  If you have set the dictionary mode to *sitewide*, when any dataset is updated, and the machine name of the column header from the source data matches the name value in the sitewide data dictionary, the data typing will also be applied to the datastore table.

Modidfy the dataset form
........................

If you are using the distribution reference setting, and you have created many data dictionaries,
you could customize the dataset.ui.json file (Remember you must copy ALL schema files from DKAN into your
root directory to customize any of them). Edit the dataset.ui.json to look like this:

    .. sourcecode:: json

      "describedBy": {
        "ui:options": {
          "description": "URL to the data dictionary for the file found at the Download URL.",
          "widget": "list",
          "type": "select",
          "titleProperty": "title",
          "source": {
            "metastoreSchema": "data-dictionary",
            "returnValue": "url"
          }
        }
      }

This will allow data publishers to select from a list of existing data dictionaries rather than entering the API endpoint.

.. _guide_data_dictionary_config:

CSV Headers Mode
................

Users can run queries against the datastore API and download the results to a CSV file. The **CSV Headers Mode** will determine what values to use for the column headers when the CSV file is generated. The default setting will simply use the same column headings that exist in the original resource file. If your site is using data dictionaries, you could change this setting to use the titles defined in the data dictionary. And there is a third option to use the converted machine name headers that are used in the datastore table.

Visit `/admin/dkan/data-dictionary/settings` to make a selection.

- Use the column names from the resource file
- Use data dictionary titles
- Use the datastore machine names

.. NOTE::
  If you are changing this setting after data has been imported, you will need to re-import the data for the change to take effect.
