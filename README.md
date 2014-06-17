[![DKAN Sitewide Build Status](https://travis-ci.org/NuCivic/dkan.svg?branch=7.x-1.x)](https://travis-ci.org/NuCivic/dkan)


[![DKAN](http://f.cl.ly/items/3q3v120q0h1q2d2A3s3L/Screenshot%202014-04-29%2018.40.15.png)](http://nucivic.com/dkan)

## What is DKAN?

[DKAN](http://nucivic.com/dkan) is a Drupal-based open data tool with a full suite of cataloging, publishing and visualization features that allows governments, nonprofits and universities to easily publish data to the public. DKAN is maintained by [NuCivic](http://nucivic.com).

## Data publishers

*   Publish data through a guided process or import via API/harvesting from other catalogs
*   Customize your own metadata fields, themes and branding*   Store data within DKAN or on external (e.g. departmental) sites*   Manage access control, version history with rollback, RDF support, user analytics*   Supported enterprise-quality commercial support and FISMA-certified cloud hosting options available

## Data users

*   Explore, search, add, describe, tag, group datasets via web front-end or API
*   Collaborate with user profiles, groups, dashboard, social network integration, comments*   Use metadata and data APIs, data previews and visualizations*   Manage access control, version history with rollback, INSPIRE/RDF support, user analytics*   Extend and leverage the full universe of more than 18,000 freely available Drupal modules

## Hosting and support

NuCivic' [Data](http://nucivic.com/products/nudata/) platform offers 24/7, secure, cloud-based DKAN hosting and support services.

*   [FAQs »](http://nucivic.com/products/nudata/nudata-faqs/)
*   [ Live demo » ](http://demo.getdkan.com/)
*   [ Docs » ](http://docs.getdkan.com/)

## Build Status

| Component      | Status      |
|----------------|:------------|
| DKAN Sitewide  | [![DKAN Sitewide Build Status](https://travis-ci.org/NuCivic/dkan.svg?branch=7.x-1.x)](https://travis-ci.org/NuCivic/dkan) |
| DKAN Dataset   | [![DKAN Dataset Build Status](https://travis-ci.org/NuCivic/dkan_dataset.svg?branch=7.x-1.x)](https://travis-ci.org/NuCivic/dkan_dataset) |
| DKAN Datastore | [![DKAN Datastore Build Status](https://travis-ci.org/NuCivic/dkan_datastore.svg?branch=7.x-1.x)](https://travis-ci.org/NuCivic/dkan_datastore) |

## Installation

### Downloadable Version 7.x-1-0

https://github.com/NuCivic/dkan-drops-7

### Drush Make

Requires drush version 6.x or 7.x.

Create a full version with drush make:

```
git clone --branch 7.x-1.x https://github.com/NuCivic/dkan.git
cd dkan
drush make --prepare-install build-dkan.make webroot
cd webroot
drush site-install dkan --db-url="mysql://DBUSER:DBPASS@localhost/DBNAME"
```

Note: Recline previews require clean URLs

## Components

DKAN consists of three main components:

### DKAN Distro

This is the installation profile that packages everything together. It included the DKAN theme, faceted search, and other elements.

### DKAN Dataset

DKAN Dataset is a stand-alone module: https://github.com/NuCivic/dkan_dataset that provides dataset and resource content types. This is the foundation for the open data publishing. **DKAN Dataset can be included in any Drupal 7 site**.

The dataset nodes contain the metadata and the resource nodes contain the file or data itself. This is exactly how CKAN does it, but in Drupal.  The metadata from the datasets are available in rdf(a) that is DCAT compliant as well as in JSON.

### DKAN Datastore

DKAN Datastore is a stand-alone module: https://github.com/NuCivic/dkan_datastore that provides the ability to include uploaded files into a datastore and expose their components via an API. **DKAN Datastore can be included in any Drupal 7 site**.

## Current Status

DKAN just published its 1.0 release.

Contact us if you want to get involved!

DKAN development is a sponsored by NuCivic. For more information about hosting and professional support options for DKAN, see http://nucivic.com

## Community

You are welcome to join the discussion on the DKAN Developers mailing list. Join and read archives at:
https://groups.google.com/forum/?hl=en#!forum/dkan-dev

## Contributing

Please file all tickets for DKAN, including those that involve code in DKAN Dataset and DKAN Datastore modules in this issue queue. We have several labels in place for you to tag the issue with and identify it with the proper component.

Also, please remember to reference the issue accross repositories in order for maintainers to pick up commits and pull requests looking at the issue. You can do that for commits like this:

```bash
git commit -m "Issue NuCivic/dkan#<issue_number>: ..."
```

Just replace **<issue_number>** with the actual issue number. You can reference pull requests exactly like that if you add the same text **"NuCivic/dkan#<issue_number>"** in a comment. 

This really help us detecting changes and pulling them in in faster.
