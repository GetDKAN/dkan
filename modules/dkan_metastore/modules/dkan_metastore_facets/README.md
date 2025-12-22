# Metastore Facets

This is an optional module for sites that are not using a decoupled frontend. It provides example Categories, Tags, and Publishers facet blocks that you can use with the *dkan_dataset_search* view.

    drush en metastore_facets

Use the block layout screen at /admin/structure/block, and place the facets into a sidebar region, restrict the display to the /dataset-search page only.

If you do not see the dataset search view, you will need to run the following to get the view and facets working:

    composer require drupal/facets:3.0.x-dev

    drush en metastore_facets

    drush cim --partial --source=modules/contrib/dkan/modules/metastore/modules/metastore_search/config/install

<aside class="admonition warning">
    <p class="admonition-title">Existing sites be very careful here</p>
    <p>This config import will import the search view as well as new search API config. If you have custom search api configuration be sure to have it exported to code for safe keeping.</p>
</aside>

The search view and facets are for demonstration purposes and provide a starting step for your own implementation.
