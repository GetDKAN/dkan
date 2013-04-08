<?php
/**
 * Implementation of hook_features_export().
 *
 * Defines one or more component types that are available to Features for export
 * and a variety of settings for each type.
 */
function rdf_mappings_features_export($data, &$export, $module_name = '') {
  // Any feature exporting RDF mappings need the rdf and rdfx modules.
  $export['dependencies']['rdf'] = 'rdf';
  $export['dependencies']['rdfx'] = 'rdfx';

  foreach ($data as $name) {
    $parts = explode('-', $name);
    $entity_type = $parts[0];
    $bundle_name = $parts[1];

    if ($rdf_mapping = rdf_mapping_load($entity_type, $bundle_name)) {
      $export['features']['rdf_mappings'][$entity_type . '-' . $bundle_name] = $rdf_mapping;
    }
  }

  return array();
}

/**
 * Implementation of hook_features_export_options().
 *
 * Provides an array of components that can be exported for a given type.
 */
function rdf_mappings_features_export_options() {
  $bundles = array();

  foreach (entity_get_info() as $entity_type => $entity) {
    foreach ($entity['bundles'] as $bundle_name => $bundle) {
      $bundles[$entity_type . '-' . $bundle_name] = $entity['label'] . ': ' . $bundle['label'];
    }
  }

  return $bundles;
}

/**
 * Implementation of hook_features_export_render().
 *
 * Renders a set of components to code as a defaults hook.
 */
function rdf_mappings_features_export_render($module, $data, $export = NULL) {
  $code = array();
  $code[] = '  $rdf_mappings = array();';
  $code[] = '';

  foreach ($data as $key => $entity_type_bundle) {
    if (is_array($entity_type_bundle)) {
      $entity_type_bundle = $key;
    }
    $parts = explode('-', $entity_type_bundle);
    $entity_type = $parts[0];
    $bundle_name = $parts[1];
    if ($rdf_mapping = rdf_mapping_load($entity_type, $bundle_name)) {
      $rdf_mapping_export = features_var_export($rdf_mapping, '  ');
      $rdf_bundle = features_var_export($bundle_name);
      $rdf_entity_type = features_var_export($entity_type);
      $code[] = "  // Exported RDF mapping: {$bundle_name}";
      $code[] = "  \$rdf_mappings[$rdf_entity_type][$rdf_bundle] = $rdf_mapping_export;";
      $code[] = "";
    }
  }

  $code[] = '  return $rdf_mappings;';
  $code = implode("\n", $code);
  return array('rdf_default_mappings' => $code);
}

/**
 * Implementation of hook_features_revert().
 *
 * Reverts components of a feature back to their default state.
 */
function rdf_mappings_features_revert($module) {
  return rdf_mappings_features_rebuild($module);
}

/**
 * Implementation of hook_features_rebuild().
 *
 * Updates faux-exportable components back to their default state.
 */
function rdf_mappings_features_rebuild($module) {
  if ($defaults = features_get_default('rdf_mappings', $module)) {
    foreach ($defaults as $entity_type => $bundles) {
      foreach ($bundles as $bundle => $mapping) {
        rdf_mapping_save(array(
          'type' => $entity_type,
          'bundle' => $bundle,
          'mapping' => $mapping,
        ));
      }
    }
  }
}
