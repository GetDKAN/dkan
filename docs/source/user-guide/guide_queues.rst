DKAN Queues
===========

.. list-table::
  :width: 100
  :widths: auto
  :header-rows: 1

  * - Queue
    - Description
  * - localize_import
    -	This queue fetches remote files from source URLs to the local filesystem
  * - datastore_import
    -	This queue handles parsing the localized data into datastore tables
  * - post_import
    - This queue will apply data-dictionary definitions and indexes to the datastore tables
  * - resource_purger
    - This queue will purge unneeded resource revisions
  * - orphan_reference_processor
    - This queue deals with the referenced nodes that have been orphaned by a change to the dataset
  * - orphan_resource_remover
    - Deletes orphaned resources belonging to deleted distributions
