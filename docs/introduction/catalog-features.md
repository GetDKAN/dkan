# Catalog Features

## Project Open Data Compliance

DKAN provides a "data.json" index to satisfy the US federal government's [Project Open Data](http://project-open-data.github.io/) requirements. More information about the "slash data" or "data.json" requirements can be found in POD's [Open Data Catalog Requirements](http://project-open-data.github.io/catalog) and [Common Core Metadata Schema](http://project-open-data.github.io/schema) pages. 

The exact mapping of data (specifically, Drupal data [tokens](https://www.drupal.org/project/token)) from your DKAN site to the data.json index can be customized using the [Open Data Schema Mapper](/dkan-developers/adding-or-update-fields-api-output). 

## DCAT-Compliant Markup
Project Open Data's [schema](http://project-open-data.github.io/schema) is based on the [DCAT open data vocabulary](http://www.w3.org/TR/vocab-dcat/). DKAN also provides RDF endpoints and RDFa markup for all Datasets following the [DCAT specification](http://www.w3.org/TR/vocab-dcat/).

## Public Catalog Listing API, Based on CKAN

For exposing more and better-structured machine-readable metadata than Project Open Data's data.json allows for, DKAN also ships with a public API based heavily on [CKAN's](http://docs.ckan.org/en/latest/api/index.html). This includes APIs for viewing the contents of an entire catalog, as well as requesting the metadata for a single [dataset](/dkan-documentation/dkan-features/dataset-features).