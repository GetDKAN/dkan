@page metastore Metastore
The **metastore** module is responsible for [schemas](https://json-schema.org/) (descriptions of data), and the data that conforms to those schemas.

**Metadata** is structured information that describes, explains, locates, or otherwise makes it easier to retrieve, use, or manage a data resource. The challenge is to define and name standard metadata fields so that a data consumer has sufficient information to find, process and understand the described data. The more information that can be conveyed in a standardized regular format, the more valuable data becomes.

There are a number of specifications for dataset metadata. By default, DKAN ships with a ["Project Open Data"](https://project-open-data.cio.gov/v1.1/schema/)-inspired schema for [datasets](https://github.com/GetDKAN/dkan/tree/2.x/schema).

It is possible to add new fields to conform to additional specifications or custom requirements. DKAN supports changing which schema is used to validate dataset data being added to the catalog.

To change the schema being used, copy the `schema` directory from the DKAN repo and place it in the root of your Drupal installation. Then make any modifications necessary to the `dataset.json` file inside the `collections` directory.

@warning
  The schema is actively used by the catalog to verify the validity of the data. Making changes to the schema, after data is present in the catalog should be done with care as non-backward compatible changes to the schema could cause issues. Look at Drupal::dkan_schema::SchemaRetriever::findSchemaDirectory() for context.
