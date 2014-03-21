

Data module
===========

The Data module provides

* An API for dynamically allocating tables for single-row records
* An API for insert/update/delete operations
* Automatic views integration
* Together with CTools module: exportable configurations
* Together with schema module: schema inspection and fixing

Its companion Data UI provides

* UI to add new database tables
* UI to add or alter columns to existing tables managed by Data module
* Default views for tables managed by Data module

Use Data Search module if you would like to search one or more columns of your
data tables:

* Install Data Search
* Go to admin/content/data
* Edit table to be indexed
* Click on "Configure search" tab
* Check table columns to be indexed

Use Data Node module if you would like to relate nodes to data records:

* Install Data Node
* Go to admin/content/data
* Edit table to relate to nodes
* Click on "Relate to nodes" tab
* Pick a content type
* Pick which id in table will be related to a node id
* Optionally:
  * Use views field handler for adding/removing a data table record to
    a node.
  * Configure Data Node block to show up in sidebar for selecting an
    active node to add a data table record to.

Working with HTML Fields

If you are working with a feed that has one or more fields that contain markup
you can specify that those individual fields to be output with the HTML as
opposed to escaped when using views.

1. Navigate to admin/build/data
2. Select 'Edit' for the table that you are working with
3. Click on the 'Configure views' tab
4. Identify the field that contains HTML. For the 'Field handler' column, select
   the 'views_handler_field_data_markup'

Working with Views and date fields

A field on your table can be declared as a date field to Date Views module if
it contains data in one of the following formats:
- Unix timestamp
- MySQL DATETIME format
- ISO date format (as supported by Date module)
Once a Data table field has had its date settings configured, it will be
available in the combined date filter and argument handlers.

For the field handler, use the views_handler_field_date handler. Fields whose
formats is other than unix timestamp will need the patch to Views at
http://drupal.org/node/2178287.

Recommendations
===============

Check out Feeds for importing content into Data tables. A working example
thereof can be found in Managing News.

http://drupal.org/project/feeds
http://managingnews.com
