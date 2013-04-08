<?php

/**
 * @file
 * On behalf implementation of Feeds mapping API for path.module.
 */

/**
 * Implements hook_feeds_processor_targets_alter().
 *
 * @see FeedsNodeProcessor::getMappingTargets().
 */
function path_feeds_processor_targets_alter(&$targets, $entity_type, $bundle_name) {
  switch ($entity_type) {
    case 'node':
    case 'taxonomy_term':
    case 'user':
      $targets['path_alias'] = array(
        'name' => t('Path alias'),
        'description' => t('URL path alias of the node.'),
        'callback' => 'path_feeds_set_target',
        'summary_callback' => 'path_feeds_summary_callback',
        'form_callback' => 'path_feeds_form_callback',
      );
      break;
  }
}

/**
 * Callback for mapping. Here is where the actual mapping happens.
 *
 * When the callback is invoked, $target contains the name of the field the
 * user has decided to map to and $value contains the value of the feed item
 * element the user has picked as a source.
 */
function path_feeds_set_target($source, $entity, $target, $value, $mapping) {
  if (empty($value)) {
    $value = '';
  }

  // Path alias cannot be multi-valued, so use the first value.
  if (is_array($value)) {
    $value = $value[0];
  }

  $entity->path = array();

  $entity_type = $source->importer->processor->entityType();

  list($id, , ) = entity_extract_ids($entity_type, $entity);

  if ($id) {
    $uri = entity_uri($entity_type, $entity);

    // Check for existing aliases.
    if ($path = path_load($uri['path'])) {
      $entity->path = $path;
    }
  }

  $entity->path['pathauto'] = FALSE;
  // Allow pathauto to set the path alias if the option is set, and this value
  // is empty.
  if (!empty($mapping['pathauto_override']) && !$value) {
    $entity->path['pathauto'] = TRUE;
  }
  else {
    $entity->path['alias'] = ltrim($value, '/');
  }
}

/**
 * Mapping configuration summary for path.module.
 *
 * @param $mapping
 *   Associative array of the mapping settings.
 * @param $target
 *   Array of target settings, as defined by the processor or
 *   hook_feeds_processor_targets_alter().
 * @param $form
 *   The whole mapping form.
 * @param $form_state
 *   The form state of the mapping form.
 *
 * @return
 *   Returns, as a string that may contain HTML, the summary to display while
 *   the full form isn't visible.
 *   If the return value is empty, no summary and no option to view the form
 *   will be displayed.
 */
function path_feeds_summary_callback($mapping, $target, $form, $form_state) {
  if (!module_exists('pathauto')) {
    return;
  }

  if (empty($mapping['pathauto_override'])) {
    return t('Do not allow Pathauto if empty.');
  }

  else {
    return t('Allow Pathauto if empty.');
  }
}

/**
 * Settings form callback.
 *
 * @return
 *   The per mapping configuration form. Once the form is saved, $mapping will
 *   be populated with the form values.
 */
function path_feeds_form_callback($mapping, $target, $form, $form_state) {
  return array(
    'pathauto_override' => array(
      '#type' => 'checkbox',
      '#title' => t('Allow Pathauto to set the alias if the value is empty.'),
      '#default_value' => !empty($mapping['pathauto_override']),
    ),
  );
}
