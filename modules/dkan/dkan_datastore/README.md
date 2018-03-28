# DKAN Datastore

DKAN Datastore bundles a number of modules and configuration to allow users to upload CSV files, parse them and save them into the native database as flat tables, and query them through a public API.

## Basic Architecture

CSV and XML files uploaded to DKAN through the "Add Resources" form or through the API are parsed and inserted into unique tables in the DKAN database. Because Drupal has a pluggable database layer DKAN's database can MySQL, PostgreSQL, or MS SQL Server.

### Manual Processing

Files are parsed and inserted in batches. The user has the option of parsing them upon form submission. If the user chooses to parse the file manually they are able to see the progress of the processing through a batch operations screen similar to the one below. 

![Drupal batch operation](http://drupal.org/files/images/computed_field_tools_drupal7_batch.png)

### Cron Processing

Files that are not processed manually are processed in pieces during cron.

### Datastore API

Once processed, Datastore information is available via the Datastore API. For more information, see the Datastore API page.

### User Interface

DKAN provides UI for managing the Datastore. (PICTURE SHOULD GO HERE) Management activities include:

*   Importing items
*   Deleting items
*   Editing the schema (see below)
*   Edit Views integration

(PICTURE OF EDITING SCHEMA BELONGS HERE)

### Drupal Architecture

The DKAN Datastore is managed by the Feeds module. Custom plugins were created for the Feed fetcher and processor to make the file uploaded to the resource form a feed item.

## Geocoder

DKAN's native Datastore can use the Drupal [geocoder](https://www.drupal.org/project/geocoder) module to add latitude/longitude coordinates to resources that have plain-text address information. This means that datasets containing plain-text addresses can be viewed on a map using the [Data Preview](dkan-documentation/dkan-features/data-preview-features) or more easily used to build map-based data visualizations.

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
```Z