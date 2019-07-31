# dkan_custom_solr
Settings for local development Solr server using [DKAN Tools](https://github.com/GetDKAN/dkan-tools).

DKAN is shipped with a search powered by the database, though it can be used in production environments using a Solr server. With this module you'll be able to use a Solr server in local development.

This modules contains the settings for creating a local Solr server which will have the index for datasets, groups and stories provided by DKAN.

Note: this server will be created using settings based on [DKAN Tools](https://github.com/GetDKAN/dkan-tools) Solr container, if you're not using DKAN Tools, you'll still be able to use it but may need to update the respective configuration in `admin/config/search/search_api/server/dkan_custom_solr/edit`.

If you want to stop using the local Solr server provided by this module, you can just disable it and revert the indexes to its original config in `admin/config/search/search_api`.
