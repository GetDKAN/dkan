Roles and Permissions
=====================

When you set up your Drupal site with DKAN, create a role to assign to data publishers. The role should include permissions for creating, editing, and deleting the Data node type. These permissions will apply to both the admin UI and data content management via the API.

Recommended permissions for a data publisher role:
  - 'access administration pages'
  - 'access content'
  - 'access content overview'
  - 'access toolbar'
  - 'administer data dictionary settings'
  - 'administer harvest_plan'
  - 'administer harvest_run'
  - 'administer metastore settings'
  - 'administer resource mapping'
  - 'create data content'
  - datastore_api_drop
  - datastore_api_import
  - 'delete any data content'
  - 'delete data revisions'
  - 'delete own data content'
  - 'edit any data content'
  - 'edit own data content'
  - harvest_api_index
  - harvest_api_info
  - harvest_api_register
  - harvest_api_run
  - 'revert data revisions'
  - 'use dkan_publishing transition archive'
  - 'use dkan_publishing transition create_new_draft'
  - 'use dkan_publishing transition hidden'
  - 'use dkan_publishing transition publish'
  - 'use dkan_publishing transition restore'
  - 'view data revisions'
  - 'view own unpublished content'
  - 'view the administration theme'

Currently DKAN installs with a role (api_user) that is used for testing and a deprecated permission: `post put delete datasets through the api`. These will be removed in a future release.
