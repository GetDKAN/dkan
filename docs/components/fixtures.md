# Fixtures and Default Content

_Fixtures_ are a programming concept for default data that is included with application code for testing or other purposes. The data is provided in a structured format like XML or JSON, and imported into the database as part of a build process.

In DKAN, fixtures are used to provide datasets and other supporting content out of the box. The most visible use case for this will be DKAN's default content, which showcases DKAN's various capabilities. The fixtures themselves for default content are packaged in a separate sub-module, [DKAN Default Content](https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_fixtures/modules/dkan_default_content).

The DKAN Fixtures module provides tools to easily export all the content that lives inside a
DKAN site into JSON fixture files, following a defined schema. Currently the content supported by the module are Groups, Resources, Datasets, Data Dashboards, Data Stories and Pages. [Visualization Entites](https://github.com/NuCivic/visualization_entity) are also supported.

The module also provides basic Migrate classes that can be used to import content easily on a DKAN site.

## Default Content Module

DKAN Default Content is the module that holds all the default content delivered with DKAN. All content is
imported through the _fixtures_ that can be found inside the /data directory. [DKAN Fixtures](https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_fixtures) was used to generate
the default content fixtures and to migrate all the data using the migration clases that are provided.

### Updating the fixtures

The default content fixtures can be updated easily through the DKAN Default Content module using the following steps:

1. All the content present on the DB is going to be exported, so be sure to clean all the content first.
2. Run ```drush dkan-save-data```
3. All default content fixtures should be updated and saved inside the 'data' directory in the dkan_default_content module.

Please note that some rules should be followed when preparing the default content:

* Only published datasets are going to be exported.
* Only resources associated with datasets are going to be exported.
* Only groups that have associated datasets are going to be exported.
* The size of of the dkan_default_content module should be kept as small as possible, so small files and images should be used.
* When using internal visualizations as visualization embeds be sure to use the 'Local' option on the visualization embed settings, so that embeds are not pointing to a specific domain.

### Importing default content

All the default content is imported automatically as soon as the DKAN Default Content module is enabled. Enable the DKAN Default Content module in the browser via admin/modules, or on the command line via drush:

```drush en dkan_default_content```

### Removing the default content

All the default content is automatically removed as soon as the DKAN Default Content module is disabled
with the exception of pages (Homepage, About page, etc). Disable the DKAN Default Content module in the browser via admin/modules, or on the command line via drush:

```drush dis dkan_default_content```

### Upgrading pages from ctools panel pages to page nodes

Starting with DKAN 7.x-1.13, the default homepage has been converted from a Panel Page into a common page node. Page nodes now have panelizer enabled, so the full layout of a panel page can be reproduced in a simple page node. DKAN now provides a function that can be used to perform this conversion automatically. The provided function will:

- Generate an exact copy of the specified panel page automatically.
- Generate a new panelized page node and disable (not delete) the old ctools panel page homepage
- The new node page will be set as the site homepage if the $is_homepage parameter is set as 'true'.

Please note that some CSS adjustments might be needed in order for the node page to look exactly like the panel page, as CSS IDs and classes might be different.

The function can be found in the dkan_sitewide module and can be used as follows:

```drush php-eval "dkan_sitewide_convert_panel_page(<page-name>);"```

### About paths
Pathauto is disabled for content created using dkan_fixtures because performance reasons. Instead, paths should be added to the fixtures using the path key.

## Taxonomy Fixtures

A similar system exists for importing and exporting taxonomy terms as default content. The NuCivic-created [Taxonomy Fixtures module](https://github.com/NuCivic/taxonomy_fixtures) ships with DKAN but is available for us in other Drupal projects.
