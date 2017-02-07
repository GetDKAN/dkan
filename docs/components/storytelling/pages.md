#  Panelized pages

DKAN uses [Panels](https://www.drupal.org/project/panels) as a layout manager for all pages. Panels lets the site editor lay out page elements called "panes." These are essentially the same as ["blocks" in the Context and core Blocks modules](https://www.drupal.org/documentation/blocks), and all blocks are available as panes within Panels. Depending on the page you're working one, however, certain additional panes may also be available that would not appear as blocks outside of Panels. Note that in many Panels dialogs the words "pane" and "content" are used interchangeably.

## Using the in-place editor (IPE)

All panelized pages give higher user roles access to the Panels In-Place Editor (IPE) interface. See [Drupal's documentation for more information on this tool](). 

### DKAN's customized IPE interface

[Placeholder for explanation of the custom IPE thing]

### Altering properties of existing components in Panels-based pages

Go to a page managed by a Panels layout (in this case the DKAN homepage) and click "Customize this page":

![Managed page](http://docs.getdkan.com/sites/default/files/panels-1.png)

Use the drag and drop controls to rearrange a given pane:

![Managed page](http://docs.getdkan.com/sites/default/files/panels-2_0.png)

Save changes:

![Managed page](http://docs.getdkan.com/sites/default/files/panels-4.png)

The updated page gets rendered:

![Managed page](http://docs.getdkan.com/sites/default/files/panels-5.png)

### Adding new panes to Panels-based pages

Go to a page managed by a panel layout and click **Customize this page**:

![Managed page](http://docs.getdkan.com/sites/default/files/panels-5.png)

Click the "plus" sign ("+") to add a new pane to any given panel region:

![Managed page](http://docs.getdkan.com/sites/default/files/panels-5a_0.png)

Select which pane to add:

![Managed page](http://docs.getdkan.com/sites/default/files/panels-5aa_0.png)
![Managed page](http://docs.getdkan.com/sites/default/files/panels-5b_0.png)

New pane is added to layout on save:

![Managed page](http://docs.getdkan.com/sites/default/files/panels-5c_0.png)

### Altering the layout of an existing page

Go to the page that need changes and click **Change Layout**:
![Managed page](http://docs.getdkan.com/sites/default/files/alter-1.png)

Select new layout:
![Managed page](http://docs.getdkan.com/sites/default/files/alter-2.png)

Choose where existing panes belong in the new layout:
![Managed page](http://docs.getdkan.com/sites/default/files/alter-3_0.png)

Click Save and enjoy the new page:
![Managed page](http://docs.getdkan.com/sites/default/files/alter-4.png)

## Featured Group Grid Pane

![Featured groups display][Featured groups display]
DKAN comes with a featured groups grid pane that you can add to any panelized page. To add it,

1. Select the region where you want to add featured groups, and click the upper left gear and select 'Add Content’.

2. On the overlay that appears, select the ‘View panes’ category on the left, then click the ‘+Add’ button on the Featured Group grid pane option.

3. The next screen will ask how many groups to display. The grid is 6 columns wide so enter 6, 12, 18, or 0 to display all groups. Click ‘Finish’ to save.

4. Then click the ‘Update and save’ on the panel configuration screen.

![Select the featured groups pane][Select the featured groups pane]
![Enter the number of groups][Enter the number of groups]

To change the order in which the groups display, navigate to the `[siteroot]/admin/dkan/featured-groups-sort-order` screen. You should see a link to it in the black Administration Menu Bar at the top under DKAN.
![Change the order of the groups][Change the order of the groups]

## Dataset list filtered by group and/or tag
If you would like to add a list of datasets filtered by a specific group or by a certain tag, follow these steps:

1.  Select the region where you want to add the dataset list, and click the upper left gear and select 'Add Content’.

2.  On the overlay that appears, select the ‘View panes’ category on the left, then click the ‘+Add’ button on the Custom Filtered Dataset List option.

3. The next screen show the settings for the panel. You can set a custom title, set the pager, filter by group or by tag. Click ‘Finish’ to save.

4. Then click the ‘Update and save’ on the panel configuration screen.
![Add a filtered dataset list][Add a filtered dataset list]
![Adjust the list settings][Adjust the list settings]

## DKAN Pages that can be managed through the In-Place Editor

In addition to the front page, the following pages have been converted to panels in the DKAN distribution s far:

### Datasets Page

![Managed page](http://docs.getdkan.com/sites/default/files/panels-6.png)
![Managed page](http://docs.getdkan.com/sites/default/files/panels-7.png)
![Managed page](http://docs.getdkan.com/sites/default/files/panels-8.png)
![Managed page](http://docs.getdkan.com/sites/default/files/panels-9.png)

### Dataset Page

![Managed page](http://docs.getdkan.com/sites/default/files/panels-10.png)
![Managed page](http://docs.getdkan.com/sites/default/files/panels-11.png)
![Managed page](http://docs.getdkan.com/sites/default/files/panels-12.png)
![Managed page](http://docs.getdkan.com/sites/default/files/panels-13.png)
![Managed page](http://docs.getdkan.com/sites/default/files/panels-14.png)

## Data Dashboards and the In-Place Editor

Data dashboards are blank panelized pages that can be filled in with any available widgets. If you want to easily create dashboard pages you can use the "Data Dashboard" content type provided by DKAN as shown on the following steps:

1. Go to the Data Dashboards section following DKAN > Data Dashboards on the navigation menu.

2. Click on 'Create Dashboard'.

3. Fill in the data dashboard title and choose the desired layout.

![Managed page](http://docs.getdkan.com/sites/default/files/data_dashboards_01.png)

4. Click 'Save' and that's it! After the data dashboard is created you can start adding content to the page using the IPE editor.

![Managed page](http://docs.getdkan.com/sites/default/files/data_dashboards_02.png)

<!-- Images -->
[Featured groups display]: 
[Select the featured groups pane]: http://docs.getdkan.com/sites/default/files/featured-groups-pane.png
[Enter the number of groups]: http://docs.getdkan.com/sites/default/files/featured-groups-items.png
[Change the order of the groups]: http://docs.getdkan.com/sites/default/files/featured-groups-sort.png
[Add a filtered dataset list]: http://docs.getdkan.com/sites/default/files/filtered-dataset-add.png
[Adjust the list settings]: http://docs.getdkan.com/sites/default/files/filtered-dataset-settings.png
