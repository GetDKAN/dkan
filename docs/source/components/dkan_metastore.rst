DKAN Metastore
==============
.. _metastore:

DKAN's **Metastore** is what you use to create, retrieve, update, and delete records describing your data.
These records are what we refer to as ":term:`metadata`".

As a data catalog, DKAN's main goal is to help you share a collection of ":term:`Dataset`" records.
A dataset's metadata can follow virtually any *schema*, or format you want. What is important is that it
points to the data you are trying to share, and that it gives useful contextual information. This usually
includes when the data was released, how often it is updated and who published it, but can include details
as precise as the geographic boundaries or relevant time period the data applies to.

Some more details of DKAN's metastore:

* The data assets themselves (usually in the form of local files or URLs to data files) are referred to internally in DKAN as *resources*.
* The structure and format of dataset metadata in DKAN are determined by a `JSON schema <https://json-schema.org/>`_. By default, DKAN provides and utilizes the `DCAT-US metadata schema <https://resources.data.gov/resources/dcat-us/>`_ to store datasets, but :ref:`custom schemas <custom_schema>` can be added to the codebase to override this.
* In DCAT-US, resources are placed in a sub-schema of the parent dataset called a *distribution*.

.. image:: https://project-open-data.cio.gov/v1.1/schema-diagram.svg
  :width: 400
  :alt: Dataset Structure

.. note::

  Read the documentation on :doc:`How to add a Dataset <../user-guide/guide_dataset>` to get started adding information to the metastore.


Configuration
-------------
.. _custom_schema:

Changing your dataset schema
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Replacing the dataset schema in DKAN allows you to add fields and conform to additional specifications.
As long as you provide a valid JSON schema, any information going into the metastore will be validated against it.

To change the schema being used, copy the contents of the `schema` directory from the DKAN repo and place
it in the root of your Drupal installation (docroot/schema). Then make any modifications necessary to the
`dataset.json` file inside the `collections` directory. Note that even if you are only changing the
dataset.json schema, it is important to copy ALL of the schema files as DKAN will be expecting all of the
schema files to be in the same location.

.. warning::

  Warning: The schema is actively used by the catalog to verify the validity of the data.
  Making changes to the schema after data is present in the catalog should be done with care
  as non-backward-compatible changes to the schema could cause issues.
  Look at ``Drupal::metastore::SchemaRetriever::findSchemaDirectory()`` for context.

Data Dictionaries
-----------------

Data dictionaries are a special kind of metadata in the metastore, which describe data at the *column level*,
in contrast to most of the other kinds of metadata, which describe data at the *dataset level*.
The term "data dictionary" is fairly broad, and can refer to anything from a PDF document to a
machine-readable table schema.

While users are free to integrate data dictionaries into their metadata schemas in any way they chose
in DKAN, we are introducing our own native data dictionary concept. Data dictionaries in DKAN are JSON
metadata items managed in the metastore in the same way that datasets and distributions are. They are,
however, less flexible than other metadata schema, which can be completely overridden/replaced in your
instance of DKAN. To use DKAN's new native data dictionary features, you must use the `data-dictionary`
schema that ships with DKAN, which is 100% compatible with the `Frictionless Data table schema <https://specs.frictionlessdata.io/table-schema/>`_ format.

Data dictionaries can have three different relationships with your catalog:

1. You may have a single data dictionary for your entire catalog, and share its column definitions across all datasets.
2. You may define a set of domain-specific data dictionaries for your catalog, which you can chose between when creating a dataset.
3. You may define one data dictionary for every dataset, or even every distribution, in your catalog.

*(Note that only option #1 above has been implemented in the current version of DKAN.)*

Data dictionaries will affect the behavior of the :doc:`Datastore <dkan_datastore>`.

By default, all data imported into a datastore will be stored as strings.
Use the data dictionary to define which columns should be stored as dates, integers, decimals, etc.

See :doc:`Data Dictionaries <../user-guide/guide_data_dictionaries>` for step-by-step instructions for use.

