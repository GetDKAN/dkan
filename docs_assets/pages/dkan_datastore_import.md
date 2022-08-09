@page dkandatastoreimport dkan:datastore:import

You can manually import file data into the datastore with the identifier of the distribution. There are two main ways to get the distribution uuid:
- Use the [API](https://demo.getdkan.org/api/1/metastore/schemas/dataset/items?show-reference-ids) to get the identifier of the file you want to import. The identifier will be at ``distribution.0.data.%Ref:downloadURL.0.data.identifier``
- Use @ref dkandatasetinfo

#### Arguments

- **uuid** The uuid of a resource.
- **deferred** Whether or not the process should be deferred to a queue.

#### Aliases

- dkan-datastore:import
