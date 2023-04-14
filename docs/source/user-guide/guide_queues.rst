DKAN Queues
===========

.. list-table::
  :width: 100
  :widths: auto
  :header-rows: 1

  * - Queue
    - Description
  * - datastore_import
    -	This queue handles fetching remote files and parsing the data into datastore tables
  * - post_import
    - This queue will apply data-dictionary definitions and indexes to the datastore tables
  * - resource_purger
    - This queue will purge unneeded resource revisions
  * - orphan_reference_processor
    - This queue deals with the referenced nodes that have been orphaned by a change to the dataset
  * - orphan_resource_remover
    - Deletes orphaned resources belonging to deleted distributions
