# DKAN
An Open Data Catalog module for [Drupal 10+](https://www.drupal.org/documentation).

[![GPL license](https://img.shields.io/badge/License-GPL(>=2)-blue.svg)](http://www.gnu.org/licenses/gpl.html)
[![DPG Badge](https://img.shields.io/badge/Verified-DPG-3333AB?logo=data:image/svg%2bxml;base64,PHN2ZyB3aWR0aD0iMzEiIGhlaWdodD0iMzMiIHZpZXdCb3g9IjAgMCAzMSAzMyIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE0LjIwMDggMjEuMzY3OEwxMC4xNzM2IDE4LjAxMjRMMTEuNTIxOSAxNi40MDAzTDEzLjk5MjggMTguNDU5TDE5LjYyNjkgMTIuMjExMUwyMS4xOTA5IDEzLjYxNkwxNC4yMDA4IDIxLjM2NzhaTTI0LjYyNDEgOS4zNTEyN0wyNC44MDcxIDMuMDcyOTdMMTguODgxIDUuMTg2NjJMMTUuMzMxNCAtMi4zMzA4MmUtMDVMMTEuNzgyMSA1LjE4NjYyTDUuODU2MDEgMy4wNzI5N0w2LjAzOTA2IDkuMzUxMjdMMCAxMS4xMTc3TDMuODQ1MjEgMTYuMDg5NUwwIDIxLjA2MTJMNi4wMzkwNiAyMi44Mjc3TDUuODU2MDEgMjkuMTA2TDExLjc4MjEgMjYuOTkyM0wxNS4zMzE0IDMyLjE3OUwxOC44ODEgMjYuOTkyM0wyNC44MDcxIDI5LjEwNkwyNC42MjQxIDIyLjgyNzdMMzAuNjYzMSAyMS4wNjEyTDI2LjgxNzYgMTYuMDg5NUwzMC42NjMxIDExLjExNzdMMjQuNjI0MSA5LjM1MTI3WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cg==)](https://www.digitalpublicgoods.net/r/dkan)
[![pipeline status](https://git.drupalcode.org/project/dkan/badges/4.x/pipeline.svg)](https://git.drupalcode.org/project/dkan/-/commits/4.x)
[![Documentation Status](https://app.readthedocs.org/projects/dkan/badge/?version=4.x)](https://dkan.readthedocs.io/en/latest/?badge=4.x)
[![Drupal Core](https://img.shields.io/badge/Drupal%20core-10%20%7C%2011-0678BE)](https://www.drupal.org/project/drupal)

## Documentation
DKAN's full documentation can be found at [dkan.readthedocs.io](https://dkan.readthedocs.io/en/4.x).

## DKAN v4

DKAN v1 and v2 were published on Github. DKAN 2 was available as a composer package
but from packagist.org, as `getdkan/dkan`.  In March 2026 we released DKAN 4 on Drupal.org.
It can now be added to any Drupal project as `drupal/dkan`. 

Besides moving to the new repository, DKAN 4 introduces the following changes:

* All submodules now follow the `dkan_` naming convention. For instance, DKAN v2
has a "datastore" module; that is now "dkan_datastore".
* [JSON Form Widget](https://www.drupal.org/project/json_form_widget) is no longer a submodule of DKAN, but a standalone module on Drupal.org.
* Several classes and methods marked as deprecated in v2 are now removed.

If you are already using DKAN v2, please to not upgrade before reading the Upgrade 
Guide in the [DKAN v2 documentation site](https://dkan.readthedocs.io/en/4.x/).

## Move to Drupal.org

In October 2026, we moved completely to git.drupalcode.org for issues, PRs and all
other development and project management tasks. We are not performing any bulk 
migration of issues and MRs from the [old GitHub repository](https://github.com/Getdkan/dkan)
to Drupal.org. Your issues may be copied over ad-hoc, but if you want to ensure
that they are represented you may re-open them yourself.

## Features

- Harvesting of data from external catalogs that provide a data.json
- JSON-based metadata catalog, with user-defined schemas
- Out-of-the-box support for DCAT-US metadata standard
- Web service API endpoints that provide remote/automated management of datasets
- Integration with a decoupled [REACT front end](https://github.com/getdkan/data-catalog-app)
- A datastore to store CSV data files in the database and make them queryable by third party applications.

## Contributing

- [Code of conduct](https://dkan.readthedocs.io/en/4.x/contributing/code_of_conduct.html)
- [Submission guidelines](https://dkan.readthedocs.io/en/4.x/contributing/submission_guidelines.html)
- [Create an issue](https://git.drupalcode.org/project/dkan/-/work_items/new?type=Issue&initialCreationContext=list-route)
- [Set up local sandbox with DDEV](https://dkan.readthedocs.io/en/4.x/developer-guide/dev_local_setup.html)

## License

DKAN and related modules are freely-available under the ["GNU General Public License, version 2 or any later version"](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html) license.

## Security

DKAN is covered by the [Drupal Security Advisory Policy](https://www.drupal.org/drupal-security-team/security-advisory-process-and-permissions-policy). If you have found a vulnerability in DKAN, please [report it confidentially](https://www.drupal.org/docs/develop/issues/issue-procedures-and-etiquette/reporting-a-security-issue).

## History

- DKAN’s initial v1.0 release was in 2014 (this code is still available on the [7.x-1.x branch](https://github.com/GetDKAN/dkan/tree/7.x-1.x), although no longer supported).
- In the fall of 2017, CivicActions took over sponsorship and maintenance of DKAN.
- In May 2020 CivicActions released a completely rewritten version of DKAN to support Drupal 8, then 9 and 10. This new version (v2) was a complete ground up rebuild of the platform, integrating architectural insight from DKAN v1 and many new capabilities.
- DKAN v4 released in March 2026, moving releases to Drupal.org
- Full project migration to git.drupalcode.org in October 2026