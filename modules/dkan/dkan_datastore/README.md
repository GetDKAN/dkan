# DKAN Datastore

DKAN Datastore bundles a number of modules and configuration to allow users to upload CSV files, parse them and save them into the native database as flat tables, and query them through a public API.

DKAN Datastore is part of the DKAN distribution which makes it easy to create an Open Data Portal.

DKAN Datastore is part of the [DKAN](https://drupal.org/project/dkan "DKAN homepage") project which includes the [DKAN profile](https://drupal.org/project/dkan "DKAN homepage") which creates a standalone Open Data portal, and [DKAN Dataset](https://drupal.org/project/dkan_dataset "DKAN Datastore homepage").

DKAN Datastore is currently managed in code on Github but is mirrored on Drupal.org.

## INSTALLATION

This module REQUIRES implementers to use "drush make". If you only use "drush download" you will miss key dependencies for required modules and libraries.

The following will download the required libraries and patched modules:

```bash
drush dl dkan_datastore
cd dkan_datastore
drush make --no-core dkan_datastore.make
```

## Managing datastores with Drush

The DKAN Datastore API module provides the functionality needed to manage the
datastores using Drush. The available commands are:

### To create a datastore from a local file:

```bash
drush dsc (path-to-local-file)
```

### To update a datastore from a local file:

```bash
drush dsu (datastore-id) (path-to-local-file)
```

### To delete a datastore file (imported items will be deleted as well):

```bash
drush dsfd (datastore-id)
```

### To get the URI of the datastore file:

```bash
drush dsfuri (datastore-id)
```

## Using without DKAN

This module can be used without DKAN. It requires a node and either file or link field to supply a file. By default it looks for a "resource" node with a "field_upload" field.

To change this default, use "hook_dkan_datastore_file_upload_field_alter()" and / or "hook_dkan_datastore_node_type_alter()". For example the following will change the expected node and file field to "article" and "file_myfile":

```

/**
 * Implements  hook_dkan_datastore_file_upload_field_alter().
 */
function MY_MODULE_dkan_datastore_file_upload_field_alter(&$field) {
  $field = 'field_myfile';
}

/**
 * Implements  hook_dkan_datastore_node_type_alter().
 */
function MY_MODULE_dkan_datastore_node_type_alter(&$node_type) {
  $node_type = 'article';
}
```

Now "article" nodes will have "Manage Datastore" and "Data API" tabs. CSV files uploaded with the "file_myfile" field can be imported into the datastore and queried via the DKAN Datastore API.

## Design

## Adding new Datastores

## Managing datastores with Drush

The DKAN Datastore API module provides the functionality needed to manage the
datastores using Drush. The available commands are:

### To create a datastore from a local file:

```bash
drush dsc (path-to-local-file)
```

### To update a datastore from a local file:

```bash
drush dsu (datastore-id) (path-to-local-file)
```

### To delete a datastore file (imported items will be deleted as well):

```bash
drush dsfd (datastore-id)
```

### To get the URI of the datastore file:

```bash
drush dsfuri (datastore-id)
```

## Contributing

We are accepting issues in the dkan issue thread only -> https://github.com/NuCivic/dkan/issues -> Please label your issue as **"component: dkan_datastore"** after submitting so we can identify problems and feature requests faster.

If you can, please cross reference commits in this repo to the corresponding issue in the dkan issue thread. You can do that easily adding this text:

```
NuCivic/dkan#issue_id
```

to any commit message or comment replacing **issue_id** with the corresponding issue id.
