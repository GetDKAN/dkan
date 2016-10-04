# Overview of Component Modules

The DKAN software suite is comprised of a number of components, or “modules” as they are known in the Drupal software world.  These modules can be installed into an existing Drupal site to provide DKAN functionality but are more often found as part of the entire DKAN “distribution.”

## DKAN-Provided Modules and Components

### DKAN Dataset (Module)

+ Provides the “Dataset” and “Resource” content types, DCAT compliant
+ Publishes datasets in RDF, RDFa, and JSON
+ Provides API read access to Datasets, Resources and Groups trough the **dkan_dataset_api** submodule
+ Provides data.json site data catalog access trough the **dkan_dataset_api** submodule

### DKAN Datastore (Module)

+ Parses uploaded CSVs and stores data in native tables
+ Provides queryable API for data through the **dkan_datastore_api** submodule

### DKAN Sitewide (Context-related Modules)

+ Provides a proposed demo front thought the **dkan_sitewide_demo_front** submodule
+ Provides a proposed set of roles and permissions through the **dkan_sitewide_roles_and_permission** submodule
+ Provides sitewide search through the **dkan_sitewide_search** submodule (**search_api** implementation)

## 3rd-Party Modules Required by DKAN Modules

### DKAN Dataset

+ autocomplete_deluxe
+ beautytips
+ chosen
+ ctools
+ date
+ date_popup
+ double_field
+ entity
+ entityreference
+ eva
+ features
+ field_group
+ field_group_table
+ jquery_update
+ libraries
+ link
+ link_iframe_formatter
+ multistep
+ og
+ og_extras
+ rdfx
+ ref_field_sync
+ restws
+ select_or_other
+ strongarm
+ token
+ uuid
+ views
+ views_datasource

Please review [dkan_dataset.make file in github](https://github.com/nuams/dkan_dataset/blob/7.x-1.x/dkan_dataset.make) for an up to date list of versioned modules.

### DKAN Datastore

+ ctools
+ data
+ feeds
+ feeds_field_fetcher
+ feeds_flatstore_processor
+ schema

Please review [dkan_datastore.make file in github](https://github.com/nuams/dkan_datastore/blob/7.x-1.x/dkan_datastore.make) for an up to date list of versioned modules.
