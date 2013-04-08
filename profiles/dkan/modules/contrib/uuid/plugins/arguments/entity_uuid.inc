<?php

/**
 * @file
 * Plugin to provide an argument handler for all entity IDs.
 */

/**
 * Plugins are described by creating a $plugin array which will be used
 * by the system that includes this file.
 */
$plugin = array(
  'title' => t("Entity: UUID"),
  'description' => t('Creates an entity context from an entity UUID argument.'),
  'context' => 'uuid_entity_uuid_context',
  'get child' => 'uuid_entity_uuid_get_child',
  'get children' => 'uuid_entity_uuid_get_children',
);

function uuid_entity_uuid_get_child($plugin, $parent, $child) {
  $plugins = uuid_entity_uuid_get_children($plugin, $parent);
  return $plugins[$parent . ':' . $child];
}

function uuid_entity_uuid_get_children($original_plugin, $parent) {
  $entities = entity_get_info();
  $plugins = array();
  foreach ($entities as $entity_type => $entity) {
    $plugin = $original_plugin;
    $plugin['title'] = t('@entity: UUID', array('@entity' => $entity['label']));
    $plugin['keyword'] = $entity_type;
    $plugin['description'] = t('Creates @entity context from an UUID argument.', array('@entity' => $entity_type));
    $plugin['name'] = $parent . ':' . $entity_type;
    $plugin_id = $parent . ':' . $entity_type;
    drupal_alter('ctools_entity_context', $plugin, $entity, $plugin_id);
    $plugins[$plugin_id] = $plugin;
  }
  drupal_alter('ctools_entity_contexts', $plugins);
  return $plugins;
}

/**
 * Discover if this argument gives us the entity we crave.
 */
function uuid_entity_uuid_context($arg = NULL, $conf = NULL, $empty = FALSE) {
  $entity_type = explode(':', $conf['name']);
  $entity_type = $entity_type[1];
  // If unset it wants a generic, unfilled context.
  if ($empty) {
    return ctools_context_create_empty('entity:' . $entity_type);
  }

  // We can accept either an entity object or a pure id.
  if (is_object($arg)) {
    return ctools_context_create('entity:' . $entity_type, $arg);
  }

  if (!is_string($arg)) {
    return FALSE;
  }
  $entity_ids = entity_get_id_by_uuid($entity_type, array($arg));
  if (!isset($entity_ids[$arg])) {
    return FALSE;
  }
  $entity_id = $entity_ids[$arg];
  $entities = entity_load($entity_type, array($entity_id));
  if (!$entities) {
    return FALSE;
  }

  return ctools_context_create('entity:' . $entity_type, $entities[$entity_id]);
}
