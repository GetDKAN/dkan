DKAN Datastore
===============

DKAN features a Datastore for uploaded files.

Any type of file can be uploaded to DKAN through the "Add Resources" form or through the API. CSV and XML files, however, can also parsed and inserted into unique tables in the DKAN database.

The DKAN Datastore's importer is a wrapper around the `Feeds <https://www.drupal.org/project/feeds>`_ module. The custom `Feeds Flatstore Processor <https://github.com/NuCivic/feeds_flatstore_processor>`_ and `Feeds Field Fetcher <https://www.drupal.org/project/feeds_field_fetcher>`_ plugins were created  the file uploaded to the resource form a feed item.

The `Data <https://www.drupal.org/project/data>`_ module is used to manage datastore tables' schema.

The Datastore API uses the `Services <https://www.drupal.org/project/services>`_ module to provide an endpoint, although nearly all the underlying functionality is overridden and provided directly by the `DKAN Datastore API <https://www.drupal.org/project/services>`_ module.


.. toctree::
   :maxdepth: 1

   usage
   fast-import
   background
   geocoder