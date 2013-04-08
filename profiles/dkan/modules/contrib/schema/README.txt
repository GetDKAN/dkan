
Schema module

PREREQUISITES

Drupal 7.x

OVERVIEW

Introduced in Drupal 6, the Schema API allows modules to declare their
database tables in a structured array (similar to the Form API) and
provides API functions for creating, dropping, and changing tables,
columns, keys, and indexes.

The Schema module provides additional Schema-related functionality not
provided by the core Schema API that is useful for module
developers. Currently, this includes:

* Schema documentation: hyperlinked display of the schema's embedded
  documentation explaining what each table and field is for. 
* Schema structure generation: the module examines the live database
  and creates Schema API data structures for all tables that match the
  live database. 
* Schema comparison: the module compares the live database structure
  with the schema structure declared by all enabled modules, reporting
  on any missing or incorrect tables. 

Note for MySQL users: The Schema module requires MySQL 5. Prior
versions of MySQL do not support the INFORMATION_SCHEMA database that
the Schema module uses to inspect the database.

INSTALLATION

Install and activate Schema like every other Drupal module.

ADMINISTRATOR USAGE

Visit Administer >> Structure >> Schema to access Schema's UI
functionality.

AUTHOR

Barry Jaspan
firstname at lastname dot org
