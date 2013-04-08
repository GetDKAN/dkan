
INTRODUCTION
------------

This module provides an API for adding universally unique identifiers (UUID) to
Drupal objects, most notably entities.

FEATURES
--------

 * Automatic UUID generation:
   UUIDs will be generated for all core entities. An API is provided for other
   modules to enable support for custom entities.
 * UUID API for entities, properties and fields:
   With this unified API you can load entities with entity_uuid_load() so that
   all supported properties and fields are made with UUID references. You can
   also save entities formatted this way with entity_uuid_save() (depends on
   Entity API).
 * Export entities to use as default/demo content:
   The integration with Features module provides the ability to export UUID
   enabled entities with intact dependencies and references to other entities.
   This functionality depends on Deploy module 7.x-2.0-alpha1 (soon to be
   released) and is probably the most robust way for installation profiles and
   distributions to provide demo content!
 * Services integration:
   The integration with Services module alters all UUID enabled entity resources
   (nodes, users, taxonomies etc) to be based on UUIDs instead. This way it
   becomes easier to share and integrate content between sites. This
   functionality is used by Deploy module.
 * More integrations:
   UUID module integrates with Views, Token, Rules and provides some CTools
   plugins.
