How to customize the dataset schema
====================================

If you have additional specification requirements, replacing the dataset schema in DKAN will allow you to store
additional metadata for a dataset beyond the :doc:`default metadata <../components/dkan_metastore>`.
As long as you provide a valid JSON schema, any information going into the metastore will be validated against it.

To change the schema being used, copy the entire schema/collections directory out of dkan to your docroot directory (docroot/schema/collections).
Then make any modifications necessary to the `dataset.json` file inside the `collections` directory. Add your custom field(s) under "properties".

.. code:: json

  "properties": {
    "myNewField": {
      "title": "Custom Field",
      "description": "Some descriptive text.",
      "type": "string"
    },

  }

.. note::

  Note that even if you are only changing the dataset.json schema, it is important to copy ALL of the schema files as DKAN will be expecting all of the
  schema files to be in the same location.

.. warning::

  You can change or remove metadata fields from the dataset schema without causing issues, with the exception of the *distribution* property. If there is no array of objects called ``distribution``, each containing a ``downloadURL`` field, the :doc:`datastore <../components/dkan_datastore>` will not detect resource for import. This may or may not be an issue, depending on your use case. We hope to improve this in the future so that the datastore is not dependent on a particular metadata schema.

.. warning::

  The schema is actively used by the catalog to verify the validity of the data.
  Making changes to the schema after data is present in the catalog should be done with care
  as non-backward-compatible changes to the schema could cause issues.
  Look at ``Drupal::metastore::SchemaRetriever::findSchemaDirectory()`` for context.

.. note::

  For more information on how forms are generated from schema files, see :doc:`JSON Form Widget <../components/json_form_widget>`.
