# Open Data Schema Map

DKAN includes the Open Data Schema Map module, which is distributed in its own github repo and can be used in non-DKAN Drupal projects. [Visit its homepage for full documentation](https://github.com/NuCivic/open_data_schema_map).

## Adding or Updating Fields to API Output

With DKAN it is easy to [add new fields](../admin/addingfields.md) which become part of the form and page view. To add the output from a field we'll call "MY NEW FIELD" to the package_show endpoint 

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