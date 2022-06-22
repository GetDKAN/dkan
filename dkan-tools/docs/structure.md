# File structure of a DKAN-Tools-based project

One of the many reasons for using DKTL is to create a clear separation between the
code specific to a particular DKAN site (i.e. "custom code") and the dependencies
we pull in from other sources (primarily, DKAN core and Drupal core). Keep all of
your custom code in the _src_ directory and symlink the overrides to the appropriate
directory inside docroot. This will make maintaining your DKAN site much easier.
DKAN Tools will set up the symlinks for you.

To accomplish this separation, DKAN Tools projects will have the following basic
directory structure, created when we run `dktl init`.

    ├── backups           # Optional for local development, see the DB backups section
    ├── docroot           # Drupal core
    |   └── modules
    |       └── contrib
    |           └── dkan # The upstream DKAN core codebase
    |
    ├── src               # Site-specific configuration, code and files.
    │   ├── modules       # Symlinked to docroot/modules/custom
    │   ├── script        # Deployment script and other misc utilities
    |   └── site          # Symlinked to docroot/sites/default
    │   │   └── files     # The main site files
    │   ├── test          # Custom tests
    |   └── themes        # Symlinked to docroot/themes/custom
    └── dktl.yml          # DKAN Tools configuration


## The src/site folder

Most configuration in Drupal sites is placed in the _/sites/default_ directory.

The _/src/site_ folder will replace _/docroot/sites/default_ once Drupal is installed. _/src/site_ should then contain all of the configuration that will be in _/docroot/sites/default_.

DKTL should have already provided some things in _/src/site_: _settings.php_ contains some generalized code that is meant to load any other setting files present, as long as they follow the _settings._\<something\>_.php_ pattern. All of the special settings that you previously had in _settings.php_ or other drupal configuration files should live in _settings.custom.php_ or a similarly-named file in _/src/site_.

## The src/test folder (custom tests)

DKAN Tools supports custom [Cypress](https://www.cypress.io/) tests found in the _src/test/cypress_ directory.

To run custom tests:

```bash
dktl test:cypress
```
