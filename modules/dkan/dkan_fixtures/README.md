## What is DKAN Fixtures?

DKAN Fixtures if a module that can be used to easily export all the content that lives inside a
DKAN site into JSON fixture files. Currently the content supported by the module are Groups, Resources, Datasets,
Visualization Entities, Data Dashboard, Data Stories and Pages. 
The module also provides basic Migrate classes that can be used to import content easily on a DKAN site.

## What is DKAN Default Content?

DKAN Default Content is the module that holds all the default content that's delivered with DKAN. All content is
imported through the fixtures that can be found inside the /data directory. DKAN Fixtures was used to generate 
the default content fixtures and to migrate all the data using the migration clases that are provided.

## How to export the default content fixtures

The default content fixtures can be updated easily through the default content module using the following steps:

1. All the content present on the DB is going to be exported so be sure to clean all the content first.
2. Run ```drush dkan-save-data```
3. All default content fixtures should be updated and saved inside the 'data' directory in the dkan_default_content module.

Please note that some rules should be followed when preparing the default content:

* Only published datasets are going to be exported.
* Only resources associated with datasets are going to be exported.
* Only groups that have associated datasets are going to be exported.
* The weight of the dkan_default_content module should be kept as slow as possible. In order to do that small files and images should be used.
* When using internal visualizations as visualization embeds be sure to use the 'Local' field on the visualization embed settings.

## How to import the default content

All the default content is imported automatically as soon as the DKAN Default Content module is enabled: 

```drush en dkan_default_content -y```

## How to remove the default content

All the default content is going to be automatically removed as soon as the DKAN Default Content module is disabled 
with the exception of pages (Homepage, About page, etc):

```drush dis dkan_default_content -y```

## How to upgrade pages from panel pages to node pages

Starting from Dkan 1.13 the Homepage has been converted from a Panel Page into a common Node Page. DKAN provides a function
that can be used to make that conversion automatically. The provided function will take care of:

- Generate an copy of the specified panel page automatically.
- A new panelizer page node will be generated and the previous panel page will be disabled, not deleted.
- The new node page will be set up as the site homepage if the $is_homepage parameter is set as 'true'.
- Please note that some CSS adjustments might be needed in order for the node page to look exactly like the panel page since CSS IDs and classes might be different.

The function can be found in the dkan_sitewide module and can be used as follows:

```drush php-eval "convert_panel_page(<page-name>);"```
