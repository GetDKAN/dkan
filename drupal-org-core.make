api = 2
core = 7.x

projects[drupal][type] = core
projects[drupal][version] = "7.27"

; Use vocabulary machine name for permissions
; http://drupal.org/node/995156
projects[drupal][patch][995156] = http://drupal.org/files/issues/995156-5_portable_taxonomy_permissions.patch
; Entity error in PHP 5.4 https://drupal.org/node/1525176 .
projects[drupal][patch][1525176] = https://drupal.org/files/issues/1525176_1.patch
