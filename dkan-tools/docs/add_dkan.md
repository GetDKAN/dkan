# Adding DKAN to an existing Drupal Site

```bash
composer require 'getdkan/dkan'
dktl drush en dkan
dktl dkan:sample-content:create
dktl frontend:install
dktl frontend:build
```
