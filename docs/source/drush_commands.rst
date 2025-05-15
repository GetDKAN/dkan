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

dkan:datastore:localize
-----------------------

    You can manually 'localize' the source files for a distribution, by fetching a
    copy to the local file system.
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

dkan:datastore:prepare-localized
--------------------------------

    Prepare the local perspective for a resource.

    Will do the following:

    - Prepare the directory in the file system.
    - Add the local_url perspective to the resource mapper. Note this is missing the file checksum.
    - Display the info necessary to perform an external file fetch.

    **Arguments**

    - **identifier** Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".

~~~~~~

dkan:datastore:reimport
--------------------------------

    Re-import data into the datastore for every distribution in a dataset.

    **Arguments**

    - **uuid** The uuid of a dataset.

~~~~~~

dkan:harvest:archive
---------------------

    Archives (unpublish) harvested entities.

    **Arguments**

    - **harvestId** The id of the harvest source.

~~~~~~

dkan:harvest:deregister
-----------------------

    Deregister a harvest plan. This action removes the harvest plan from the
    system, but will leave the datasets in place. This is good if you are using
    harvest to migrate content and you do not plan on using the harvest feature
    to update the datasets.

    If you want to remove the harvest plan AND the datasets associated with it,
    use the --revert option.

    **Arguments**

    - **harvestId** The harvest id
    - **revert** Perform a revert before deregistering.

    **Usage**

        ``drush dkan:harvest:deregister --revert myHarvestId``

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

        ``dkan-harvest:register '{"identifier":"myHarvestId","extract":{"type":"\\Drupal\\harvest\\ETL\\Extract\\DataJson","uri":"http://example.com/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'``

    Or

        ``dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json``

~~~~~~

dkan:harvest:revert
--------------------

    Revert a harvest. Removes harvested entities and unpublishes orphaned
    keywords, themes, and distributions. The harvest plan will remain and can
    be run again to generate the datasets after any issues have been resolved.

    **Arguments**

    - **harvestId** The source to revert.

    **Usage**

        ``drush dkan:harvest:revert myHarvestId``

~~~~~~

dkan:harvest:run-all
--------------------

    Run all harvests.

    Optionally, only run harvests which haven't been run before.

    **Options**

    - **new** Only run harvests which have not been run before.

    **Usage**

        ``drush dkan:harvest:run-all --new``

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

    If you are using the `DKAN DDEV Add-On <https://github.com/GetDKAN/ddev-dkan>`_, you can create and delete test user accounts with the following commands.

    **Add users**

    ``ddev dkan-test-users``

    **Remove users**

    ``ddev dkan-test-users --remove``

    You can define your own custom test users by adding a testuser.json file to the root of your project. These commands will generate and remove the users specified, if no file is found, the DKAN default user accounts will be used.
