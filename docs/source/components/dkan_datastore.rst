DKAN Datastore
==============
.. _datastore:

The DKAN **Datastore** provides a service to parse CSV files and save the data in database tables.
This allows users to query the data through a `public API <https://demo.getdkan.org/api>`_.

When a dataset is added to the catalog, the mimetype value of each distribution object is checked
to determine if the distribution contains a CSV file. The CSV distributions will be added to the
import queue to be parsed and imported into the datastore during cron runs.

When defining the distribution object of your dataset, be sure to include the mimetype property:
`"mediaType": "text/csv"` if using the API, or select 'CSV' from the format field if using the GUI.

The import will happen in batches during cron runs as part of the datastore_import queue. The import
will iterate over the file, if it does not finish, the job will be returned to the queue. Be sure to
have cron running on a regular basis so that large file imports will complete. You can view the status
of datastore imports at `/admin/dkan/datastore/status`.

If you enable the :ref:`Datastore Mysql Import <mysql_import>` module, the file will be imported in
a single step using MySQL's native LOAD DATA function.

.. attention::

  Note that the CSV must be in UTF-8 format to parse correctly.

If there is a change to the *distribution: downloadURL* property, the existing datastore will be dropped
and a new import will be triggered.

.. _mysql_import:

.. include:: ../../../modules/datastore/modules/datastore_mysql_import/README.md
   :parser: myst_parser.sphinx_

Datastore Settings
------------------

See :doc:`Datastore Settings <../user-guide/guide_datastore_settings>` for configuration information.

Data dictionary integration
---------------------------

If you are using DKAN Data Dictionaries on your site, the datastore will refer to them during the
post import process. If a file has just been imported, and a data dictionary is available for it,
the datastore importer will:

1. Match fields in the data dictionary to properties/columns in the new datastore table by name.
2. Create a new job in the queue to alter the new table in the database to match its columns to the field types defined in the dictionary, and change the column description/comment in the database to match the "title" in the dictionary.
3. Report the status of the alter job once it has run.

See :doc:`Metastore <dkan_metastore>` and :doc:`Data Dictionaries <../user-guide/guide_data_dictionaries>` for more on how to use this feature.
