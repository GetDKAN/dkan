Dataset Preview Features
========================

Recline.js
-------------

DKAN, like CKAN, offers an integration with the `Recline <http://reclinejs.com>`_ Javascript library. Recline allows site visitors to preview tabular data visually. The preview works for CSV and XLS* files that are uploaded to the DKAN site or hosted remotely and linked to, as well as for data stored in DKAN's local SQL-based datastore.

Grid View
*************
All tabular data can be rendered as spreadsheet-style rows and columns:

.. figure:: ../../images/csv-preview.png

*For xls files be sure to fill in the format field to see previews of the data

.. figure:: ../../images/xls-format.png

Map View
*************

Visitors can preview data that contains either coordinates or GeoJSON on a `Leaflet.js <http://leafletjs.com/>`_ -based map:

.. figure:: ../../images/map-preview.png

Graph View
*************

If enabled, visitors can chose one column of your data as an X-axis, one or more as Y-axis data, and preview your data as a bar, point or line graph.

.. figure:: ../../images/graph-preview.png


File size limits
****************

Files can only be previewed if they are well formatted or small enough to render in the browser.

If files are too large to preview within 1 second you will get the following message "File was too large or unavailable for preview."

Files that are too large to preview in the browser can be previewed by `adding them to the datastore <../datastore/index.html>`_. Once a file is in the datastore the preview is only asking for the first 25 rows of the data. Thus large datasets can be previewed.

Additional preview support
--------------------------

Support has been added for previewing JSON, geojson, XML, ArcGIS REST, WMS, images, PDF, and ZIP* files.
*DKAN will display a list of contents for ZIP files uploaded as resources on datasets. (Unlike recline visual previews, the zip file preview does not work with remote linked files.)

.. figure:: ../../images/zip-preview.png

.. figure:: ../../images/xml-preview.png

.. figure:: ../../images/pdf-preview.png

.. figure:: ../../images/json-preview.png

.. figure:: ../../images/geojson-preview.png

.. figure:: ../../images/png-preview.png

Preview Configuration
---------------------
By default previews are available for resources with files below 3MB of size. However you can customize this limit in the recline configuration page (/admin/dkan/recline).

.. figure:: ../../images/recline-configuration.png

External Previews
---------------------

Starting with version 7.x-1.10, DKAN supports previewing/opening resources in external services that offer simple URL-based integrations. For instance, the CartoDB mapping service offers an `Open in CartoDB service <https://cartodb.com/open-in-cartodb>`_. Enabling this for CSV files will result in a dataset display like this:

.. figure:: ../../images/external-preview.png

External preview functionality can be enabled and configured in the "DKAN Dataset Previews" administration page (/admin/dkan/dataset_preview).