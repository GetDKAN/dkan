[![DKAN Sitewide Build Status](https://circleci.com/gh/NuCivic/dkan.svg?style=svg)](https://circleci.com/gh/NuCivic/dkan)

[![Join the chat at https://gitter.im/NuCivic/dkan](https://badges.gitter.im/NuCivic/dkan.svg)](https://gitter.im/NuCivic/dkan?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## What is DKAN?

[![DKAN](https://www.drupal.org/files/2016-02-05_12-09-49.png)](http://nucivic.com/dkan)

[DKAN](http://nucivic.com/dkan) is a Drupal-based open data tool with a full suite of cataloging, publishing and visualization features that allows governments, nonprofits and universities to easily publish data to the public. DKAN is maintained by [NuCivic](http://nucivic.com).

## Data publishers

*   Publish data through a guided process or import via API/harvesting from other catalogs
*   Customize your own metadata fields, themes and branding
*   Store data within DKAN or on external (e.g. departmental) sites
*   Manage access control, version history with rollback, RDF support, user analytics
*   Supported enterprise-quality commercial support and FISMA-certified cloud hosting options available

## Data users

*   Explore, search, add, describe, tag, group datasets via web front-end or API
*   Collaborate with user profiles, groups, dashboard, social network integration, comments
*   Use metadata and data APIs, data previews and visualizations
*   Manage access control, version history with rollback, INSPIRE/RDF support, user analytics
*   Extend and leverage the full universe of more than 18,000 freely available Drupal modules

## Hosting and support

NuCivic' [Data](http://nucivic.com/data/) platform offers 24/7, secure, cloud-based DKAN hosting and support services.

*   [ Live demo » ](http://demo.getdkan.com/)
*   [ Docs » ](http://docs.getdkan.com/)

## Installation

Please note that we are in the process of revamping our installation and upgrade guide. The instructions here will work, but please bear with us as we develop better documentation and processes. 

Before getting started, it's recommended that you familiarize yourself with:
 
* [Drush, the command line tool]()
* [Drupal's installation process]()
* [Drupal's upgrade process]()
* [Drupal profiles and distributions]()

What you will find in this folder is a Drupal _installation profile_. To set up a working website using DKAN, you will need to acquire or build a full DKAN distribution of Drupal.  

### "Fully Made" version:

https://github.com/NuCivic/dkan-drops-7

At the moment, our supported fully-made DKAN codebase is the [DKAN DROPS-7](https://github.com/NuCivic/dkan-drops-7) repository, which is optimized to run on the Pantheon platform. You can build a DKAN site with a single click on Pantheon [here](https://dashboard.getpantheon.com/products/dkan/spinup). (We also offer [one-click installation on Acquia](http://docs.getdkan.com/dkan-documentation/get-dkan/dkan-acquia))

### Drush Make

This "builds" a full DKAN website codebase from the bleeding-edge development version of DKAN, by downloading Drupal and all the additional modules that DKAN needs to run. This method is particularly useful for people who want to work on the DKAN project itself, as it preserves Git versioning information in every profile, theme and module directory. The core developers use this method when developing and testing DKAN.  

Note that `rsync` is used to copy the DKAN profile inside the Drupal `/profiles` folder. You may wish to modify this process to fit your own development practices.

Requires drush version 8.x.

```bash
git clone --branch 7.x-1.x https://github.com/NuCivic/dkan.git
cd dkan
drush make --prepare-install drupal-org-core.make webroot --yes
rsync -av . webroot/profiles/dkan --exclude webroot
drush -y make --no-core --working-copy --contrib-destination=./ drupal-org.make webroot/profiles/dkan --no-recursion --concurrency=3 
cd webroot 
drush site-install dkan --db-url=mysql://DBUSER:DBPASS@localhost/DBNAME --verbose --yes --account-pass=admin
```

Note: Recline previews require [clean URLs](https://www.drupal.org/getting-started/clean-urls#enabling-7)

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

Check tags for latest DKAN Version. 7.x-1.x is the development branch.

Contact us if you want to get involved!

DKAN development is a sponsored by NuCivic. For more information about hosting and professional support options for DKAN, see http://nucivic.com/data

### Releases and Release Candidates

Currently, we plan releases wrapping github issues on milestones. For instance, if the latest release for dkan is ```7.x-1.n``` then a ```DKAN 7.x-1.n+1``` milestone should exists. You are welcome to take a look and propose bugs fixes or new features for the next release.

However, there are times when we need to create a release candidate for the next release. This usually happens when security updates are needed for contrib modules but other criteria may arise.

We keep DKAN, DKAN Dataset and DKAN Datastore versioning in sync.

## Getting Help with DKAN

Have a question, found a bug, or need help with DKAN?

### I have a general question DKAN as a Developer or Site Builder

Please post a question on our Developer list: https://groups.google.com/forum/?hl=en#!forum/dkan-dev

### I have a bug or issue

Please post it to our Github issue queue: https://github.com/nucivic/dkan/issues

### I would like to purchase DKAN support or hosting

Please contact us at NuCivic http://nucivic.com/contact

## Community

You are welcome to join the discussion on the DKAN Developers mailing list. Join and read archives at:
https://groups.google.com/forum/?hl=en#!forum/dkan-dev

## Contributing

Please file all tickets for DKAN, including those that involve code in DKAN Dataset and DKAN Datastore modules in this issue queue. We have several labels in place for you to tag the issue with and identify it with the proper component.

Please follow the [Ticket Template](https://github.com/NuCivic/dkan/blob/7.x-1.x/CONTRIBUTING.md#new-feature-template) when creating new tickets.

Also, please remember to reference the issue across repositories in order for maintainers to pick up commits and pull requests looking at the issue. You can do that for commits like this:

```bash
git commit -m "Issue NuCivic/dkan#<issue_number>: ..."
```

Just replace **<issue_number>** with the actual issue number. You can reference pull requests exactly like that if you add the same text **"NuCivic/dkan#&lt;issue_number&gt;"** in a comment. 

This really help us detecting changes and pulling them in in faster.
