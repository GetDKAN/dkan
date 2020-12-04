@page tut_metastore_properties How to add or remove entity generation for schema sub properties

When you create a dataset, additional data nodes will be created for specific sub-elements of the dataset as well, the default properties are: publisher, theme, keywords, and distribution. These data nodes will provide unique reference ids for the sub-elements and can be accesssed via an API endpoint. Learn more [here](https://demo.getdkan.org/api).

You can customize which sub-elements generate additional data nodes here `admin/config/dkan/properties`.

![dataset properties](https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan2/dataset-properties.png)

When the value of these elements change or become outdated, the corresponding data node will be unpublished by the **orphan_reference_processor** queue task.

If you prefer to run it manually, you may do so with:

```
drush queue-run orphan_reference_processor
```
