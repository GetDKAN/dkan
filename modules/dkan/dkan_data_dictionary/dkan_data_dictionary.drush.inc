<?php

/**
 * @file
 * Dkan_harvest.drush.inc.
 */

use Dkan\DataDictionary\Resource;

/**
 *
 */
function dkan_data_dictionary_drush_command() {
  $items = array();

  // Validate a resource.
  $items['dkan-validate-resource'] = array(
    'aliases' => array('dkan-vr', 'dkan-validate-resource'),
    'description' => 'Validate a single resource agaist it\'s data dictionary.',
    'arguments' => array(
      'nid' => 'NID of the Resource to be validated.',
    ),
    'options' => array(),
    'drupal dependencies' => array('dkan_data_dictionary'),
  );

  return $items;
}

/**
 *
 */
function drush_dkan_data_dictionary_dkan_validate_resource($nid) {

  $nodemw = entity_metadata_wrapper('node', $nid);

  $validator_spec = $nodemw->field_describedby_spec->value();
  $validatorWrapper = dkan_data_dictionary_validator_load($validator_spec);

  // Get Schema.
  $schema = NULL;

  $describedby_file = $nodemw->field_describedby_file->value();
  if (!empty($describedby_file)) {
    // TODO load file.
  }
  else {
    $schema = $nodemw->field_describedby_schema->value();
  }

  // Get Data.
  $resource = Resource::createFromDrupalNodeNid($nid);
  $data = $resource->getFilePath();

  // TODO add check.
  $manager = $validatorWrapper->getValidationManager();

  $manager->init($schema, $data);

  $count = 1;
  $step = 20;
  while (TRUE) {
    $index = $count + $step;
    drush_print("validating rows " . $count . " -> " . $index);
    list($output, $errors) = $manager->validateChunk($step);

    if (!empty($errors)) {
      drush_print("errors found: ..");
      drush_print_table($errors);
    }

    if (count($output) < $step) {
      break;
    }

    $count = $count + $step;
  }
}
