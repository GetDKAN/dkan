DKAN Developer's Guide
----------------------

DKAN is a [Drupal distribution](http://drupal.org/documentation/build/distributions). This means that:

* The majority of the code for a fully-built DKAN site comes from [Drupal core](http://www.drupal.org) 
* A large amount of the additional code and functionality comes from [community-contributed Drupal modules](https://www.drupal.org/project/project_module) that have been selected and configured specifically for DKAN. This is often refered to as "contrib code."
* A growing number of modules developed specifically for DKAN by [NuCivic](http://www.nucivic.com) and the growing community of DKAN developers and contributors provide the remaining code and functionality needed for DKAN. This smallest subset of the code in a functioning DKAN site is what we refer to in this documentation as "the DKAN code."

The complete list of Drupal core, contributed, and DKAN-specific modules used can be found in [the DKAN profile's info file](https://github.com/NuCivic/dkan/blob/7.x-1.x/dkan.info). (The Drupal installer script uses the dkan.info file to ensure that all required modules are enabled). 

In a fresh DKAN codebase, all non-Drupal-core, both DKAN and contrib, can be found in _profiles/dkan_.

The DKAN code can be found in the following repositories, all currently hosted on GitHub:

Repository | Description
-----------|--------------
[DKAN](https://github.com/NuCivic/dkan) | The DKAN drupal distribution. Contains the old default DKAN theme, the dkan_sitewide module which defines block and navigation items, and the [make file](http://drush.ws/docs/make.txt) for building DKAN.
[DKAN Dataset](https://www.drupal.org/project/dkan_dataset) | Defines the Dataset and Resource content types as [Drupal Features](https://www.drupal.org/documentation/modules/features), and overrides the default Drupal content editing forms to provide a specialized Dataset creation workflow.
[DKAN Datastore](https://github.com/NuCivic/dkan_datastore) | Sets up the local datastore
[recline](https://github.com/NuCivic/recline) | Provides an integration between Drupal file fields and the [Recline.js](http://okfnlabs.org/recline/) library for [data previews](/dkan-documentation/dkan-features/data-preview-features).
[Feeds Flatstore Processor](https://github.com/NuCivic/feeds_flatstore_processor) | Defines plugins for the Drupal [Feeds](https://www.drupal.org/project/feeds) module for importing CSV files to data tables. DKAN Datastore relies on this module for importing.
[NuBoot Radix](https://github.com/NuCivic/nuboot_radix) |  The current default theme for DKAN, based on [Radix](https://www.drupal.org/project/radix). 

Correctly following the [installation instructions](/dkan-documentation/dkan-developers/installation) will ensure that code is cloned into the correct locations to build a functional DKAN site. Additional modules that have been developed specifically for DKAN, but are not included in DKAN's "core," are listed in the [Extending DKAN](/dkan-documentation/extending-dkan) section.

.. toctree::
   :maxdepth: 1

   accessibility
   addingfields
   socialmedia
   contributing
   datasetfields
   subtheme
   reportingbugs
   maintaining
   addingdkanfeatures