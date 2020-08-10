__DKAN__ is an open-source open-data platform inspired by [CKAN](https://ckan.org/) (Comprehensive Knowledge Archive Network) and built on top of the very popular [Drupal](https://drupal.org) CMS (Content Management System).

DKAN is actively maintained by [CivicActions](https://civicactions.com).

---

## Getting Started

If you are already convinced that DKAN is the right tool for your open-data needs, refer to the \ref installation page to start working with DKAN.

---

## Structure

%Drupal's mechanism for extension is called __modules__. DKAN is a module that extends %Drupal's functionality.

%Drupal modules can have submodules; DKAN utilizes this structure to organize its internal subsystems. Information about the most important modules/submoudles in DKAN can be found in the \ref mods page.

DKAN's modules and subsystems are organized around 3 main data functions:
1. Management.
2. Aggregation.
3. Accessability.

### Management
The main function of any open-data platform is to help manage data. Making data **public** is simple, anyone can place a file in a web accessible store these days, but making data **open** takes a bit more work.
This is what we mean by data management: Provinding tools that empower data-providers to make data **open** (accessible, discoverable, compliant, etc), to empower data-consumers to find and use the data they need: *data accessability*.

Most management functions in DKAN are provided by the \ref meta module.

### Aggregation
Many open-data catalogs are aggregations of other sources of data. DKAN provides tools to allow any DKAN catalog to host aggregated data in conjuction with originally sourced data.
Aggregating data is known in the CKAN ecosystem as **harvesting**. We liked the agriculatural analogy, kept the term harvesting, and placed our aggregation tool in the \ref harvest module.

### Accessability
Finally, data is only good to the degree to which it can be found and understood. This is why many of the modules in DKAN are dedicated to helping make data more accessible.

The \ref meta helps data-providers inject context information (*metadata*) about the data. The \ref search module provides a configurable way to allow that context/metadata to be used by data-consumers and find what they need.

The searchable metadata provided by the \ref search module will help users narrow down their search, but ultimately the user will have to look at the data.

Data in files isn't naturally searchable, but the \ref datastore module parses and stores data in a more explorable format. DKAN can then use the datastore to provide more tools that allow the user to explore the data. Tools like our \link Drupal::datastore::SqlEndpoint::Service SQL Endpoint \endlink
