Importing Very Large Files
--------------------------

Sometimes datasets are very large. They are represented by large files and create large tables in the site database.

It can help to download the files as a separate step from the import process, outside of the DKAN queue process.

These instructions assume you’ve already set up a dataset, either through the UI or by running a harvest or through the dataset API. We’ll need the UUID of the existing dataset.

Prepare The Local Perspective
=============================

You can use Drush to discover more information about the dataset, given its UUID:

.. code-block::
    % drush dkan:dataset-info f44522a8-66b8-406a-8bb2-78d796cde47c
    {
        "latest_revision": {
            "uuid": "f44522a8-66b8-406a-8bb2-78d796cde47c",
            "node_id": "11",
            "revision_id": "11",
            "moderation_state": "published",
            "title": "2016 Ownership Payment Data",
            "modified_date_metadata": "2023-06-30T12:00:00+00:00",
            "modified_date_dkan": "2023-08-09T17:49:07+0000",
            "distributions": [
                {
                    "distribution_uuid": "c4ec60bb-2bee-524f-8ae7-6924bdba54b1",
                    "resource_id": "ff6cb8d64923ff814a101f5e159c4d0e",
                    "resource_version": "1691603349",
                    "fetcher_status": "waiting",
                    "fetcher_percent_done": 0,
                    "file_path": "not found",
                    "source_path": "https:\/\/download.cms.gov\/openpayments\/PGYR16_P063023\/OP_DTL_OWNRSHP_PGYR2016_P06302023.csv",
                    "importer_percent_done": 0,
                    "importer_status": "waiting",
                    "importer_error": null,
                    "table_name": "not found"
                }
            ]
        }
    }

We want the resource_id of the distribution. Here’s an example of extracting it using jq:

.. code-block::
    % drush dkan:dataset-info f44522a8-66b8-406a-8bb2-78d796cde47c | jq -r '.latest_revision.distributions[].resource_id'
    ff6cb8d64923ff814a101f5e159c4d0e

Now that we have the resource ID, we can tell DKAN to prepare for some other script or process to download the file. We use Drush to do this:

.. code-block::
    % drush dkan:datastore:prepare-localized ff6cb8d64923ff814a101f5e159c4d0e
    {
        "source": "https:\/\/download.cms.gov\/openpayments\/PGYR16_P063023\/OP_DTL_OWNRSHP_PGYR2016_P06302023.csv",
        "path_uri": "public:\/\/resources\/ff6cb8d64923ff814a101f5e159c4d0e_1691603349",
        "path": "\/var\/www\/html\/docroot\/sites\/default\/files\/resources\/ff6cb8d64923ff814a101f5e159c4d0e_1691603349",
        "file_uri": "public:\/\/resources\/ff6cb8d64923ff814a101f5e159c4d0e_1691603349\/OP_DTL_OWNRSHP_PGYR2016_P06302023.csv",
        "file": "\/var\/www\/html\/docroot\/sites\/default\/files\/resources\/ff6cb8d64923ff814a101f5e159c4d0e_1691603349\/OP_DTL_OWNRSHP_PGYR2016_P06302023.csv"
    }

We can pipe this to jq as well:

.. code-block::
    % drush dkan:datastore:prepare-localized ff6cb8d64923ff814a101f5e159c4d0e | jq -r .path
    /var/www/html/docroot/sites/default/files/resources/ff6cb8d64923ff814a101f5e159c4d0e_1691603349

This Drush command, dkan:datastore:prepare-localized, will add this file path information to the dataset as well, which we can check by re-running our dataset info:

.. code-block::
    % drush dkan:dataset-info f44522a8-66b8-406a-8bb2-78d796cde47c | jq -r '.latest_revision.distributions[].file_path'
    public://resources/ff6cb8d64923ff814a101f5e159c4d0e_1691603349/OP_DTL_OWNRSHP_PGYR2016_P06302023.csv

Transfer The File
=================

In this example we’ll just use wget to copy the file at the command line. At the moment, automating this process is left as an exercise for the reader, but a combination of bash and jq should be able to accomplish this.

From the output of dkan:datastore:prepare-localized we get the path. In our case this is /var/www/html/docroot/sites/default/files/resources/ff6cb8d64923ff814a101f5e159c4d0e_1691603349

We’ll need to change into this directory… This may differ on your system.

.. code-block::
    % cd sites/default/files/resources/ff6cb8d64923ff814a101f5e159c4d0e_1691603349

Now we can use a file transfer tool to put the file where it belongs. The file is the source field from dkan:datastore:prepare-localized.

.. code-block::
    % wget https://download.cms.gov/openpayments/PGYR16_P063023/OP_DTL_OWNRSHP_PGYR2016_P06302023.csv

Perform The Import
==================

In order to perform this style of import, we have to set a configuration to use the local file. It’s important that we do this or else DKAN will perform the file transfers again, negating all our work so far.

This configuration can only be set via Drush:

.. code-block::
    % drush config:set common.settings always_use_existing_local_perspective 1


     Do you want to update always_use_existing_local_perspective key in common.settings config? (yes/no) [yes]:
     >

We can verify that this configuration was set:

.. code-block::
    % drush config:get common.settings always_use_existing_local_perspective
    'common.settings:always_use_existing_local_perspective': true

Now our import will use the local file.

If we used harvest to set up the datasets, they are probably already queued to import. If not, we can set up our dataset to import:

.. code-block::
    % ddev drush dkan:datastore:import --deferred ff6cb8d64923ff814a101f5e159c4d0e
     [notice] Queued import for 5c10426922cb88f20d3f5a2ae45d2f11

Now we run cron, or we can run the specific queue:

.. code-block::
    % ddev drush queue:run datastore_import
     [notice] ResourceLocalizer for 5c10426922cb88f20d3f5a2ae45d2f11__ completed.
     [notice] ImportService for 5c10426922cb88f20d3f5a2ae45d2f11__ completed.
     [success] Processed 1 items from the datastore_import queue in 12.12 sec.

And now we look at the dataset again and verify that it has imported:

.. code-block::
    % ddev drush dkan:dataset-info 4c774e90-7f9e-5d19-b168-ff9be1e69034
    {
        "latest_revision": {
            "uuid": "4c774e90-7f9e-5d19-b168-ff9be1e69034",
            "node_id": "308",
            "revision_id": "382",
            "moderation_state": "published",
            "title": "2016 General Payment Data",
            "modified_date_metadata": "2023-06-30T12:00:00+00:00",
            "modified_date_dkan": "2023-08-09T16:31:16+0000",
            "distributions": [
                {
                    "distribution_uuid": "cdc9b12e-37e7-5b20-8adf-c21c82c7c099",
                    "resource_id": "5c10426922cb88f20d3f5a2ae45d2f11",
                    "resource_version": "1691598677",
                    "fetcher_status": "done",
                    "fetcher_percent_done": 100,
                    "file_path": "public:\/\/resources\/5c10426922cb88f20d3f5a2ae45d2f11_1691598677\/OP_DTL_GNRL_PGYR2016_P06302023.csv",
                    "source_path": "https:\/\/download.cms.gov\/openpayments\/PGYR16_P063023\/OP_DTL_GNRL_PGYR2016_P06302023.csv",
                    "importer_percent_done": 0,
                    "importer_status": "done",
                    "importer_error": "",
                    "table_name": "datastore_6a539bc4bfbb3fd209d9f2ce797ec0e9"
                }
            ]
        }
    }
