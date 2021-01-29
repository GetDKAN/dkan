__DKAN__ is an open-source open-data platform inspired by [CKAN](https://ckan.org/) (Comprehensive Knowledge Archive Network) and built on top of the very popular [Drupal](https://drupal.org) CMS (Content Management System).

---

## Structure

%Drupal allows _modules_ to extend its functionality. DKAN is a %Drupal module.

Modules can have submodules; DKAN utilizes this structure to organize its internal subsystems. Information about the subsystems/components in DKAN can be found in the \ref components page.

DKAN's modules and subsystems are organized around four main data functions:

1. Management
2. Aggregation
3. Discoverability
4. Usability

### Data Management

The main function of any open data platform is to help manage data. Making data _public_ is simple, anyone can place a file in a web-accessible store these days, but making data _open_ takes a bit more work. True open data is accessible, discoverable, machine-readable, linked to other resources that provide context, published in an open format and under an open license. 

This is what we mean by data management: providing tools that empower data publishers to make data open, which empowers data consumers to find and use the data they need.

@note
    For more on the fundamentals of open data, read [the Open Definition](https://opendefinition.org/od/2.1/en/) and [5-Star Open Data](https://5stardata.info/).

Most data management functions in DKAN are provided by the \ref metastore module.

### Data Aggregation

Many open data catalogs are aggregations of other sources of data. DKAN provides tools to allow any DKAN catalog to host aggregated or federated datasets in conjunction with originally-sourced data. A very large real-world example of this is [Data.gov](https://www.data.gov/), a catalog which aggregates datasets the U.S. federal government.

Aggregating or importing datasets from different remote sources into a catalog is often known as _harvesting_. DKAN has robust and extensible functionality for this that lives in the \ref harvest module.

### Discoverability

Finally, data is only useful and open to the degree to which it can be found and understood. This is why many of the modules in DKAN are dedicated to helping make data more accessible.

The \ref metastore helps data publisher give context (\ref Metadata) to their data. The \ref search module provides a configurable way to allow data consumers to use metadata  and find what they need.

The searchable metadata provided by the \link metastore_search.info.yml metastore_search \endlink module will help users narrow down their search, but ultimately the user will have to look at the data itself.

### Usability
Data in files isn't naturally searchable, but the \ref datastore module parses and stores data in a more explorable format. DKAN can then use the datastore to provide direct access to the data, through tools like the \link Drupal::datastore::SqlEndpoint::Service SQL Endpoint \endlink

---

DKAN is actively maintained by [CivicActions](https://civicactions.com/dkan).

To learn more about the DKAN community visit [GetDKAN.org](https://getdkan.org).
