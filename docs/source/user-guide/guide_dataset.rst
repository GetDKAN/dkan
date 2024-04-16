How to add a Dataset
====================

.. _guide_dataset:

There are several methods for adding datasets to your site.

API
---
You will need to authenticate with a user account possessing the *Data: Create new content* permission.
See :ref:`Authentication <authentication>` for details.

.. http:post:: /api/1/metastore/schemas/dataset/items

  **Create a dataset:**

  .. sourcecode:: http

    POST http://dkan.ddev.site/api/1/metastore/schemas/dataset/items?format_json HTTP/1.1
    content-type: application/json
    Authorization: Basic username:password

        {
          "title": "My new dataset",
          "description": "Detailed description for my new dataset.",
          "accessLevel": "public",
          "accrualPeriodicity": "R/P1Y",
          "publisher": {
            "name": "Publisher Name"
          },
          "contactPoint": {
            "fn": "Test Contact",
            "hasEmail": "test@example.com"
          },
          "issued": "2013-02-10",
          "modified": "2022-06-01",
          "keyword": ["tag1","tag2"],
          "license": "http://opendefinition.org/licenses/odc-pddl/",
          "spatial": "London, England",
          "temporal": "2013-02-10/2022-06-01"
          "theme": [
            "Category"
          ],
          "distribution": [
              {
                  "downloadURL": "http://demo.getdkan.org/sites/default/files/distribution/5dc1cfcf-8028-476c-a020-f58ec6dd621c/data.csv",
                  "mediaType": "text/csv",
                  "format": "csv",
                  "description": "The data we want to share.",
                  "title": "Resource Example"
              }
          ]
        }

.. http:patch:: /api/1/metastore/schemas/dataset/items/{datasetID}

  **Edit a dataset:**

  .. sourcecode:: http

    PATCH http://dkan.ddev.site/api/1/metastore/schemas/dataset/items/{datasetID}?format_json HTTP/1.1
    content-type: application/json
    Authorization: Basic username:password

        {
          "modified": "2023-06-01",
          "distribution": [
              {
                  "downloadURL": "http://demo.getdkan.org/sites/default/files/new-data-file.csv",
                  "mediaType": "text/csv",
                  "format": "csv",
                  "title": "2023 data update"
              }
          ]
        }

GUI
----

**Create a dataset:**

1. Log in to the site.
2. Navigate to Admin > DKAN > Datasets.
3. Click the "+ Add new dataset" button.
4. Be sure to provide as much descriptive details for your dataset as you can so that users who may be interested in your data can find it.
5. The Release Date is the date on which the dataset was first made available.
6. The Temporal field describes the time frame to which the data is applicable.
7. The Frequency field describes how often the data is updated.
8. When adding keywords and category terms, be sure you are not duplicating existing terms with minor spelling/capitalization differences.
9. Pay close attention to the required fields (marked with \*).
10. Use the Distribution *Download URL* field to enter a url to your file or upload a local file.
11. If you are uploading a file, wait for the upload to finish before clicking the Save button. The file name will turn blue when is it complete.
12. If you are adding more than one file to a dataset be sure to utilize the **File Title** field to distinguish the differences in the files to the user.
13. Click "Save".
14. Run cron to start the import.

**Edit a dataset:**

1. Log in to the site.
2. Navigate to Admin > DKAN > Datasets.
3. Find the dataset you wish to edit and click the "Edit" link in the right-hand column.
4. Click "Save"


Harvest
-------
Harvesting is a method well suited for managing datasets in bulk.
In the example below we are only creating a single dataset, but you can add as many datasets to
the dataset array as you want. Create a json file in your local sites/default/files directory like this:

*h1.json*

.. code-block:: json

      {
        "@context": "https:\/\/project-open-data.cio.gov\/v1.1\/schema\/catalog.jsonld",
        "@id": "http:\/\/fake.com\/data.json",
        "@type": "dcat:Catalog",
        "conformsTo": "https:\/\/project-open-data.cio.gov\/v1.1\/schema",
        "describedBy": "https:\/\/project-open-data.cio.gov\/v1.1\/schema\/catalog.json",
        "dataset": [
          {
            "@type": "dcat:Dataset",
            "accessLevel": "public",
            "contactPoint": {
              "fn": "admin",
              "hasEmail": "test@test.com"
            },
            "description": "Test description",
            "distribution": [
              {
                "@type": "dcat:Distribution",
                "downloadURL": "http://demo.getdkan.org/sites/default/files/distribution/cedcd327-4e5d-43f9-8eb1-c11850fa7c55/Bike_Lane.csv",
                "mediaType": "text\/csv",
                "format": "csv",
                "title": "Test Resource"
              }
            ],
            "identifier": "cedcd327-4e5d-43f9-8eb1-c11850fa7c66",
            "issued": "2016-06-22",
            "modified": "2020-08-12",
            "publisher": {
              "@type": "org:Organization",
              "name": "demo.getdkan.com"
            },
            "theme": [
              "Test"
            ],
            "title": "New Dataset",
            "keyword": [
              "tag-1"
            ]
          }
        ]
      }


Create a harvest based on the file above:

.. prompt:: bash $

      drush dkan:harvest:register --identifier=harvest1 --extract-uri=http://dkan.ddev.site/sites/default/files/h1.json
      drush dkan:harvest:run harvest1
      drush cron

More on the :doc:`harvest method can be found here <guide_harvest>`.

Add demo site content
---------------------

Generate the same 10 datasets that are used on the `DKAN demo site <https://demo.getdkan.org/>`_.
Enable the sample content module. Run the create command to add the datasets.
Running cron will run the queues that fetch the csv files and import them into datstore tables. You will likely need to run cron multiple times.
When the sample content is no longer needed, remove the datasets with the remove command.

.. prompt:: bash $

      drush en sample_content -y
      drush dkan:sample-content:create
      drush cron
      drush cron
      drush dkan:sample-content:remove

Troubleshooting
^^^^^^^^^^^^^^^

If you see output like this (note the errors):

.. code-block::

   +----------------+-----------+---------+---------+--------+
   | run_id         | processed | created | updated | errors |
   +----------------+-----------+---------+---------+--------+
   | sample_content | 10        | 0       | 0       | 10     |
   +----------------+-----------+---------+---------+--------+

You will need to add this line to your settings.php file, adjust as needed.

.. code-block::

   $settings['file_public_base_url'] = $settings['base_url'] . 'sites/default/files';
