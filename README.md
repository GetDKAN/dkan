# DKAN
An Open Data Catalog module for [Drupal 10+](https://www.drupal.org/documentation).

[![GetDKAN](https://circleci.com/gh/GetDKAN/dkan/tree/2.x.svg?style=svg)](https://circleci.com/gh/GetDKAN/dkan/tree/2.x)
[![Maintainability](https://qlty.sh/gh/GetDKAN/projects/dkan/maintainability.svg)](https://qlty.sh/gh/GetDKAN/projects/dkan)
[![Code Coverage](https://qlty.sh/gh/GetDKAN/projects/dkan/coverage.svg)](https://qlty.sh/gh/GetDKAN/projects/dkan)
[![GPL license](https://img.shields.io/badge/License-GPL(>=2)-blue.svg)](http://www.gnu.org/licenses/gpl.html)
[![DPG Badge](https://img.shields.io/badge/Verified-DPG-3333AB?logo=data:image/svg%2bxml;base64,PHN2ZyB3aWR0aD0iMzEiIGhlaWdodD0iMzMiIHZpZXdCb3g9IjAgMCAzMSAzMyIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE0LjIwMDggMjEuMzY3OEwxMC4xNzM2IDE4LjAxMjRMMTEuNTIxOSAxNi40MDAzTDEzLjk5MjggMTguNDU5TDE5LjYyNjkgMTIuMjExMUwyMS4xOTA5IDEzLjYxNkwxNC4yMDA4IDIxLjM2NzhaTTI0LjYyNDEgOS4zNTEyN0wyNC44MDcxIDMuMDcyOTdMMTguODgxIDUuMTg2NjJMMTUuMzMxNCAtMi4zMzA4MmUtMDVMMTEuNzgyMSA1LjE4NjYyTDUuODU2MDEgMy4wNzI5N0w2LjAzOTA2IDkuMzUxMjdMMCAxMS4xMTc3TDMuODQ1MjEgMTYuMDg5NUwwIDIxLjA2MTJMNi4wMzkwNiAyMi44Mjc3TDUuODU2MDEgMjkuMTA2TDExLjc4MjEgMjYuOTkyM0wxNS4zMzE0IDMyLjE3OUwxOC44ODEgMjYuOTkyM0wyNC44MDcxIDI5LjEwNkwyNC42MjQxIDIyLjgyNzdMMzAuNjYzMSAyMS4wNjEyTDI2LjgxNzYgMTYuMDg5NUwzMC42NjMxIDExLjExNzdMMjQuNjI0MSA5LjM1MTI3WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cg==)](https://www.digitalpublicgoods.net/r/dkan)

## Documentation
DKAN's full documentation can be found at [dkan.readthedocs.io](https://dkan.readthedocs.io/en/4.x).

## Update: DKAN v4

DKAN v1 and v2 were published on Github. DKAN 2 was available as a composer package
but from packagist.org, as `getdkan/dkan`.  In March 2026 we released 
[DKAN 4 on Drupal.org](https://www.drupal.org/project/dkan). It can now be added to any Drupal
project as `drupal/dkan`. For now, we continue to use Github for development, and create PRs 
and issues there. However, we are not creating 4.x releases on Github. The 4.x branch is
mirrored to git.drupalcode.org, and we create tags and
[releases](https://www.drupal.org/project/dkan/releases) there.

Our long-term plan is to move issues and MRs to Drupal.org as well. We are going to wait,
at minimum, until [the new Gitlab-based issue system becomes available](https://www.drupal.org/project/drupalorg/issues/3409678). 
For now, please submit issues, questions or PRs to [our Github repository](https://github.com/GetDKAN/dkan/).

Besides moving to the new repository, DKAN 4 introduces the following changes:

* All submodules now follow the `dkan_` naming convention. For instance, DKAN v2
has a "datastore" module; that is now "dkan_datastore".
* [JSON Form Widget](https://www.drupal.org/project/json_form_widget) is no longer a submodule of DKAN, but a standalone module on Drupal.org.
* Several classes and methods marked as deprecated in v2 are now removed.

If you are already using DKAN v2, please to not upgrade before reading the Upgrade 
Guide in the [DKAN v2 documentation site](https://dkan.readthedocs.io/en/4.x/). However, 
**do upgrade soon, as all new features and most bugfixes will be exclusive to version 4
going forward**.

## Features

- Harvesting of data from external catalogs that provide a data.json
- JSON-based metadata catalog, with user-defined schemas
- Out-of-the-box support for DCAT-US metadata standard
- Web service API endpoints that provide remote/automated management of datasets
- Integration with a decoupled [REACT front end](https://github.com/getdkan/data-catalog-app)
- A datastore to store CSV data files in the database and make them queryable by third party applications.

## Contributing

- [Code of conduct](https://dkan.readthedocs.io/en/2.x/contributing/code_of_conduct.html)
- [Submission guidelines](https://dkan.readthedocs.io/en/2.x/contributing/submission_guidelines.html)
- [Create an issue](https://github.com/GetDKAN/dkan/issues/new/choose)
- [Set up local sandbox with DDEV](https://dkan.readthedocs.io/en/2.x/developer-guide/dev_local_setup.html)

## License

DKAN and related modules are freely-available under the ["GNU General Public License, version 2 or any later version"](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html) license.

## Security

If you have found a vulnerability in DKAN, please report this by e-mailing dkan-security@civicactions.com.

## History

- DKAN’s initial v1.0 release was in 2014 (this code is still available on the [7.x-1.x branch](https://github.com/GetDKAN/dkan/tree/7.x-1.x), although no longer supported).
- In the fall of 2017, CivicActions took over sponsorship and maintenance of DKAN.
- In May 2020 CivicActions released a completely rewritten version of DKAN to support Drupal 8, then 9 and 10. This new version (v2) was a complete ground up rebuild of the platform, integrating architectural insight from DKAN v1 and many new capabilities.
- DKAN v4 released in March 2026, moving releases to Drupal.org
