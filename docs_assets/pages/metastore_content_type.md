@page data Metastore Content Type

The **Metastore Content Type** module provides the connection to [Drupal](https://drupal.org) as a means of storage, it adds a content type called *data*.

This content type will hold the metadata of a dataset in JSON format. Additional data nodes will be created for specific sub-elements of the dataset as well, (i.e. publisher, theme, keywords, and distribution). These data nodes will provide unique reference ids for the sub-elements and can be accesssed via an API endpoint. Learn more [here](datasetapi.html#identifiers).

You can customize which sub-elements generate additional data nodes here `admin/config/dkan/properties`.

![dataset properties](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/dataset-properties.png)

When the value of these elements change or become outdated, the corresponding data node will be removed by the **orphan_reference_processor** queue task.

If you prefer to run it manually, you may do so with:

```
drush queue-run orphan_reference_processor
```
