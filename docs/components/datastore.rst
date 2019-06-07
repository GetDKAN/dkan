Datastore
=========

When you create a dataset with resources, DKAN is reading the data directly from the resource file or API.

The Datastore component provides an option for you to parse CSV or TSV files and save the data into database tables. This allows users to query the data through a public API.

So by adding your CSV resources to the datastore, you are getting the fullest functionality possible out of your datasets.

Drush Commands
--------------

=====================   =========  ============================
Command                 Args       Notes
=====================   =========  ============================
dkan-datastore:import   $uuid      import file to the datastore
dkan-datastore:drop     $uuid      drop the datastore table
=====================   =========  ============================