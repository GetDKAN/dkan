Drush commands
===============

dkan:dataset-info
-----------------
  View information about a dataset.

  **Arguments**

  - **uuid** The uuid of a dataset.

~~~~~~

dkan:datastore:drop
-------------------
    Drop a datastore.

    **Arguments**

    - **uuid** The uuid of a dataset.

~~~~~~

dkan:datastore:drop-all
-----------------------

    Drop a ALL datastore tables.

~~~~~~

dkan:datastore:import
---------------------

    You can manually import file data into the datastore with the identifier of the distribution.
    There are two main ways to get the distribution uuid:

    1. Use the `API <https://demo.getdkan.org/api/1/metastore/schemas/dataset/items?show-reference-ids>`_ to get the identifier of the file you want to import.
       The identifier will be at ``distribution.0.data.%Ref:downloadURL.0.data.identifier``
    2. Use ``dkan:dataset-info``

    **Arguments**

    - **uuid** The uuid of a resource.
    - **deferred** Whether or not the process should be deferred to a queue.

~~~~~~

dkan:datastore:list
--------------------

    List information about all datastores.

    **Options**

    - **format** The format of the data. (default: **table**)
    - **status** Show imports of the given status.
    - **uuid-only** Only the list of uuids.

~~~~~~

dkan:harvest:archive
---------------------

    Archives (unpublish) harvested entities.

    **Arguments**

    - **harvestId** The id of the harvest source.

~~~~~~

dkan:harvest:deregister
-----------------------

    Deregister a harvest.

    **Arguments**

    - **harvestId** The harvest id

~~~~~~


dkan:harvest:info
-----------------

    Give information about a previous harvest run.

    **Arguments**

    - **harvestId** The harvest id.
    - **runId** The run's id.

~~~~~~

dkan:harvest:list
-----------------

   List available harvests.

~~~~~~

dkan:harvest:publish
--------------------

    Publishes harvested entities.

    **Arguments**

    - **harvestId**. The id of the harvest source.

~~~~~~

dkan:harvest:register
---------------------

    Register a new harvest.

    **Arguments**

    - Harvest plan configuration as a JSON string. Wrap in single quotes, do not add spaces between elements.

    **Options**

    - **identifier** The harvest id.
    - **extract-type** Extract type.
    - **extract-uri** Extract URI.
    - **transform** A transform class to apply. You may pass multiple transforms.
    - **load-type** Load class.

    **Usage**

        ``dkan-harvest:register '{"identifier":"myHarvestId","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"http://example.com/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'``

    Or

        ``dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json``

~~~~~~

dkan:harvest:revert
--------------------

    Revert a harvest, i.e. remove harvested entities and unpublish orhpaned keywords, themes, and distributions.

    **Arguments**

    - **harvestId** The source to revert.

    **Usage**

        ``drush dkan:harvest:revert myHarvestId``

~~~~~~

dkan:harvest:run-all
--------------------

    Run all pending harvests.

~~~~~~

dkan:harvest:run
----------------

    Run a harvest.

    **Arguments**

    - **harvestId** The harvest id.

~~~~~~

dkan:harvest:status
-------------------

    Show status of of a particular harvest run.

    **Arguments**

    - **harvestId** The id of the harvest source.
    - **runId** The run's id. Optional. Show the status for the latest run if not provided.

    **Usage**

        ``drush dkan:harvest:status myHarvestId 1599157120``

~~~~~~

dkan:metadata-form:sync
-----------------------

    Synchronize the module with the React app.

~~~~~~

dkan:metastore-search:rebuild-tracker
-------------------------------------

    Rebuild the search api tracker for the dkan index.

~~~~~~


dkan:metastore:publish
----------------------

    Publish the latest version of a dataset.

    **Arguments**

    - **uuid** Dataset identifier.

~~~~~~

dkan:sample-content:create
--------------------------

    Create sample content.

~~~~~~

dkan-test-users
---------------

    If you are using the `DKAN DDEV Add-On <https://github.com/GetDKAN/dkan-ddev-addon>`_, you can create and delete test user accounts with the following commands.

    **Add users**

    ``ddev dkan-test-users``

    **Remove users**

    ``ddev dkan-test-users --remove``

    You can define your own custom test users by adding a testuser.json file to the root of your project. These commands will generate and remove the users specified, if no file is found, the DKAN default user accounts will be used.
