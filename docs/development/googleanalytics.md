# Google Analytics Reports

It is easy to create custom reports to access information specifically useful for DKAN sites. Once you have <a href="http://drupal.org/project/google_analytics">installed the Google Analytics module</a> and added your tracking code you are ready to begin.

The following will create reports that provide details on popular datasets and resources as well as file downloads. 

## Datasets and Resources Report
1. Login to Google Analytics and go to your DKAN web site overview

2. Click "Customization" -> "New Custom Report"

3. Add "Datasets and Resources" as title

4. Add "Datasets" tab

5. Add "Metric Group" of "Pageviews" and "Dimension Drilldowns" of "Page"

6. Add "Filters" to include "dataset" and "exclude" resource. This will make a selection for ONLY datasets and not their associated resources
![drill downs] (http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-12-16%20at%2012.58.54%20PM.png)

7. Add "Resources" tab

8. Repeat step 5

9. Add "Filters" to include "dataset/*/resource".  This will make a selection for ONLY resources and not their associated datasets

![resources](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-12-16%20at%201.02.25%20PM.png)

## Files Report
The Drupal Google Analytics module automatically tracks file downloads. To access and customize this report take the following steps.

1. Click "Behavior" -> "Events" -> "Overview" -> "Downloads"

![file download  reports](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-12-16%20at%201.06.54%20PM.png)

2. This will provide a report with all file downloads. To add to the "Custom Reports" list or customize for specific file types click "Customize". 