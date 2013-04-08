                  __ _      _     _ 
                 / _(_)    | |   | |
 __ _  ___  ___ | |_ _  ___| | __| |
/ _` |/ _ \/ _ \|  _| |/ _ \ |/ _` |
| (_| | __/ (_) | | | |  __/ | (_| |
\__, |\___|\___/|_| |_|\___|_|\__,_|
 __/ |                              
|___/                               

Geofield is a module for storing geographic data in Drupal 7.
It supports all geo-types (points, lines, polygons, multitypes etc.)

http://drupal.org/project/geofield


---
INSTALLATION

Following are instructions for manual installation of Geofield and its
required pieces. Want to do it the easy way? Install drush
(http://drupal.org/project/drush) and drush-make
(http://drupal.org/project/drush_make), then issue the command:

  drush make geofield.make

Otherwise:

1. Install the Libraries module (http://drupal.org/project/libraries)

2. If it's not already there, create the directory /sites/all/libraries 
   (or /sites/SITENAME/libraries for a multisite installation)

3. Download the geoPHP library from
   https://github.com/downloads/phayes/geoPHP/geoPHP.tar.gz
   (For more information, see the full project page at
   https://github.com/phayes/geoPHP)

4. Unarchive the library files and place in the "libraries" directory
   mentioned in Step 2

Your directory structure should now look something like:
/sites/all/libraries/geoPHP/geoPHP.inc

---
CONFIGURATION

To be written. Maybe by you?

---
DEPENDENCIES

libraries
  provides API for handling libraries
  http://drupal.org/project/libraries

geoPHP
  provides geometry transformations
  https://github.com/phayes/geoPHP


---
RELATED MODULES

openlayers
  provides mapping for geofield
  http://drupal.org/project/openlayers
  
geocoder
  provides geocoding widget for geofield
  https://drupal.org/project/geocoder


---
CREDITS

Original author:  Tristan O'Neil
Contributors:     Alex Barth, Jeff Miccolis, Young Hahn, Tom MacWright, 
                  Patrick Hayes, Dave Tarc, Nikhil Trivedi, Marek Sotak, 
                  Khalid Jebbari, Brandon Morrison, David Peterson


---
API NOTES

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

