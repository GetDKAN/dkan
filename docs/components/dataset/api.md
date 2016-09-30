# Dataset API

DKAN has a number of public APIs that are designed to provide catalog and dataset information as well as updates that allow observers to track and pull in changes.  

The public APIs are specifically designed to allow CKAN sites to harvest from DKAN based off of the APIs used for the [CKAN Harvester](https://github.com/ckan/ckanext-harvest/tree/master/ckanext/harvest/harvesters).

## Supported APIs

#### site_read

See: http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.site_read

Demo: http://demo.getdkan.com/api/3/action/site_read  

#### data.json

See: http://project-open-data.github.io/

Demo: http://demo.getdkan.com/data.json  

#### revision_list

See: http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.revision_list

Demo: http://demo.getdkan.com/api/3/action/revision_list  

#### package_list

See: http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_list

Demo: http://demo.getdkan.com/api/3/action/package_list  

#### current_package_list_with_resources

See: http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_list

Demo: http://demo.getdkan.com/api/3/action/current_package_list_with_resources  

#### package_show

See: http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show  

#### resource_show

See: http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show  

#### group_package_show

See: http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show  

#### package_revision_list

See: http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show  

#### group_list

See:  http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show

Demo: http://demo.getdkan.com/api/3/action/group_list?order_by=name&all_fields=TRUE  

## User Interface  

The APIs can be enabled by enabling the dkan_dataset_api module. The current APIs include the data.json file for Project Open Data as well as several CKAN Dataset APIs.  

![](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-04-23%20at%2011.19.45%20AM.png)  

DKAN also includes the [services](http://drupal.org/project/services) module with which one can open full CRUD endpoints for content like Datasets. This would take some custom code. The code required to do this will be added to DKAN soon.