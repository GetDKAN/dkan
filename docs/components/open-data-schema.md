# Open Data Schema Mapping in DKAN

Placeholder for ODSM DKAN module when moves into core.

## Data.JSON

DKAN provides a "data.json" index to satisfy the US federal government's Project Open Data requirements. This module is disabled by default but can be enabled quite easily. More information about the "slash data" or "data.json" requirements can be found at the following pages:  

*   [Project Open Data Catalog Requirements](http://project-open-data.github.io/catalog/)
*   [Common Core Metadata Schema](http://project-open-data.github.io/schema/)

**Enabling the 'DKAN Open Data' module**

1.  Login to your DKAN site as an administrator at SITENAME/user/login
2.  In the administration menu, select the "Modules" option
3.  On the "Modules" page, scroll to "DKAN Open Data" in the "DKAN" section
4.  Enable the module by clicking its checkbox and then clicking the "Save configuration" button at the bottom of the page
5.  Confirm your site's data catalog is now visible at SITENAME/data.json

## Adding or Updating Fields to API Output

With DKAN it is easy to [Add New Fields](/dkan-documentation/dkan-developers/adding-fields-dkan) which become part of the form and page view. To add the new field to the output of one of DKAN's APIs such as "package_show" or "data.json", install the [Open Data Schema Mapper](https://github.com/NuCivic/open_data_schema_map) and [Open Data Schema Mapper DKAN](https://github.com/NuCivic/open_data_schema_map_dkan) modules if they are not already included in your version of DKAN. See Open Data Schema Mapper's [README](https://github.com/NuCivic/open_data_schema_map/blob/master/README.md) for more details about that module.

## Adding "MY NEW FIELD" to package_show Endpoint

To add the output from MY NEW FIELD to the package_show endpoint 

+ Go to ```/admin/config/services/odsm/edit/ckan_resource_show```
+ Choose a field to add the output to:

![open data schema mapper](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-02%20at%202.43.49%20PM.png)

+ Click or select the token for "MY NEW FIELD" under "Replacement Patterns"

![token list](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-02%20at%202.45.33%20PM.png)

+ Make sure token is placed in desired field:

![value entered](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-02%20at%202.45.51%20PM.png)

+ Click **Save**
+ Output from "MY NEW FIELD" should be visible in package_show endpoint:

![endpoint](http://docs.getdkan.com/sites/default/files/package_show_Example.png)

Note that the "position" field now has the value from a node we created with "MY NEW FIELD" in the <a href="/dkan-documentation/dkan-developers/adding-fields-dkan">Add New Fields</a> example.