# Search

DKAN offers faceted search similar to CKAN. This functionality comes from the Search API module in Drupal: http://drupal.org/project/search_api Out of the box DKAN uses the native database: http://drupal.org/project/search_api_db DKAN can easily be updated to use Solr to power the search using the Search API Solr module: http://drupal.org/project/search_api_solr For a great tutorial on setting up Search API Solr, see: http://zugec.com/2011/04/how-setup-search-api-apache-solr

### Switching to Solr

To switch from the native database to Solr simply:

*   Create or purchase a Solr instance
*   Install search_api_solr
*   Create a Solr server: ![add server](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-09-24%20at%2011.00.54%20AM.png)
*   Switch the Dataset index to the Solr server: ![switch index](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-09-24%20at%2011.03.05%20AM.png)

### "Did You Mean?" Spellchecking

To add spellcheck, simply install the Search API Spellcheck: https://www.drupal.org/project/search_api_spellcheck

### Searching within Resource files

PDFs, CSVs and other files attached to Resources can be searched by using the Tika library. This functionality is made possible with the Search API Attachments module: http://drupal.org/project/search_api_attachments

### Search API Handbook

See the Search API Handbook for more recipes and information: https://www.drupal.org/node/1250878