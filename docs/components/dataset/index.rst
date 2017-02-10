DKAN Dataset Module and Sub-Modules
===================================

DKAN's core functionality around datasets, their metadata and resources, is defined in "DKAN Dataset" (*dkan_dataset*) and its submodules (in the `modules` folder):

* **DKAN Dataset Content Types** contains the actual `Features <https://www.drupal.org/project/features>`_ exports for the Dataset and Resource content types and fields.
* **DKAN Dataset Rest API** defines a REST endpoint via the `Services <https://www.drupal.org/project/services>`_ module, exposing full CRUD operations to authenticated 3rd-party apps and services. See the :doc:`Dataset REST API documentation</apis/rest-api>` for more information.
* **DKAN Dataset Groups** provides `Organic Groups <https://www.drupal.org/project/og>`_ functionality in DKAN, which groups both dataset content and site users into discreet groups with separate branding and granular access permissions. Usually used to allow for multiple data publishers (for instance, sub-agencies sharing a single data portal).

.. toctree::
   :maxdepth: 1

   usage
   datasetfeatures
   previews
   groups
