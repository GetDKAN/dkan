[![DKAN](https://drupal.org/files/dkanscreenshot.png)](http://nucivic.com/dkan)

## What is DKAN?

[DKAN](http://nucivic.com/dkan) is a Drupal-based open data platform with a full suite of cataloging, publishing and visualization features that allows governments, nonprofits and universities to easily publish data to the public. DKAN is maintained by [Nuams](http://nucivic.com/dkan).

## Data publishers

*   Publish data through a guided process or import via API/harvesting from other catalogs
*   Customize your own metadata fields, themes and branding*   Store data within DKAN or on external (e.g. departmental) sites*   Manage access control, version history with rollback, INSPIRE/RDF support, user analytics*   Supported enterprise-quality commercial support and FISMA-certified cloud hosting options available

## Data users

*   Explore, search, add, describe, tag, group datasets via web front-end or API
*   Collaborate with user profiles, groups, dashboard, social network integration, comments*   Use metadata and data APIs, data previews and visualizations*   Manage access control, version history with rollback, INSPIRE/RDF support, user analytics*   Extend and leverage the full universe of more than 18,000 freely available Drupal modules

## Hosting and support

Nuams' [NuData](http://nucivic.com/products/nudata/) platform offers 24/7, secure, cloud-based DKAN hosting and support services.

*   [FAQs »](http://nucivic.com/products/nudata/nudata-faqs/)
*   [ Live demo » ](http://demo.getdkan.com/)

## Installation

### Downloadable Version 7.x-1-beta1

https://github.com/nuams/dkan

The "fully made" version of DKAN is not available on drupal.org because the Recline library which includes Apache 2 version code cannot be added to drupal.org. 

### Drush Make

Create a full version with drush make:

#### 7.x-1.x

<caption>
drush make http://drupalcode.org/project/dkan.git/blob_plain/refs/heads/7.x-1.x:/dkan_distro.make 
</caption>

### Git

#### 7.x-1.x

`git clone --branch 7.x-1.x http://git.drupal.org/project/dkan.git `

## Components

DKAN consists of three main components:

### DKAN Distro

This is the installation profile that packages everything together. It included the DKAN theme, faceted search, and other elements.

### DKAN Dataset

DKAN Dataset is a stand-alone module: http://drupal.org/project/dkan_dataset that provides dataset and resource content types. This is the foundation for the open data publishing. **DKAN Dataset can be included in any Drupal 7 site**.

The dataset nodes contain the metadata and the resource nodes contain the file or data itself. This is exactly how CKAN does it, but in Drupal.  The metadata from the datasets are available in rdf(a) that is DCAT compliant as well as in JSON.

### DKAN Datastore

DKAN Datastore is a stand-alone module: http://drupal.org/project/dkan_datastore that provides the ability to include uploaded files into a datastore and expose their components via an API. **DKAN Dataset can be included in any Drupal 7 site**.

## Current Status

### Beta 1

DKAN is currently on version 7.x-1.0-beta1.

### Full Release Blockers

We hope to have a full release ready this winter. Here are the list of release-blockers:

**DKAN Distro Bugs and Features:**
[#2033147]
[#2042585]
[#2042637]
[#2043309]
[#2042653]
[#2042651]
[#2061031]
[#2042581]
[#2063905]
[#2063913]
[#1810098]

**Testing**
DKAN Dataset Tests
[#2042639]

DKAN Datastore Tests
[#2143819]
[#2143817]

DKAN Sitewide Tests
[#2143821]

**CKAN API Read Compliance**
[#2125111]
[#2125125]
[#2125145]
[#2143831]
[#2143841]
[#2143867]
[#2143871]
[#2143883]

**DKAN to CKAN Harvesting Example**
[#2125147]

**CKAN or DKAN to DKAN Harvesting (Migrate)**
[#2064473]

### Post Full release features:

[#2064477]
[#2064475]

Contact us if you want to get involved!

DKAN development is a sponsored by NUAMS. For more information about hosting and professional support options for DKAN, see http://nuams.com/products

## Contributing

Please file all tickets for DKAN, including those that involve code in DKAN Dataset and DKAN Datastore modules in this issue queue.
