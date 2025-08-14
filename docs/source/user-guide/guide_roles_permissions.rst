Roles and Permissions
=====================

When you set up your Drupal site with DKAN, create a role to assign to data publishers. The role should include permissions for creating, editing, and deleting the Data node type. These permissions will apply to both the admin UI and data content management via the API.

Currently DKAN installs with a role (api_user) that is used for testing and a deprecated permission: `post put delete datasets through the api`. These will be removed in a future release.
