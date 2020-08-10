@page meta Metastore

The DKAN **Metastore** module is responsible for [schemas](https://json-schema.org/) (descriptions of data), and the data that conforms to those schemas.

### JSON Schema

Schema is a human and machine readable vocabulary that allows you to annotate a list of properties and validate JSON documents.

### Dataset

A dataset is an identifiable collection of structured data objects (distribution list) unified by some criteria (authorship, subject, scope, spatial or temporal extent…) this is called metadata.

### Metadata
**Metadata** is structured information that describes, explains, locates, or otherwise makes it easier to retrieve, use, or manage a data resource. The challenge is to define and name standard metadata fields so that a data consumer has sufficient information to find, process and understand the described data. The more information that can be conveyed in a standardized regular format, the more valuable data becomes.

There are a number of specifications for dataset metadata. By default, DKAN ships with a ["Project Open Data"](https://project-open-data.cio.gov/v1.1/schema/)-inspired schema for [datasets](https://github.com/GetDKAN/dkan2/tree/master/schema).

**Sample schema files**

- [JSON - Minimum Requirements](https://project-open-data.cio.gov/v1.1/examples/catalog-sample.json)
- [JSON - With Extended Fields](https://project-open-data.cio.gov/v1.1/examples/catalog-sample-extended.json)

![dataset structure](https://project-open-data.cio.gov/v1.1/schema-diagram.svg)

### Custom Metadata
It is possible to add new fields to conform to additional specifications or custom requirements. DKAN supports changing which schema is used to validate dataset data being added to the catalog.

To change the schema being used, copy the `schema` directory from the DKAN repo and place it in the root of your Drupal installation. Then make any modifications necessary to the `dataset.json` file inside the `collections` directory.

@warning
  The schema is actively used by the catalog to verify the validity of the data. Making changes to the schema, after data is present in the catalog should be done with care as non-backward compatible changes to the schema could cause issues. Look at Drupal::metastore::SchemaRetriever::findSchemaDirectory() for context.
