 @page metastore Metastore 
 The metastore is responsible for [schemas](https://json-schema.org/) (descriptions of data), and the data that conforms to those schemas.
 
 By default, DKAN ships with a ["Project Open Data"](https://project-open-data.cio.gov/v1.1/schema/)-inspired schema for [datasets](https://github.com/GetDKAN/dkan2/tree/master/schema).
 
 DKAN supports changing which schema is used to validate dataset data being added to the catalog.
 
 To change the schema being used, copy the `schema` directory from the DKAN repo and place it in the root of your Drupal installation. Then make any modifications necessary to the `dataset.json` file inside the `collections` directory.
 
 @warning
    The schema is actively used by the catalog to verify the validity of the data. Making changes to the schema, after data is present in the catalog should be done with care as non-backward compatible changes to the schema could cause issues. Look at Drupal::dkan_schema::SchemaRetriever::findSchemaDirectory() for context.
