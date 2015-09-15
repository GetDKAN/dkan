# Breadcrumbs in DKAN

Breadcrumbs in DKAN are all configured by this module.

## Path Breadcrumbs Features

DKAN uses Path Breadcrumbs to override Drupal's core breadcrumb functionality. Path Breadcrumbs can be administered at ``admin/structure/path-breadcrumbs``.

Newly created breadcrumb rules or overridden rules should be captured using Features and Features Overrides modules in a custom module.

## Path Breadcrumb Theme Functions

The dkan_sitewide_breadcrumbs.module file contains two overrides, one to add the home icon to the breadcrumb and the second to control the breadcrumb for the dataset creation form.
