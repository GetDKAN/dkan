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

## File Cache Endpoints

The Open Data Schema Map module now defines a drush command called `odsm-filecache`.  This command takes as  its argument the machine name for an ODSM endpoint.  For example:

```
drush odsm-filecache data_json_1_1;
```

The above command triggers the processing for the endpoint defined for the data_json_1_1 ODSM API and results in the following cached file being generated on completion:

```
public://odsm_cache_data_json_1_1
```

In order to enable the cached version of an API endpoint you need to run the command above replacing `data_json_1_1` with
the machine  name of the ODSM endpoint to be cached.

In order to update this cache you need to re-run the command that generated it.

We recommend you set up a cron job to run the command on a regular schedule, perhaps in sync with your data harvesting schedule.