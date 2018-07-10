# The Visualization Entity

You may also refer to the [Visualization Entity specific ReadTheDocs site.](https://visualization-entity.readthedocs.io/en/latest/)

The <a href="https://github.com/GetDKAN/visualization_entity">Visualization Entity</a> module provides a suite of options for creating visualizations in DKAN that used natively or embedded in other sites.

This module is under development. Documentation will be updated as the module is updated.

### The Visualization Entity module provides the following types of visualizations:

* Charts and Graphs
 * [More information](https://github.com/GetDKAN/visualization_entity_charts) (Under development)

* Choropleth maps
 * [More information]( https://visualization-entity.readthedocs.org/en/latest/create-a-choropleth-visualization/)

* I-Frame embedded content (Coming soon)

* GeoJSON based maps (Coming soon)
 * [More information](https://visualization-entity.readthedocs.org/en/latest/create-a-geojson-visualization/)

## How to create a chart:

1. Use the administration menu at the top to navigate to *Structure » Entity types » Visualization » Chart » Add Chart*

![Navigating to Structure -> Entity Types -> Visualizations -> Charts][Navigating to Structure -> Entity Types -> Visualizations -> Charts]

2. Enter values for the title and description tags.

3. There are three options for uploading a file to use with Charts:

*OPTION 1:* Using the Upload box, you can upload a file from your hard drive. Remember to click the Upload button after selecting the file.

![Uploading a file to Charts][Uploading a file to Charts]

*OPTION 2:* Begin typing the name of the resource the chart should be based on in the ‘Existing resource’ autocomplete field. Select the resource you want to use from the list. The ‘Source’ field at the bottom of the page should populate with a link to the file associated with the resource.

![Resource name autocomplete][Resource name autocomplete]

*OPTION 3:* Paste a link to the file in the box titled Source and choose the file type.

![Choosing the file source][Choosing the file source]

4. Click the ‘Next’ button. If the file or URL loaded properly, you will be able to fill out two fields beneath the title "Define Variables."

The first one, **Series**, determines the **Y axis**, and the second field, **X-Field**, determines the X axis. In these fields, you may choose the columns that you are going to display. Only the Series field can contain multiple values.

If the column names are not displayed properly, check again that your source URL was correct.

5. Click the Next button.

7. Select the type of chart you want to create and click Next once more. At this point, you will see a preview of your chart.

![Selecting your chart type][Selecting your chart type]

8. If the data is loading but the chart is not displaying it properly, you can modify it by using the options provided in the right column.

**X Format** allows you to provide X and Y axis labels, rotate X axis labels so that they fit (45 degrees, -45 degrees, 90 degrees, etc.), change chart colors from default, and modify chart and label margins.

 After editing and customizing the chart, click **Finish.**

## How to create a map:

1. Navigate to the dataset that will be used for the map.

2. Click **Explore Data**.

3. Right-click (ctrl-click on Mac) the **Download** button to copy the URL of the resource file.

4. Now use the administration menu at the top to navigate to *Structure » Entity types » Visualization » Map » Add Map*

5. Enter values for the title, description, categories and tags fields.

6. At the bottom of the form, paste the resource link into the Load Data "Source" field.

7. Click Next.

8. If the URL was loaded properly you will be able to select the columns where your geographic data is. It might be in two columns (Latitude and Longitude fields), or in a single field (Geo Point field). If the column names are not displayed properly, check again that your source URL was correct.

9. Click the ‘Finish’ button

**Enable Clustering (Optional):** Plotting thousands of markers on a map can quickly lead to a degraded user experience. Too many markers on the map can cause both visual overload and sluggish interaction with the map. To overcome poor performance, you can simplify the information displayed on the map by enabling the clustering feature. You can also choose to display the title or not by checking/unchecking the Show Title checkbox.

##How to create a table:

Tables allow you to display a preview of the data as it would be seen when opening it in Microsoft Excel or a similar program.

1. Use the administration menu at the top to navigate to *Structure » Entity types » Visualization » Table » Add Table*

2. Enter values for the title, description, categories and tags fields.

3. Start typing the name of the data resource you would like to use for your table into the ‘Resource’ field, a list will appear based on your entry, select the resource from the list.

4. Check the ‘Resize columns to fit data’ checkbox

5. Click Save.

<!-- Images -->
[Navigating to Structure -> Entity Types -> Visualizations -> Charts]: https://docs.getdkan.com/sites/default/files/Screen%20Shot%202016-01-21%20at%204.50.31%20PM.png
[Alt]: https://docs.getdkan.com/sites/default/files/Screen%20Shot%202016-01-21%20at%204.50.31%20PM.png
[Uploading a file to Charts]: https://docs.getdkan.com/sites/default/files/Screen%20Shot%202016-01-21%20at%205.07.22%20PM_0.png
[Resource name autocomplete]: https://docs.getdkan.com/sites/default/files/Screen%20Shot%202016-01-21%20at%205.37.49%20PM.png
[Choosing the file source]: https://docs.getdkan.com/sites/default/files/Screen%20Shot%202016-01-21%20at%205.38.39%20PM.png
[Selecting your chart type]: https://docs.getdkan.com/sites/default/files/Screen%20Shot%202016-01-21%20at%205.53.59%20PM.png
