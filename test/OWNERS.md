#OWNERS FILE

Purpose
--------
This file is to help clarify which teams are responsible for what folder and files in this repository.

Teams
------

* **Product** - The DKAN product team, internally called "Mars". Generally responsible for the product itself, releases, and functional tests.
* **DevOps** - The NuCivic DevOps team, internally called "Pluto". Generally responsile for development environments, testing frameworks, and automation.

Process
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
