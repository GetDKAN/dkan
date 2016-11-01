# Creating Internal Visualizations

## How to create a chart

1.  Navigate to the dataset you want to base your chart on
2.  Click the ‘Explore Data’ button
3.  Right-click (control-click) the download button to copy the URL of the resource file.
![Save the resource url][Save the resource url]
4.  Now use the administration menu at the top to navigate to Structure » Entity types » Visualization » Chart » Add Chart
![Add new visualization][Add new visualization]
5.  Enter values for the title, description, categories and tags fields.
![fields][fields]
6.  At the bottom of the form, paste the resource link you just copied into the ‘Source’ field.
![load data][load data]
7.  Click the ‘Next’ button.
8.  If the URL was loaded properly you will have two fields to fill under the title 'Define Variables'. The first one, 'Series' - stands for the Y axis, and the second field, X-Field, stands for the X axis. On these fields you have to choose the columns that you are going to display. Only the Series field can contain multiple values. If the column names are not displayed properly, check again that your source URL was correct. Keep the radio buttons checked in 'auto'.
![define variables][define variables]
9.  Click the ‘Next’ button.
10.  Now you can select the type of chart you want to create. Click on the image of the chart type you would like to use.
![chart types][chart types]
11. Click the ‘Next’ button.
12. If everything went ok, you should see your chart displayed. The data might be slightly misplaced so on the right column, you can edit the X Format for the labels (number, date, etc) , Label Rotation, Color of the lines / columns / etc, X and Y labels for the axis themselves and margins to move not only the labels but the chart as well.
13. After editing and customizing the chart to your liking, click the ‘Finish’ button.

##How to create a map

1.  Navigate to the dataset you want to base your chart on
2.  Click the ‘Explore Data’ button
3.  Right-click (control-click) the download button to copy the URL of the resource file.
![Save the resource url][Save the resource url]
4.  Now use the administration menu at the top to navigate to Structure » Entity types » Visualization » Map » Add Map
![Add new visualization][Add new visualization]
5.  Enter values for the title, description, categories and tags fields.
6.  At the bottom of the form, paste the resource link you just copied into the Load Data ‘Source’ field.
7.  Click the ‘Next’ button.
8.  If the URL was loaded properly you will be able to select the columns where your geographic data is. It might be in two columns (Latitude and Longitude fields), or in a single field (Geo Point field). If the column names are not displayed properly, check again that your source URL was correct. 
9.  Enable Clustering: Plotting thousands of markers on a map can quickly lead to a degraded user experience. Too many markers on the map can cause both visual overload and sluggish interaction with the map. To overcome poor performance, you can simplify the information displayed on the map by enabling the clustering feature.
10.   You can also choose to display the title or not by checking/unchecking the Show Title checkbox.
11.  Click the ‘Finish’ button
![Map example][Map example]

Example of a clustered map:

![cluster example][cluster example]

 <!-- Images -->
[lat and long]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%202.04.50%20AM.png
[clustered]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%202.04.30%20AM.png
[menu]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-06%20at%203.56.52%20PM_0.png
[fields]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%201.47.44%20AM.png
[dataset url]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%201.57.19%20AM.png
[load data]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%201.48.57%20AM.png
[define variables]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%201.50.00%20AM.png
[chart types]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%201.51.16%20AM.png
[final chart]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%201.53.54%20AM.png
[map menu]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%201.58.54%20AM.png
[map options]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%202.01.17%20AM.png
[lat long]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%202.04.50%20AM.png
[map ok]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%202.00.54%20AM.png
[clustered ok]: http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-08-07%20at%202.04.30%20AM.png
[Save the resource url]: http://docs.getdkan.com/sites/default/files/Screenshot_8_17_15__3_15_PM.png
[Add new visualization]: http://docs.getdkan.com/sites/default/files/Screenshot_8_17_15__5_19_PM.png
[Map example]: http://docs.getdkan.com/sites/default/files/Screenshot_8_18_15__9_35_AM.png
[cluster example]: http://docs.getdkan.com/sites/default/files/Screenshot_8_18_15__9_36_AM.png
