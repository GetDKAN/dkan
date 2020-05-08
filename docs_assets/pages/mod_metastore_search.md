@page search Metastore Search

The **Metastore Search** module provides some integration with [search_api](https://www.drupal.org/project/search_api) to facilitate querying DKAN's metadata.

Search API manages search servers and indexes. For more information on how Search API works visit their [documentation](https://www.drupal.org/docs/8/modules/search-api).

This module provides a Data Source for Search API that allows it to index DKAN's metadata. In particular datasets. It also provides a default server and index configurations.

Querying the search server is possible at ```/api/1/search```

Different search queries can be executed against the index through query parameters.

These are the available query parameters:

| Parameter | Default value | Example | Description |
| :------------- | :----------: | :----------: | :---------- |
|fulltext| N/A |``?fulltext=hello``| Performs a search for the given phrase/word against all fields indexed as Fulltext|
|field| N/A |``?keyword=hospital,blah``| Filters the search by the given field (keyword in the example), and the specific list of value given. The filter performs a logical AND so content with the keywords 'hospital' and 'blah' will be displayed|
|page| 1 |``?page=2``| The page to be displayed. How many items are shown in a page is determined by the page-size parameter|
|page-size| 10 |``?page-size=50``| The number of items to be returned in each page|
|sort|search_api_relevance|``?sort=keyword``| The field to sort by |
|sort-order|asc| ``?sort-order=desc``| The order of the sort applied|

## Search Facets

1. Navigate to `admin/config/search/search-api/index/dkan/fields` to define which fields of your metadata to search on.
2. Rebuild tracking information with `drush dkan:metastore-search:rebuild-tracker`
3. Re-index the site `drush sapi-i`

Viewing the ``/api/1/search`` endpoint, you should see a "facets" section with the configured facet fields and their results.
