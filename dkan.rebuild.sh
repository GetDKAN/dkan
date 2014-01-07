#!/bin/bash
echo "Rebuilding..."
rm -rf modules/contrib themes/contrib libraries/
drush -y make --no-core --contrib-destination=. drupal-org.make --no-recursion
