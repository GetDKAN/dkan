                  __ _      _     _
                 / _(_)    | |   | |
 __ _  ___  ___ | |_ _  ___| | __| |
/ _` |/ _ \/ _ \|  _| |/ _ \ |/ _` |
| (_| | __/ (_) | | | |  __/ | (_| |
\__, |\___|\___/|_| |_|\___|_|\__,_|
 __/ |
|___/

CONTENTS OF THIS FILE
---------------------

 * About Geofield
 * Install
 * Configure
 * Credits
 * API notes

ABOUT GEOFIELD
--------------
Geofield (http://drupal.org/project/geofield) is a Drupal 7 module that
provides a field types for storing geographic data. This data can be attached
to any entity, e.g., nodes, users and taxonomy terms. Geofield provides
different widgets for data input and formatters for data output. The Geofield
module can can store data as Latitude and Longitude, Bounding Box and Well
Known Text (WKT) and it supports all types of geographical data: points,
lines, polygons, multitypes et cetera.

Great documentation on Geofield can be found at http://drupal.org/node/1089574

INSTALL
-------

Install the modules Geofield and geoPHP in the usual way. General information
on installing Drupal modules can be found here: http://drupal.
org/documentation/install/modules-themes/modules-7

Optionally install Open Layers 2: http://drupal.org/project/openlayers

CONFIGURE
---------

To add a geofield to a content type go to /admin/structure/types/ and choose
"Manage fields" for the chosen content type. Add a new field of the field type
"Geofield", and choose the preferred widget, e.g., "OpenLayers Map". Configure
the field according ton the chosen options.

Geofield comes with the basic but easy-to-use submodule Geofield Map that
allows you to display geographical data in a Google map. Enable Geofield Map
at /admin/modules. Read more about Geofield Map at
http://drupal.org/node/1466490

For more advanced and flexible data display you need to configure or create a
map in OpenLayers at /admin/structure/openlayers/maps. You can easily create
your own map by cloning an existing one. An introduction to OpenLayers can be
found here: http://drupal.org/node/1481374.

When you have configured a map in OpenLayers you must define to use the map.
Go to  /admin/structure/types and choose "Manage display".

Note: you can also add a geofield to a user, a taxonomy term or a comment.

CREDITS
-------
Original author:  Tristan O'Neil
Contributors:     Alex Barth, Jeff Miccolis, Young Hahn, Tom MacWright,
                  Patrick Hayes, Dave Tarc, Nikhil Trivedi, Marek Sotak,
                  Khalid Jebbari, Brandon Morrison, David Peterson

API NOTES
---------
Geofield fields contain nine columns of information about the geographic data
that is stores. At its heart is the 'wkt' column where it stores the full
geometry in the 'Well Known Text' (WKT) format. All other columns are metadata
derived from the WKT column. Columns are as follows:

  'wkt'          WKT
  'geo_type'     Type of geometry (point, linestring, polygon etc.)
  'lat'          Centroid (Latitude or Y)
  'lon'          Centroid (Longitude or X)
  'top'          Bounding Box Top (Latitude or Max Y)
  'bottom'       Bounding Box Bottom (Latitude or Min Y)
  'left'         Bounding Box Left (Longitude or Min X)
  'right'        Bounding Box Right (Longitude or Max X)

When a geofield is saved using the provided widgets, these values are passed
through the geofield_compute_values function in order to compute dependent
values. By default dependent values are computed based on WKT, but this may be
overriden to compute values based on other columns. For example,
geofield_compute_values may be called like so:

  geofield_compute_values($values, 'latlon');

This will compute the wkt field (and all other fields) based on the lat/lon
columns, resulting in a point. As a developer this is important to remember if
you modify geofield information using node_load and node_save. Make sure to
run any modified geofield instances through geofield_compute_values in order
to make all columns consistent.
