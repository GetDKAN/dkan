DKAN Overview
=============

**DKAN** is an open-source open-data platform inspired by `CKAN <https://ckan.org/>`_ (Comprehensive Knowledge Archive Network)
and built on top of the very popular `Drupal <https://drupal.org>`_ CMS (Content Management System).

----

Structure
---------

DKAN is a Drupal module that adds data management functionality.

Within DKAN there are additional modules to organize internal subsystems. Information about the
subsystems/components in DKAN can be found in the :doc:`../components/index` page.

DKAN's modules and subsystems are organized around four main data functions:

1. Management
2. Aggregation
3. Discoverability
4. Usability

Data Management
^^^^^^^^^^^^^^^

The main function of any open data platform is to help manage data. Making data *public* is simple,
anyone can place a file in a web-accessible server, but making data *open* takes a bit more work.
True open data is accessible, discoverable, machine-readable, linked to other resources that provide context,
published in an open format and under an open license.

This is what we mean by data management: providing tools that empower data publishers to make data open,
which empowers data consumers to find and use the data they need.

.. note::
    For more on the fundamentals of open data, read `the Open Definition <https://opendefinition.org/od/2.1/en/>`_
    and `5-Star Open Data <https://5stardata.info/>`_.

Most data management functions in DKAN are provided by the :doc:`../components/dkan_metastore` module.

Data Aggregation
^^^^^^^^^^^^^^^^

Many open data catalogs are aggregations of other sources of data. DKAN provides tools to
allow any DKAN catalog to host aggregated or federated datasets in conjunction with
originally-sourced data. A very large real-world example of this is `Data.gov <https://www.data.gov/>`_,
a catalog which aggregates datasets the U.S. federal government.

Aggregating or importing datasets from different remote sources into a catalog is often known as
*harvesting*. DKAN has robust and extensible functionality for this that lives in the :doc:`../components/dkan_harvest` module.


Discoverability
^^^^^^^^^^^^^^^

Finally, data is only useful and open to the degree to which it can be found and understood.
This is why many of the modules in DKAN are dedicated to helping make data more accessible.

The :doc:`../components/dkan_metastore` helps data publishers give context (:term:`metadata`)
to their data. The :doc:`../developer-guide/dev_search` module provides a configurable way to
allow data consumers to use metadata and find what they need.

The searchable metadata provided by the metastore_search module will help users narrow down
their search, but ultimately the user will have to look at the data itself.

Usability
^^^^^^^^^

Data in files isn't naturally searchable, but the :doc:`../components/dkan_datastore` module
parses and stores data in a more explorable format. DKAN can then use the datastore to provide
direct access to the data, through tools like the DatastoreQuery Endpoint.

----

DKAN is actively maintained by `CivicActions <https://civicactions.com/dkan>`_.

To learn more about the DKAN community visit `DKAN Discussions <https://github.com/GetDKAN/dkan/discussions>`_.
