# CKAN Dataset API

DKAN provides a number of public, read-only APIs that are designed to provide catalog and dataset information as well as updates that allow observers to track and pull in changes.  These public APIs are specifically designed to allow CKAN sites to harvest from DKAN based off of the APIs used for the [CKAN Harvester](https://github.com/ckan/ckanext-harvest/tree/master/ckanext/harvest/harvesters).

All the APIs listed on this page are provided via the [Open Data Schema Map](https://github.com/NuCivic/open_data_schema_map) module.

## Supported APIs

### site_read

See: <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.site_read>

Demo: <http://demo.getdkan.com/api/3/action/site_read>  

### revision_list

See: <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.revision_list>

Demo: <http://demo.getdkan.com/api/3/action/revision_list>

### package_list

See: <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_list>

Demo: <http://demo.getdkan.com/api/3/action/package_list>  

### current_package_list_with_resources

See: <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_list>

Demo: <http://demo.getdkan.com/api/3/action/current_package_list_with_resources>

### package_show

See: <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show>

Demo: <http://demo.getdkan.com/api/3/action/package_show?id=5dc1cfcf-8028-476c-a020-f58ec6dd621c>

### resource_show

See: <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show>

### group_package_show

See: <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show>

### package_revision_list

See: <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show>

### group_list

See:  <http://docs.ckan.org/en/latest/api/index.html#ckan.logic.action.get.package_show>

Demo: <http://demo.getdkan.com/api/3/action/group_list?order_by=name&all_fields=TRUE>