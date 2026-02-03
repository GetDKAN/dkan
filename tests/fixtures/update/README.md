# DB fixtures for update path tests

This folder is for fixtures to use against [update path tests](https://www.drupal.org/docs/drupal-apis/update-api/writing-automated-update-tests-for-drupal-8-or-later).
To create a new dump fixture:

1. Build a [DKAN development instance](https://dkan.readthedocs.io/en/latest/developer-guide/dev_local_setup.html)
against the branch or tag you want to use as a baseline.

2. Run `ddev dkan-site-install`, then `ddev dkan-sample-content`.

3. Making sure you are in your project root, run:

```sh
ddev exec php ./web/core/scripts/db-tools.php dump-database-d8-mysql | gzip > ./tests/fixtures/update/YOUR_DUMP_NAME.php.gz
```
