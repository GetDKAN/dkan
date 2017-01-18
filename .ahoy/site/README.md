DKAN Site Commands
===========

This is a repository of site  level commands for sites built off of DKAN.

This requires using DKAN Starter which is documented here: http://dkan-starter.readthedocs.io/en/latest/

The following need to be added to your configuration file for the site commands to work:

```yml
private:
  aws:
    scrubbed_data_url: Your AWS bucket URL
  probo:
    password: Your password for QA sites
```
