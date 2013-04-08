<?php

/**
 * Implements hook_features_api().
 */
function taxonomy_features_api() {
  return array(
    'taxonomy' => array(
      'name' => t('Taxonomy'),
      'feature_source' => TRUE,
      'default_hook' => 'taxonomy_default_vocabularies',
      'default_file' => FEATURES_DEFAULTS_INCLUDED,
    ),
  );
}

/**
 * Implements hook_features_export_options().
 */
function taxonomy_features_export_options() {
  $vocabularies = array();
  foreach (taxonomy_get_vocabularies() as $vocabulary) {
    $vocabularies[$vocabulary->machine_name] = $vocabulary->name;
  }
  return $vocabularies;
}

/**
 * Implements hook_features_export().
 *
 * @todo Test adding existing dependencies.
 */
function taxonomy_features_export($data, &$export, $module_name = '') {
  $pipe = array();

  // taxonomy_default_vocabularies integration is provided by Features.
  $export['dependencies']['features'] = 'features';
  $export['dependencies']['taxonomy'] = 'taxonomy';

  // Add dependencies for each vocabulary.
  $map = features_get_default_map('taxonomy');
  foreach ($data as $machine_name) {
    if (isset($map[$machine_name]) && $map[$machine_name] != $module_name) {
      $export['dependencies'][$map[$machine_name]] = $map[$machine_name];
    }
    else {
      $export['features']['taxonomy'][$machine_name] = $machine_name;

      $fields = field_info_instances('taxonomy_term', $machine_name);
      foreach ($fields as $name => $field) {
        $pipe['field'][] = "taxonomy_term-{$field['bundle']}-{$field['field_name']}";
        $pipe['field_instance'][] = "taxonomy_term-{$field['bundle']}-{$field['field_name']}";
      }
    }
  }
  return $pipe;
}

/**
 * Implements hook_features_export_render().
 */
function taxonomy_features_export_render($module, $data) {
  $vocabularies = taxonomy_get_vocabularies();
  $code = array();
  foreach ($data as $machine_name) {
    foreach ($vocabularies as $vocabulary) {
      if ($vocabulary->machine_name == $machine_name) {
        // We don't want to break the entity cache, so we need to clone the
        // vocabulary before unsetting the id.
        $vocabulary = clone $vocabulary;
        unset($vocabulary->vid);
        $code[$machine_name] = $vocabulary;
      }
    }
  }
  $code = "  return ". features_var_export($code, '  ') .";";
  return array('taxonomy_default_vocabularies' => $code);
}

/**
 * Implements hook_features_revert().
 */
function taxonomy_features_revert($module) {
  taxonomy_features_rebuild($module);
}

/**
 * Implements hook_features_rebuild().
 *
 * Rebuilds Taxonomy vocabularies from code defaults.
 */
function taxonomy_features_rebuild($module) {
  if ($vocabularies = features_get_default('taxonomy', $module)) {
    $existing = taxonomy_get_vocabularies();
    foreach ($vocabularies as $vocabulary) {
      $vocabulary = (object) $vocabulary;
      foreach ($existing as $existing_vocab) {
        if ($existing_vocab->machine_name === $vocabulary->machine_name) {
          $vocabulary->vid = $existing_vocab->vid;
        }
      }
      taxonomy_vocabulary_save($vocabulary);
    }
  }
}
