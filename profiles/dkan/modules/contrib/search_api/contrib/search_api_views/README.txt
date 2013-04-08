Search API Views integration
----------------------------

This module integrates the Search API with the popular Views module [1],
allowing users to create views with filters, arguments, sorts and fields based
on any search index.

[1] http://drupal.org/project/views

"More like this" feature
------------------------
This module defines the "More like this" feature (feature key: "search_api_mlt")
that search service classes can implement. With a server supporting this, you
can use the „More like this“ contextual filter to display a list of items
related to a given item (usually, nodes similar to the node currently viewed).

For developers:
A service class that wants to support this feature has to check for a
"search_api_mlt" option in the search() method. When present, it will be an
array containing two keys:
- id: The entity ID of the item to which related items should be searched.
- fields: An array of indexed fields to use for testing the similarity of items.
When these are present, the normal keywords should be ignored and the related
items be returned as results instead. Sorting, filtering and range restriction
should all work normally.

"Facets block" display
----------------------
Most features should be clear to users of Views. However, the module also
provides a new display type, "Facets block", that might need some explanation.
This display type is only available, if the „Search facets“ module is also
enabled.

The basic use of the block is to provide a list of links to the most popular
filter terms (i.e., the ones with the most results) for a certain category. For
example, you could provide a block listing the most popular authors, or taxonomy
terms, linking to searches for those, to provide some kind of landing page.

Please note that, due to limitations in Views, this display mode is shown for
views of all base tables, even though it only works for views based on Search
API indexes. For views of other base tables, this will just print an error
message.
The display will also always ignore the view's "Style" setting, selected fields
and sorts, etc.

To use the display, specify the base path of the search you want to link to
(this enables you to also link to searches that aren't based on Views) and the
facet field to use (any indexed field can be used here, there needn't be a facet
defined for it). You'll then have the block available in the blocks
administration and can enable and move it at leisure.
Note, however, that the facet in question has to be enabled for the search page
linked to for the filter to have an effect.

Since the block will trigger a search on pages where it is set to appear, you
can also enable additional „normal“ facet blocks for that search, via the
„Facets“ tab for the index. They will automatically also point to the same
search that you specified for the display. The Search ID of the „Facets blocks“
display can easily be recognized by the "-facet_block" suffix.
If you want to use only the normal facets and not display anything at all in
the Views block, just activate the display's „Hide block“ option.

Note: If you want to display the block not only on a few pages, you should in
any case take care that it isn't displayed on the search page, since that might
confuse users.

FAQ: Why „*Indexed* Node“?
--------------------------
The group name used for the search result itself (in fields, filters, etc.) is
prefixed with „Indexed“ in order to be distinguishable from fields on referenced
nodes (or other entities). The data displayed normally still comes from the
entity, not from the search index.
