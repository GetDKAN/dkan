# DKAN Open Data Platform

DKAN is an open source open data platform with a full suite of cataloging, publishing and visualization features that allow organizations to easily publish data to the public.

*  [ Official website ](https://getdkan.org)
*  [ Demo ](http://demo.getdkan.com/)
*  [ Documetation ](https://docs.getdkan.com/)

## Community

Join the [DKAN Slack community](https://dkansignup.herokuapp.com/).

## Connect

* [ Subscribe ](http://eepurl.com/c01YS1)
* [ Slack ](https://dkan.slack.com/)
* [ Blog ](https://medium.com/dkan-blog)
* [ Twitter ](https://twitter.com/getdkan)
* [ GitHub ](https://github.com/getdkan)
* [ YouTube ](https://www.youtube.com/channel/UCl7qFUCkyh32lss4EjQEUXg)
* [ Drupal ](https://www.drupal.org/project/dkan)
* [ Eventbrite ](https://www.eventbrite.com/o/dkan-14793986036)
* [ RSS ](https://medium.com/feed/dkan-blog)

## Help

* General: [DKAN documentation](https://docs.getdkan.com)
* Developers: Submit a [Github issue](https://github.com/GetDKAN/dkan/issues) or post to [ #dev ](https://dkan.slack.com/messages/C4BEVFDKJ/) channel in [ DKAN Slack ](https://dkan.slack.com)
* Bugs: Submit a [Github issue](https://github.com/GetDKAN/dkan/issues)

## Features

### For data publishers

*   Publish data through a guided process or import via API/harvesting from other catalogs
*   Customize your own metadata fields, themes and branding
*   Store data within DKAN or on external (e.g. departmental) sites
*   Manage access control, version history with rollback, RDF support, user analytics

### For data users

*   Explore, search, add, describe, tag, group datasets via web front-end or API
*   Collaborate with user profiles, groups, dashboard, social network integration, comments
*   Use metadata and data APIs, data previews and visualizations
*   Manage access control, version history with rollback, INSPIRE/RDF support, user analytics
*   Extend and leverage the full universe of more than 18,000 freely available Drupal modules

## Installation

Please see the [Installation](https://docs.getdkan.com/en/latest/introduction/installation.html) section of the DKAN Documentation.

### Upgrading DKAN

Please see the [Updating and Maintaining DKAN](https://docs.getdkan.com/en/latest/introduction/maintaining.html) section of the DKAN Documentation for general upgrade information.

## Releases

Check the [releases page](https://github.com/GetDKAN/dkan/releases) for latest DKAN Version. 7.x-1.x is the development branch.

### Releases and release candidates

DKAN follows a modified semantic versioning convention, and has _major_, _point_ (also known as _minor_), and _patch_ releases.

The only _major_ release of DKAN has been 7.x-1.0. It is unlikely there will be a 7.x-2.x version of DKAN but in the case of a major architecture change, this is possible. More likely is a 8.x-2.x release if and when DKAN is ported to Drupal 8. At the moment there is no work being done on a Drupal 8 version.

_Point_ releases occur approximately every 1-2 months and include new functionality and architectural changes. For instance, DKAN 7.x-1.1 was the first point release, and 7.x-1.2 was the second. While we try to make updating as seamless as possible, _point release_ updates often involve some work, especially if the website uses a custom theme or modules outside of what is included in the distro.

_Patch_ releases, introduced after the release of DKAN 7.x-1.12, occur much more frequently, and include bug fixes, core and contrib module updates, and minor enhancements. The first patch release was version 7.x-1.12.1, the second was 7.x-1.12.2, and so on. Updating to a new _patch_ release should be very straightforward and cause little to no distruption to a website.

### Tags and branches

After a _point_ release comes out, we create a _release branch_, on which we do any work intended for future _patch_ releases on that version of DKAN. The _release branch_ for version 7.x-1.12 development, for instance, is `release-1-12`. New features and other work destined for the next _point release_ continues on the main development branch, `7.x-1.x`.

We keep the DKAN profile (this project), [DKAN Dataset](https://github.com/GetDKAN/dkan_dataset), [DKAN Datastore](https://github.com/GetDKAN/dkan_datastore), [DKAN Workflow](https://github.com/GetDKAN/dkan_workflow) and [Recline](https://github.com/GetDKAN/recline) versioning in sync. Other depdendencies that we maintain, incuding [Open Data Schema Map](https://github.com/GetDKAN/open_data_schema_map) and [Visualization Entity](https://github.com/GetDKAN/visualization_entity) follow their own, separate release cycle.

## License

DKAN and related modules are freely-available under the [ GPLv2 (or later) ](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html) license.

## Contributing

Please file all tickets for DKAN [in this issue queue](https://github.com/GetDKAN/dkan/issues). We have several labels in place for you to tag the issue with and identify it with the proper component.

Please follow the [Ticket Template](https://github.com/GetDKAN/dkan/blob/7.x-1.x/.github/CONTRIBUTING.md#new-feature-template) when creating new tickets.

Also, please remember to reference the issue across repositories in order for maintainers to pick up commits and pull requests looking at the issue. You can do that for commits like this:

```bash
git commit -m "Issue GetDKAN/dkan#<issue_number>: ..."
```

Just replace **<issue_number>** with the actual issue number. You can reference pull requests exactly like that if you add the same text **"GetDKAN/dkan#&lt;issue_number&gt;"** in a comment.

This helps us with detecting changes and pulling them in faster.
