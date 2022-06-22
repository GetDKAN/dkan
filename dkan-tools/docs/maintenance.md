# Primary Maintenance Tasks

A DKAN site does not differ substantially from [maintaining other Drupal
sites](https://www.drupal.org/docs/8/configuration-management).

By "maintenance" we mean three specific tasks

-  **Upgrading** DKAN to receive new features and bug-fixes
-  **Adding** additional modules or features
-  **Overriding** current modules or functionally

## Getting DKAN Updates

DKAN uses a slightly modified [semantic](https://www.drupal.org/docs/8/understanding-drupal-version-numbers) versioning system.

**Major.Minor.Patch**

- *Major* indicates compatibility
- *Minor* indicates backwards compatible new features or upgrades
- *Patch* indicates a release for security updates and bug fixes

Please note *you can not use* ``drush up`` *with DKAN*. This is because
DKAN is not packaged on Drupal.org.

### Basic Upgrades

If you are maintaining your site with [DKAN Tools](https://github.com/getdkan/dkan-tools), upgrading is as simple as running


```bash
dktl composer require 'getdkan/dkan:2.1.0'
```

Or edit the version number in your composer.json file:

```bash
"require": {
    "getdkan/dkan": "2.0.0"
}
```

 and run `composer update`

### Upgrading DKAN from 7.x-1.x to 8.x-2.x

The easiest method will be to stand up a fresh Drupal 8 DKAN site and harvest the datasets from your Drupal 7 DKAN site.

A more detailed upgrade path will be documented soon.



## Adding modules to your project


```bash
dktl composer require 'drupal/group:1.2'
```
