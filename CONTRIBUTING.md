# Contribute to DKAN!

Thanks for your interest in contributing to DKAN.

## Coding standards

We use the Drupal and DrupalPractice ruleset from the Drupal Coder project, along with the PHP_CS toolset.

Your IDE probably already has an easy way to use this configuration. If it doesn't, you can add it to your development environment:

    ddev composer require --dev drupal/coder:@stable

You can also these scripts to your composer.json file to make it easier to perform the scan:

    "scripts": {
        "phpcs": "./vendor/bin/phpcs --standard=docroot/modules/contrib/dkan/phpcs.xml",
        "phpcbf": "./vendor/bin/phpcbf --standard=docroot/modules/contrib/dkan/phpcs.xml"
    }

Use `phpcs` to scan the codebase. Use `phpcbf` to automatically fix many errors.
