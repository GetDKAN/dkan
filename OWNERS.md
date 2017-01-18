#OWNERS FILE

Purpose
--------
This file is to help clarify which NuCivic teams are responsible for what folder and files in this repository. Internally this helps us to be more efficient and have better quality control.

NuCivic is the steward of DKAN though as an open source project we seek to make it as easy as possible for contributors outside of NuCivic to contribute.

Teams
------

* **Product** - The DKAN product team, internally at NuCivic called "Mars". Generally responsible for the product itself, releases, and functional tests.
* **DevOps** - The NuCivic DevOps team, internally at NuCivic  called "Pluto". Generally responsile for development environments, testing frameworks, and automation.

Internal Process
-------
To create an issue internally, make sure it's assigned to the owner team that's appropriate.

To create a pull request, Make sure the PR is reviewed and merged by the appropriate team. For cross-team issues, get signoff from each team before merging.

Owners
------

###Product
Product owns everything by default.

- `DEFAULT`

### DevOps

- `./.ahoy/*`
- `./*.sh`
- `./circle*`
- `./.probo.yml`
- `./*.make` (exluding `./drupal-org-core.make` and `./drupal-org.make`)
- `./test/*` (excluding `./test/features/*`)
