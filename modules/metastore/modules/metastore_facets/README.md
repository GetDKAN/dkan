# Metastore Facets

This is an optional module for sites that are using a Drupal theme rather than the decoupled frontend. It provides example Categories, Tags, and Publishers facet blocks that you can use with the *dkan_dataset_search* view.

    drush en metastore_facets

Use the block layout screen at /admin/structure/block, and place the facets into a sidebar region, restrict the display to the /dataset-search page only.

If you don't see the view available, try

    drush cim --partial --source=modules/contrib/dkan/modules/metastore/modules/metastore_search/config/install

> :warning: **Existing sites be very careful here**: This config import will import the search view as well as new search API config. If you have custom search api configuration be sure to have it exported to code for safe keeping. To use the search view you will need to add the nid field to your configuration.
