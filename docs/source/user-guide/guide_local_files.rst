Importing Very Large Files
--------------------------

Sometimes datasets are very large. They are represented by large files and create large tables in the site database.

It can help to download the files as a separate step from the import process, outside of the DKAN queue process. Then we can instruct DKAN to use those existing files for processing into the database.

These instructions assume you’ve already created a dataset (through the the UI, a harvest or the API) that contains a distribution pointing to a remote CSV file, and that this CSV file has not yet been imported to the datastore. We’ll need the ID of the existing dataset.

TL;DR
=====

Abbreviated instructions. Read further for more details.

- Use :code:`drush dkan:datastore:prepare-localized [id]` to set up the local file directory.
- Transfer the file in some way other than a DKAN import.
- Tell DKAN to use local files with this configuration: :code:`drush config:set common.settings always_use_existing_local_perspective 1`
- Perform the import: :code:`drush dkan:datastore:import --deferred [id] && drush queue:run datastore_import`
- Turn off the local file preference with configuration: :code:`drush config:set common.settings always_use_existing_local_perspective 0`

Prepare The Local Perspective
=============================

You can use Drush to discover more information about the dataset, given its ID:

.. code-block::

    % drush dkan:dataset-info bf215cd3-dd81-498c-b57a-4847dbeaac44
    {
        "latest_revision": {
            "uuid": "bf215cd3-dd81-498c-b57a-4847dbeaac44",
            "node_id": "5",
            "revision_id": "5",
            "moderation_state": "published",
            "title": "New Dataset",
            "modified_date_metadata": "2020-08-12",
            "modified_date_dkan": "2023-08-11T18:34:25+0000",
            "distributions": [
                {
                    "distribution_uuid": "3ce75e28-5a07-5351-804c-ecb9b412986b",
                    "resource_id": "1fecf29222b12fc1ce2678abbc8f870f",
                    "resource_version": "1691778866",
                    "fetcher_status": "waiting",
                    "fetcher_percent_done": 0,
                    "file_path": "not found",
                    "source_path": "http:\/\/demo.getdkan.org\/sites\/default\/files\/distribution\/cedcd327-4e5d-43f9-8eb1-c11850fa7c55\/Bike_Lane.csv",
                    "importer_percent_done": 0,
                    "importer_status": "waiting",
                    "importer_error": null,
                    "table_name": "not found"
                }
            ]
        }
    }

We want the resource ID from the distribution. You can just select and copy it from the JSON output, or extract it using `jq <https://jqlang.github.io/jq/>`_:

.. code-block::

    % drush dkan:dataset-info bf215cd3-dd81-498c-b57a-4847dbeaac44 | jq -r '.latest_revision.distributions[].resource_id'
    1fecf29222b12fc1ce2678abbc8f870f

Now that we have the resource ID, we can tell DKAN to prepare for some other script or process to download the file. We use Drush to do this:

.. code-block::

    % drush dkan:datastore:prepare-localized 1fecf29222b12fc1ce2678abbc8f870f
    {
        "source": "http:\/\/demo.getdkan.org\/sites\/default\/files\/distribution\/cedcd327-4e5d-43f9-8eb1-c11850fa7c55\/Bike_Lane.csv",
        "path_uri": "public:\/\/resources\/1fecf29222b12fc1ce2678abbc8f870f_1691778866",
        "path": "\/var\/www\/html\/docroot\/sites\/default\/files\/resources\/1fecf29222b12fc1ce2678abbc8f870f_1691778866",
        "file_uri": "public:\/\/resources\/1fecf29222b12fc1ce2678abbc8f870f_1691778866\/Bike_Lane.csv",
        "file": "\/var\/www\/html\/docroot\/sites\/default\/files\/resources\/1fecf29222b12fc1ce2678abbc8f870f_1691778866\/Bike_Lane.csv"
    }

We can pipe this to jq as well:

.. code-block::

    % drush dkan:datastore:prepare-localized 1fecf29222b12fc1ce2678abbc8f870f | jq -r .path
    /var/www/html/docroot/sites/default/files/resources/1fecf29222b12fc1ce2678abbc8f870f_1691778866

This Drush command, :code:`dkan:datastore:prepare-localized`, will add this file path information to the dataset map as well, which we can check by re-running our dataset info:

.. code-block::

    % ddev drush dkan:dataset-info bf215cd3-dd81-498c-b57a-4847dbeaac44 | jq -r '.latest_revision.distributions[].file_path'
    public://resources/1fecf29222b12fc1ce2678abbc8f870f_1691778866/Bike_Lane.csv

Transfer The File
=================

In this example we’ll just use wget to copy the file at the command line. At the moment, automating this process is left as an exercise for the reader, but a combination of bash and jq should be able to accomplish this.

From the output of :code:`dkan:datastore:prepare-localized` we get the path. In our case this is :code:`/var/www/html/docroot/sites/default/files/resources/1fecf29222b12fc1ce2678abbc8f870f_1691778866`

We’ll need to change into this directory… This may differ on your system.

.. code-block::

    % cd sites/default/files/resources/1fecf29222b12fc1ce2678abbc8f870f_1691778866

Now we can use a file transfer tool to put the file where it belongs. The file is the source field from :code:`dkan:datastore:prepare-localized`.

.. code-block::

    % wget http://demo.getdkan.org/sites/default/files/distribution/cedcd327-4e5d-43f9-8eb1-c11850fa7c55/Bike_Lane.csv

Perform The Import
==================

In order to perform this style of import, we have to set a configuration to use the local file. It’s important that we do this or else DKAN will perform the file transfers again, negating all our work so far.

This configuration can only be set via Drush:

.. code-block::

    % drush config:set common.settings always_use_existing_local_perspective 1

We can verify that this configuration was set:

.. code-block::

    % drush config:get common.settings always_use_existing_local_perspective
    'common.settings:always_use_existing_local_perspective': true

Now our import will use the local file.

If we used harvest to set up the datasets, they are probably already queued to import. If not, we can set up our dataset to import:

.. code-block::

    % drush dkan:datastore:import --deferred 1fecf29222b12fc1ce2678abbc8f870f
     [notice] Queued import for 5c10426922cb88f20d3f5a2ae45d2f11

Now we run cron, or we can run the specific queue:

.. code-block::

    % drush queue:run datastore_import
     [notice] ResourceLocalizer for 1fecf29222b12fc1ce2678abbc8f870f__ completed.
     [notice] ImportService for 1fecf29222b12fc1ce2678abbc8f870f__ completed.
     [success] Processed 1 items from the datastore_import queue in 0.25 sec.

And now we look at the dataset again and verify that it has imported:

.. code-block::

    % drush dkan:dataset-info bf215cd3-dd81-498c-b57a-4847dbeaac44
    {
        "latest_revision": {
            "uuid": "bf215cd3-dd81-498c-b57a-4847dbeaac44",
            "node_id": "5",
            "revision_id": "5",
            "moderation_state": "published",
            "title": "New Dataset",
            "modified_date_metadata": "2020-08-12",
            "modified_date_dkan": "2023-08-11T18:34:25+0000",
            "distributions": [
                {
                    "distribution_uuid": "3ce75e28-5a07-5351-804c-ecb9b412986b",
                    "resource_id": "1fecf29222b12fc1ce2678abbc8f870f",
                    "resource_version": "1691778866",
                    "fetcher_status": "done",
                    "fetcher_percent_done": 100,
                    "file_path": "public:\/\/resources\/1fecf29222b12fc1ce2678abbc8f870f_1691778866\/Bike_Lane.csv",
                    "source_path": "http:\/\/demo.getdkan.org\/sites\/default\/files\/distribution\/cedcd327-4e5d-43f9-8eb1-c11850fa7c55\/Bike_Lane.csv",
                    "importer_percent_done": 0,
                    "importer_status": "done",
                    "importer_error": "",
                    "table_name": "datastore_782876a5222d7fe70df20e7def7f3b3e"
                }
            ]
        }
    }
