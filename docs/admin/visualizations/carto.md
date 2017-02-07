# Publishing maps with CartoDB and DKAN

[The DKAN Datastore CartoDB module](https://github.com/NuCivic/dkan_datastore_cartodb) takes resources in [DKAN](https://github.com/NuCivic/dkan) and makes them editable in [CartoDB](http://cartodb.com/). The resulting visualizations can be displayed as previews for DKAN resources as well as to tell stories about your data elsewhere on your DKAN site.

CartoDB’s visualizations are built on a powerful [SQL API](http://docs.cartodb.com/cartodb-platform/sql-api.html). Once resources are added to CartoDB, their contents can also be queried. This allows users to make applications from your data.

## Install and configure DKAN Datastore CartoDB

Download the DKAN Datastore CartoDB to your existing DKAN site: [https://github.com/NuCivic/dkan_datastore_cartodb](https://github.com/NuCivic/dkan_datastore_cartodb). 

The module requires the [CartoDB PHP Client](https://github.com/Vizzuality/cartodbclient-php). See “Installation” on the [README.md](https://github.com/NuCivic/dkan_datastore_cartodb#installation) for details.

For general instructions on Drupal module installation see: [https://www.drupal.org/documentation/install/modules-themes](https://www.drupal.org/documentation/install/modules-themes)

Once the module is enabled, go to /admin/dkan/cartodb. Here you will enter your CartoDB credentials:

![DKAN CartoDB Admin Settings form](http://docs.getdkan.com/sites/default/files/DKANCartoDB.png)

Once you’ve saved your credentials you should see “Successfully connected to CartoDB.”

## Add a resource
Next, add a resource with a file attached to it:

![Adding a Resource](http://docs.getdkan.com/sites/default/files/addaresource.png)

Once the resource has been uploaded, click “Manage Datastore”:

![Manage Datastore](http://docs.getdkan.com/sites/default/files/ImportAllContent.png)

Click “Import” to start the process:

![Uploading](http://docs.getdkan.com/sites/default/files/Uploading.png)

When the uploading has finished, the “Data API” tab will now indicate that users can query the contents of your file:

![Creating a visualization](http://docs.getdkan.com/sites/default/files/visualization1.png)

## Visualizing data from CartoDB
Now that your resource has been added to CartoDB, it can be queried through CartoDB’s SQL API. It can also still be previewed by DKAN’s native [Recline](http://okfnlabs.org/recline/) data preview.

CartoDB’s tools allow you to take your data and make rich interactive maps. To do so, go to CartoDB and create a visualization using your data. See [CartoDB’s Editor documentation](http://docs.cartodb.com/cartodb-editor.html) for details.

Once a visualization has been created in CartoDB, it is available as the data preview for the resource.

To access it click “Manage Datastore” once more. You should see a list of available visualizations:

![Creating a visualization, step two](http://docs.getdkan.com/sites/default/files/visualization2.png)

Once you have selected a visualization to represent a resource it will be displayed on the resource itself:

![Map](http://docs.getdkan.com/sites/default/files/mappreview.png)
