# Advanced Metadata Features

A Dataset is a container for storing files, APIs, or other resources  as well as the [metadata](http://en.wikipedia.org/wiki/Metadata) about those resources.  The metadata in a DKAN Dataset is structured specifically for describing Open Data.

The metadata in a DKAN Dataset module is culled from the DCAT standard as well as Project Open Data. The full list of default Dataset fields is [available in the developer section](/dkan-documentation/dkan-developers/dataset-technical-field-reference).

The Dataset form allows users to create Datasets and add appropriate metadata:
![dataset form](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-05-31%20at%204.55.34%20PM.png)

The DKAN Dataset API exposes Dataset metadata for individual datasets as well an entire catalog.
![api screenshot](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-10%20at%204.03.27%20PM.png)

## Custom metadata

It is easy to add new fields to DKAN which will show up on the Dataset form, make available as search facets, and be available to output in one of the Dataset APIs.

If there is information that only pertains to one or more datasets then it is possible to use the "Additional Info" field. This allows content editors to add unique field / value entries that exist only on a single dataset:

![additional info field](https://cloud.githubusercontent.com/assets/512243/4188796/57b53a52-3776-11e4-97f6-61e18e3cd90d.png)

Globally-available custom fields can also be added through [Drupal's Fields UI](https://www.drupal.org/documentation/modules/field-ui) and added to public APIs using the [Open Data Schema Mapper](http://docs.getdkan.com/dkan-documentation/dkan-developers/adding-or-update-fields-api-output).

## Data Extent

The "Data Extent" block is a visual representation of the "Spatial / Geographical Coverage Area".

![Data Extent](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-04-30%20at%2010.30.22%20AM.png)

The "Spatial / Geographical Coverage Area" field is a geojson representation of the area a Dataset covers. This can be a point, box, or other representation.<br><br>DKAN provides a widget so that a spatial area can be drawn if desired:

![Spatial field](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-04-24%20at%2010.46.43%20AM.png)

## Revision History

DKAN Datasets and Resources track revisions in order to log and display changes, using Drupal's built-in revision system.

![viewing revisions](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-04-14%20at%202.30.29%20PM.png)

A list of recent revisions are available through the revision_list API at "/api/3/action/revision_list"

Diffs of individual files are not available by default but could be added using [this or another similar strategy](https://drupal.org/node/2101377).
