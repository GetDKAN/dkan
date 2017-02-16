api: '2'
core: 7.x
projects:
  drupal:
    type: core
    version: '7.54'
    # Use vocabulary machine name for permissions, see http://drupal.org/node/995156
    patch:
      995156: 'http://drupal.org/files/issues/995156-5_portable_taxonomy_permissions.patch'
      # Notice: Undefined index: #field_name in file_managed_file_save_upload()
      1903010: 'https://www.drupal.org/files/issues/drupal-undefinedindex_fileupload-1903010-4.patch'
      # Warning: filesize(): stat failed
      628094: 'https://www.drupal.org/files/issues/file.remote-file_save.628094.22.patch'
      #State error with select multiple
      2844358: 'https://www.drupal.org/files/issues/drupal_bug_multiple_values_select_states.patch'
