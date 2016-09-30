DKAN Datastore
==============

DKAN features a Datastore for uploaded files. Currently supported files are CSV and XML. Any type of file can be uploaded to DKAN but will not be parsed and stored in the Datastore.

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

.. toctree::
   :maxdepth: 1
   
   datastoreAPI