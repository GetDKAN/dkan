@page data Data

The **dkan_data** module provides the connection to [Drupal](https://drupal.org) as a means of storage.

## What is a Dataset

A dataset is an identifiable collection of structured data objects (distribution list) unified by some criteria (authorship, subject, scope, spatial or temporal extent…) as well as <a href="metastore.html">metadata</a>.

### Sample Data Files

- [JSON - Minimum Requirements](https://project-open-data.cio.gov/v1.1/examples/catalog-sample.json)
- [JSON - With Extended Fields](https://project-open-data.cio.gov/v1.1/examples/catalog-sample-extended.json)

![dataset structure](https://project-open-data.cio.gov/v1.1/schema-diagram.svg)

### Properties

DKAN adds a content type called *data*. This content type will hold the metadata of a dataset in JSON format. Additional data nodes will be created for specific sub-elements of the dataset as well, (i.e. publisher, theme, keywords, and distribution). These data nodes will provide unique reference ids for the sub-elements and can be accesssed via an API endpoint. Learn more [here](guide-dataset-api.html#identifiers).

You can customize which sub-elements generate additional data nodes here `admin/config/dkan/properties`.

![dataset properties](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan/dataset-properties.png)

When the value of these elements change or become outdated, the corresponding data node will be removed by the **orphan_reference_processor** queue task.

If you prefer to run it manually, you may do so with:

```
drush queue-run orphan_reference_processor
```
