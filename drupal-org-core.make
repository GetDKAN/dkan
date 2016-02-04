api: '2'
core: 7.x
projects:
  drupal:
    type: core
    version: '7.41'
    # Use vocabulary machine name for permissions, see http://drupal.org/node/995156
    patch:
      995156: 'http://drupal.org/files/issues/995156-5_portable_taxonomy_permissions.patch'
