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

  $resource = new Resource($nid);

  $datadict_machine_name = $resource->getDataDictSchemaSpec();

  if (empty($datadict_machine_name)) {
    $message = t(
      "Node \"@node_title\" [@node_id] does not have a Data Dictionary Spec set.",
      array(
        '@node_title' => $resource->label(),
        '@node_id' => $resource->getIdentifier(),
      )
    );
    return drush_log($message, 'error');
  }

  $dataDict = dkan_data_dictionary_dictionary_load($datadict_machine_name);

  if (empty($dataDict)) {
    $message = t(
      "Failed to load the Data Dictionary Spec \"@datadict_machine_name\" associated to node \"@node_title\" [@node_id].",
      array(
        '@node_title' => $resource->label(),
        '@node_id' => $resource->getIdentifier(),
        '@datadict_machine_name' => $datadict_machine_name,
      )
    );
    return drush_log($message, 'error');
  }

  $schema = $resource->getDataDictSchema();

  if (empty($schema)) {
    $message = t("Data Dictionary associated to node \"@node_title\" [@node_id] is empty.",
      array(
        '@node_title' => $resource->label(),
        '@node_id' => $resource->getIdentifier(),
      )
    );
    return drush_log($message, 'error');
  }

  if(!$dataDict->hasManager()) {
    $message = t("Data Dictionary associated to node \"@node_title\" [@node_id] does not support data validation.",
      array(
        '@node_title' => $resource->label(),
        '@node_id' => $resource->getIdentifier(),
      )
    );
    return drush_log($message, 'error');
  }
  else {
    $manager = $dataDict->getDataDictionaryManager($resource);
  }

  $step = 20;
  $manager->validate($step);
}
