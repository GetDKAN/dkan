Open Data Schema Map DKAN
=========================

Default Open Data Schema Map endpoints for DKAN. Includes CKAN and Project Open Data endpoints.

## Project Open Data

Provides default mappings between DKAN and POD's [data.json](https://project-open-data.cio.gov/v1.1/schema/).

### Notes

* The ["name" field on data.json's "publisher" object](https://project-open-data.cio.gov/v1.1/schema/#publisher) maps to a dataset's group (see [Organic Groups](https://www.drupal.org/project/og)) in DKAN. Note that while it is possible to assign a dataset to multiple groups in DKAN, data.json only allows for a single publisher. If a dataset belongs to multiple groups, only the first group will be exposed as the "publisher" in data.json

## CKAN

Provides endpoints for publishing via the [CKAN API](http://docs.ckan.org/en/latest/api/)

* ckan_package_show
* ckan_current_package_list_with_resources
* ckan_group_list
* ckan_group_package_show
* ckan_package_list
* ckan_package_show
