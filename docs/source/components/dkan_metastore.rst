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
* The structure and format of dataset metadata in DKAN are determined by a `JSON schema <https://json-schema.org/>`_. By default, DKAN provides and utilizes the `DCAT-US metadata schema <https://resources.data.gov/resources/dcat-us/>`_ to store datasets, but :doc:`custom schemas <../user-guide/guide_custom_schemas>` can be added to the codebase to override this.
* In DCAT-US, resources are placed in a sub-schema of the parent dataset called a *distribution*.

.. image:: https://resources.data.gov/schemas/dcat-us/v1.1/schema-diagram.svg
  :width: 400
  :alt: Dataset Structure

.. seealso::

  * Read the documentation on :doc:`How to add a Dataset <../user-guide/guide_dataset>` to get started adding information to the metastore.
  * Read the documentation on :doc:`Changing your dataset schema <../user-guide/guide_custom_schemas>` to learn how to add custom fields.
  * Read the :doc:`DKAN Metastore developer guide <../developer-guide/dev_metastore>` for technical implementation details.

Metastore References
--------------------

An important feature of the metastore is the ability to create relationships
between metadata records, by having particular properties reference content from another
metadata item rather than store the value directly. For instance, if you reference
the dataset's publisher property, adding a dataset with this JSON content:

.. code-block:: json

  {
    "title": "Dataset fragment",
    "publisher": {
      "@type": "org:Organization",
      "name": "Data Organization"
    }
  }

Could be stored as:

.. code-block:: json

  {
    "title": "Dataset fragment",
    "publisher": "550e8400-e29b-41d4-a716-446655440000"
  }

Where the publisher property is now a node UUID that references another metadata item
with the schema "publisher". This allows multiple datasets to share properties such
as publisher or keyword, that can be loaded or changed independently. This is conceptually very
similar to using a `Drupal Entity Reference field <https://www.drupal.org/docs/user_guide/en/structure-reference-fields.html>`_.

.. note::

  As currently implemented, DKAN references do not follow the `JSON 
  Reference <https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03>`_ specification.

When the metastore encounters a value in a referenced property that does not match
an existing node for that value, it will create a new node.

You can change which properties are referenced in your DKAN site by visiting the
Metastore settings page at ``admin/dkan/properties``, and scrolling to the 
"Dataset properties to be stored as separate entities" section. By default, currently
DKAN installs with the following properties set to be referenced:

* ``publisher``
* ``keyword``
* ``theme``
* ``distribution``

However, ``distribution`` will likely soon not be referenced by default. The fact that
any change to a distribution within a dataset will generate a new distribution node 
can be confusing, and it is often more useful to have the distribution embedded
directly within the dataset. Recent changes to DKAN allow distributions to be directly
embedded within datasets without losing related data-dictionary or datastore functionality
(which was previously tightly coupled to the existence of a distribution node).

Orphans
#######

When a property is referenced, and the value of that property is changed in a dataset,
the old value is no longer referenced by any dataset. This is called an "orphaned" entity.
For instance, if we have referenced distributions, and a dataset is updated to change
the ``downloadURL`` (or even the ``title``, a new distribution node will be created, 
and the old distribution node will be unpublished and "orphaned".

"Orphaned" is a Drupal `workflow state <https://www.drupal.org/docs/8/core/modules/workflows/overview>`_. 
Changing a dataset triggers a `queue <https://api.drupal.org/api/drupal/core%21core.api.php/group/queue/11.x>`_
called ``orphan_reference_processor`` that examines the old referenced node, makes sure
it is not referenced by any other dataset, and if it is not, transitions it to the
"orphaned" state. Orphaned nodes are not deleted, but they are not visible to the public.

Migrating to Embedded Distributions
###################################

If you have an existing DKAN site that has been referencing distributions (or any property),
and want to change your site to embed distributions instead, you can use the ``dkan:metastore:unreference-datasets``
:ref:`Drush command <drush-metastore-unreference-datasets>`. This command will open,
dereference and resave the datasets overwriting the references with the actual embedded data.
The command also includes an option either to orphan the old referenced entities or to delete them immediately.

Obviously, before any process that will modify a large amount of content on your site,
perform this migration with caution. Do a dry run on a staging environment, and 
ensure you have a recent database backup and a reliable rollback plan in place.


Data Dictionaries
-----------------

Data dictionaries are a special kind of metadata in the metastore, which describe data at the *column level*,
in contrast to most of the other kinds of metadata, which describe data at the *dataset level*.
The term "data dictionary" is fairly broad, and can refer to anything from a PDF document to a
machine-readable table schema.

While users are free to integrate data dictionaries into their metadata schemas in any way they chose
in DKAN, DKAN also has its own native data dictionary type. Data dictionaries in DKAN are JSON
metadata items managed in the metastore in the same way that datasets and distributions are. They are,
however, less flexible than other metadata schema, which can be completely overridden/replaced in your
instance of DKAN. To use DKAN's new native data dictionary features, you must use the `data-dictionary`
schema that ships with DKAN, which is 100% compatible with the `Frictionless Data table schema <https://specs.frictionlessdata.io/table-schema/>`_ format.

Data dictionaries can have three different relationships with your catalog:

1. You may have a single data dictionary for your entire catalog, and share its column definitions across all datasets.
2. You may define a set of domain-specific data dictionaries for your catalog, which you can chose between when creating a dataset.
3. You may define one data dictionary for every dataset, or even every distribution, in your catalog.

Data dictionaries will affect the behavior of the :doc:`Datastore <dkan_datastore>`.

By default, all data imported into a datastore will be stored as strings.
Use the data dictionary to define which columns should be stored as dates, integers, decimals, etc.

See :doc:`Data Dictionaries <../user-guide/guide_data_dictionaries>` for step-by-step instructions for use.

