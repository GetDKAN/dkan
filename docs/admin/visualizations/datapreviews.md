# DKAN Data Preview Features

DKAN allows users to have a preview of their data uploaded to or linked to a _resource_. Which preview type is displayed for a particular _resource_ depends on the data format selected. If no data format is provided, DKAN will attempt to auto-detect the format from the file's metadata; re-editing the _resource_ and correcting the format may be necesarry if this is not successfull.

## Recline.js
DKAN, like CKAN, offers an integration with the [Recline](http://reclinejs.com) Javascript library. Recline allows site visitors to preview tabular data visually. The preview works for CSV and XLS files that are uploaded to the DKAN site or hosted remotely and linked to, as well as for data stored in DKAN's local SQL-based datastore.

### Grid View

All tabular data can be rendered as spreadsheet-style rows and columns:

![Grid view screenshot](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-08%20at%204.46.35%20PM.png)

### Map View

Visitors can preview data that contains either coordinates or GeoJSON on a [Leaflet.js](http://leafletjs.com/)-based map:

![Map view screenshot](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-08%20at%204.45.28%20PM.png)

### Graph View

If enabled, visitors can chose one column of your data as an X-axis, one or more as Y-axis data, and preview your data as a bar, point or line graph.

![Graph view screenshot](http://docs.getdkan.com/sites/default/files/1%202012%20Foreclosures%20by%20State%20DKAN.png)

### File size limits

Files can only be previewed if they are well formatted or small enough to render in the browser.

If files are too large to preview within 1 second you will get the following message "File was too large or unavailable for preview."

Files that are too large to preview in the browser can be previewed by <a href="/dkan-documentation/dkan-developers/dkan-datastore">adding them to the datastore</a>. Once a file is in the datastore the preview is only asking for the first 25 rows of the data. Thus large datasets can be previewed.

## Additional Preview Types

Beginning with version 1.13, DKAN provides preview formats for several additional file types beyond what is supported by Recline.js. (These preview formatters are still defined in the [Recline](https://github.com/NuCivic/recline) module however.)

### Zip files

DKAN offers the ability to preview the files and folders locked in ZIP files. DKAN will display a list of contents for ZIP files uploaded as resources on datasets. (Unlike recline visual previews, the zip file preview does not work with remote linked files.)

![zip preview](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-09-22%20at%202.58.10%20PM.png)

### Image files

Image files (JPG, PNG or GIF) uploaded as resources will be displayed directly on the resource page.

### Web Map Service (WMS)

DKAN can use Leaflet to display a preview of a [WMS server](https://en.wikipedia.org/wiki/Web_Map_Service), provided an endpoint. The following screenshot comes from a North Dakota WMS endpoint at [http://ndgishub.nd.gov/arcgis/services/All_Communications/MapServer/WMSServer?](http://ndgishub.nd.gov/arcgis/services/All_Communications/MapServer/WMSServer?). 

![wms preview](images/wms.png)

WMS support in DKAN is still somewhat expiremental and your results may vary.

### ArcGIS REST

An ESRI/ArcGIS REST endpoint may also be displayed in a Leaflet preview. 

![arcgis preview](images/arcgis.png)

Make sure to select "arcgis" as the format when you create these resources.

### JSON files

### GeoJSON files

### XML files

### PDF files

## External Previews

Starting with version DKAN supports previewing/opening resources in external services that offer simple URL-based integrations. For instance, the CartoDB mapping service offers an [Open in CartoDB service](https://cartodb.com/open-in-cartodb). Enabling this for CSV files will result in a dataset display like this:

![External preview example](http://docs.getdkan.com/sites/default/files/2015-11-11_13-13-34.png)

## Configuration
By default previews are available for resources with files below 3MB of size. However you can customize this limit in the recline configuration page (/admin/dkan/recline).

![Configuration screenshot](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202016-05-31%20at%2012.57.41%20PM.png)
