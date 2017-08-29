<?php

/**
 * @file
 * Dkan_harvest.drush.inc.
 */

/**
 * Implements hook_drush_command().
 */
function dkan_harvest_drush_command() {
  // Run harvest operation on the source passed as argument.
  $items['dkan-harvest'] = array(
    'aliases' => array('dkan-h', 'dkan-harvest-run', 'dkan-hr'),
    'description' => 'Run a harvest (cache and migration) operation on a single source or all the available sources if no argument is provided.',
    'callback' => 'dkan_harvest_drush_command_harvest',
    'arguments' => array(
      'source_machine_name' =>
      'The source machine name to run the harvest operation on',
    ),
    'options' => array(
      'skiphash' => 'Skip hash checking and update all the datasets available in the source.',
      'limit' => 'Limit on the length of each migration. Check migrate doc for more about this option.',
      'instrument' => 'Capture performance information (timer, memory, or all). Check migrate doc for more about this option.',
      'idlist' => 'A comma delimited list of ids to import or rollback. Check migrate doc for more about this option.',
    ),
    'drupal dependencies' => array('dkan_harvest'),
  );

  // Run harvest cache operation on the source passed as argument.
  $items['dkan-harvest-cache'] = array(
    'aliases' => array('dkan-hc', 'dkan-cache-harvested-data', 'dkan-chd'),
    'description' => 'Run a cache operation on a single source or all the available sources if the argument is not provided.',
    'callback' => 'dkan_harvest_drush_command_cache',
    'arguments' => array(
      'source_machine_name' =>
      'The source machine name to run the cache operation on',
    ),
    'drupal dependencies' => array('dkan_harvest'),
  );

  // Run harvest migrate operation on the source passed as argument.
  $items['dkan-harvest-migrate'] = array(
    'aliases' => array('dkan-hm', 'dkan-migrate-cached-data', 'dkan-mcd'),
    'description' => 'Run a migrate operation on a single source or all the available sources if the argument is not provided.',
    'callback' => 'dkan_harvest_drush_command_migrate',
    'arguments' => array(
      'source_machine_name' => 'The source machine name to run the migration on',
    ),
    'options' => array(
      'skiphash' => 'Skip hash checking and update all the datasets available in the source.',
      'limit' => 'Limit on the length of each migration. Check migrate doc for more about this option.',
      'instrument' => 'Capture performance information (timer, memory, or all). Check migrate doc for more about this option.',
      'idlist' => 'A comma delimited list of ids to import or rollback. Check migrate doc for more about this option.',
    ),
    'drupal dependencies' => array('dkan_harvest'),
  );

  // Run harvest rollback operation on the source passed as argument.
  $items['dkan-harvest-rollback'] = array(
    'aliases' => array('dkan-hr'),
    'description' => 'Run a harvest rollback on a single source.',
    'callback' => 'dkan_harvest_drush_command_rollback',
    'arguments' => array(
      'source_machine_name' => 'The source machine name to run the rollback on.',
    ),
    'options' => array(
      'limit' => 'Limit on the length of each migration. Check migrate doc for more about this option.',
      'instrument' => 'Capture performance information (timer, memory, or all). Check migrate doc for more about this option.',
      'idlist' => 'A comma delimited list of ids to import or rollback. Check migrate doc for more about this option.',
    ),
    'drupal dependencies' => array('dkan_harvest'),
  );

  // Displays the list of available sources.
  $items['dkan-harvest-status'] = array(
    'aliases' => array('dkan-hs'),
    'description' => 'Status Harvest Sources available',
    'callback' => 'dkan_harvest_drush_command_status',
    'drupal dependencies' => array('dkan_harvest'),
  );

  // Deregister the migration associated with a source.
  $items['dkan-harvest-deregister'] = array(
    'aliases' => array('dkan-hdr'),
    'description' => 'Deregister the migration associated with the specified source',
    'callback' => 'dkan_harvest_drush_command_deregister',
    'arguments' => array(
      'source_machine_name' => 'The source machine name',
    ),
    'options' => array(),
    'drupal dependencies' => array('dkan_harvest'),
  );

  /* NOT IMPLEMENTED YET */
  $items['orphaned-resources-count'] = array(
    'description' => "Count resources that are not linked to datasets.",
    'drupal dependencies' => array('dkan_dataset'),
    'aliases' => array('dkan-or-count', 'orphaned-resources'),
  );

  /* NOT IMPLEMENTED YET */
  $items['orphaned-resources-delete'] = array(
    'description' => "Count resources that are not linked to datasets.",
    'drupal dependencies' => array('dkan_dataset'),
    'aliases' => array('dkan-or-delete', 'orphaned-resources-delete'),
  );

  return $items;
}

/**
 * Run a harvest operation (cache + migrate).
 *
 * Run harvest on all the harvest source
 * available or the source passed as arguments.
 *
 * @param string $source_machine_name
 *
 *   Optional harvest source machine name.
 */
function dkan_harvest_drush_command_harvest($source_machine_name = NULL) {
  dkan_harvest_drush_command_migrate($source_machine_name, FALSE);
}

/**
 * Run harvest cache operation on the source passed as argument.
 *
 * @param string $source_machine_name
 *
 *   Source machine name to cache.
 */
function dkan_harvest_drush_command_cache($source_machine_name = NULL) {

  $sources = array();

  if (isset($source_machine_name)) {
    $source = new HarvestSource($source_machine_name);
    if (!$source) {
      drush_log("No source with machine name " . $source_machine_name . " found.", 'error');
      return FALSE;
    }
    $sources[] = $source;
  }
  else {
    drush_log("Running the harvest caching on all the available sources.", 'notice');
    $sources = dkan_harvest_sources_definition();    
  }

  foreach($sources as $source) {
    // Config batch process.
    $batch = dkan_harvest_generate_cache_batch($source);
    // Setup batch process.
    $batch['progressive'] = FALSE;
    batch_set($batch);
    drush_backend_batch_process();
  }
}

/**
 * Run harvest migrate operation on the source passed as argument.
 *
 * @param string $source_machine_name
 *
 *   Source machine name to migrate.
 */
function dkan_harvest_drush_command_migrate($source_machine_name = NULL, $perform_cache = FALSE) {
  $supported_options = array('limit', 'skiphash', 'instrument', 'idlist');
  $options = array();

  // Gather harvest migration options. If parsing the arguments fails just relay
  // on the parsing callback to oouput an appropriate message and stop.
  $option_parsed = dkan_harvest_get_options($options, $supported_options);
  if (!$option_parsed) {
    return;
  }

  $sources = array();
  if (isset($source_machine_name)) {
    $source = dkan_harvest_get_harvestsource($source_machine_name);

    if (!$source) {
      return FALSE;
    }

    drush_log("Running the harvest migration on " . $source_machine_name, 'notice');
    $sources[] = $source;
  }
  else {
    drush_log("Running the harvest migration on all the available sources.", 'notice');
    $sources = dkan_harvest_sources_definition();
  }

  foreach($sources as $source) {
    // Config batch process.
    $batch = dkan_harvest_generate_migrate_batch($source, $options, $perform_cache);
    // Setup batch process.
    $batch['progressive'] = FALSE;
    batch_set($batch);
    drush_backend_batch_process();
  }

  // Print instrument info if set.
  global $_migrate_track_memory, $_migrate_track_timer;
  if ($_migrate_track_memory) {
    drush_migrate_print_memory();
  }

  if ($_migrate_track_timer && !drush_get_context('DRUSH_DEBUG')) {
    drush_print_timers();
  }
}

/**
 * Run harvest migrate operation on the source passed as argument.
 *
 * @param string $source_machine_name
 *
 *   Source machine name to migrate.
 */
function dkan_harvest_drush_command_rollback($source_machine_name) {
  $options = array();
  $supported_options = array('limit', 'instrument', 'idlist');

  // Gather harvest migration options. If parsing the arguments fails just relay
  // on the parsing callback to oouput an appropriate message and stop.
  $option_parsed = dkan_harvest_get_options($options, $supported_options);
  if (!$option_parsed) {
    return;
  }

  $sources = array();

  if (!isset($source_machine_name)) {
    return drush_set_error(
      dt("No source machine_name provided.")
    );
  }
  else {
    $source = dkan_harvest_get_harvestsource($source_machine_name);

    if (!$source) {
      return FALSE;
    }

    $message = t("Running the harvest rollback on %source_machine_name",
      array('%source_machine_name' => $source_machine_name));
    drush_log($message, 'notice');
    $sources[] = $source;
  }

  dkan_harvest_rollback_sources($sources, $options);
}

/**
 * Relevent Info about the available Harvest Sources.
 */
function dkan_harvest_drush_command_status() {
  $harvest_sources = dkan_harvest_sources_definition();

  $rows = array();

  $rows[] = array(
    'machine name' => 'Machine Name',
    'label' => 'Label',
    'uri' => 'URI',
    'type' => 'Type',
  );

  // Build table array.
  foreach ($harvest_sources as $harvest_source) {
    $rows[] = array(
      'machine name' => $harvest_source->machineName,
      'label' => $harvest_source->label,
      'uri' => $harvest_source->uri,
      'type' => $harvest_source->type->label,
    );
  }

  drush_print_table($rows, TRUE);
}

/**
 * Drop the harvest migrate information about a harvest source.
 *
 * @param string $source_machine_name
 *
 *   Source machine name to migrate.
 */
function dkan_harvest_drush_command_deregister($source_machine_name = NULL) {
  // Gather harvest migration options.
  $options = array();
  $supported_options = array();

  // Gather harvest migration options. If parsing the arguments fails just relay
  // on the parsing callback to oouput an appropriate message and stop.
  $option_parsed = dkan_harvest_get_options($options, $supported_options);
  if (!$option_parsed) {
    return;
  }

  if (!isset($source_machine_name)) {
    $message = t("A harvest source needs to be specified.");
    drush_log($message, 'error');
    return FALSE;
  }

  $harvest_source = new HarvestSource($source_machine_name);

  if (!$harvest_source) {
    drush_log(t("Harvest Source not found!"), 'error');
    return FALSE;
  }

  dkan_harvest_deregister_sources(array($harvest_source), $options);
}

/**
 * Wrapper for options parsing callbacks.
 */
function dkan_harvest_get_options(&$options, $lookup = array()) {
  foreach ($lookup as $option) {
    $options = call_user_func('dkan_harvest_get_option_' . $option, $options);
    if ($options === FALSE) {
      // Some argument parsing failed. escalate.
      return drush_set_error(NULL, dt('unsupported or invalid use of the !option option.',
      array('!option' => $option)));
    }
  }
  return TRUE;
}

/**
 * Helper function to parse the 'limit' option.
 *
 * This is taken from migrate.drush.inc.
 */
function dkan_harvest_get_option_limit($options) {
  $limit = drush_get_option('limit');
  if ($limit) {
    $parts = explode(' ', $limit);
    $options['limit']['value'] = $parts[0];
    // Default unit.
    if (!isset($parts[1])) {
      $parts[1] = 'items';
    }
    $options['limit']['unit'] = $parts[1];
    // Validation.
    if (!in_array($options['limit']['unit'],
      array('seconds', 'second', 'items', 'item'))) {
      return drush_set_error(NULL, dt("Invalid limit unit '!unit'",
      array('!unit' => $options['limit']['unit'])));
    }
  }
  return $options;
}

/**
 * Helper function to parse the 'instrument' option.
 *
 * This is taken from migrate.drush.inc.
 */
function dkan_harvest_get_option_instrument($options) {
  $instrument = drush_get_option('instrument');
  global $_migrate_track_memory, $_migrate_track_timer;
  switch ($instrument) {
    case 'timer':
      $_migrate_track_timer = TRUE;
      break;

    case 'memory':
      $_migrate_track_memory = TRUE;
      break;

    case 'all':
      $_migrate_track_timer = TRUE;
      $_migrate_track_memory = TRUE;
      break;
  }
  return $options;
}

/**
 * Helper function to parse the 'skiphash' option.
 */
function dkan_harvest_get_option_skiphash($options) {
  $options['skiphash'] = drush_get_option('skiphash', FALSE);
  return $options;
}

/**
 * Helper function to parse the 'idlist' option.
 *
 * This is taken from migrate.drush.inc.
 */
function dkan_harvest_get_option_idlist($options) {
  if ($idlist = drush_get_option('idlist', FALSE)) {
    $options['idlist'] = $idlist;
  }
  return $options;
}

/**
 * Return the HarvestSource from machine name.
 */
function dkan_harvest_get_harvestsource($source_machine_name) {
  $source = NULL;

  try {
    $source = new HarvestSource($source_machine_name);
  }
  catch (Exception $exception) {
    $message = dt("No source with machine name %source_machine_name found.",
      array(
        '%source_machine_name' => $source_machine_name,
      ));
    return drush_set_error(NULL, $message);
  }

  return $source;
}

/**
 * Get a count of orphaned resources.
 */
function drush_dkan_harvest_orphaned_resources_count() {
  $result = db_query("SELECT count(node.nid) AS nid FROM
  {node} node
  LEFT JOIN {field_data_field_dataset_ref} field_data_field_dataset_ref ON node.nid = field_data_field_dataset_ref.entity_id AND (field_data_field_dataset_ref.entity_type = 'node' AND field_data_field_dataset_ref.deleted = '0')
  WHERE (( (node.status = '1') AND (node.type IN  ('resource')) AND (field_data_field_dataset_ref.field_dataset_ref_target_id IS NULL ) ))
  ")->fetchAll();
  drush_print(dt('There are !count orphaned resources', array('!count' => $result[0]->nid)));
  return TRUE;
}

/**
 * Delete orphaned resources.
 */
function drush_dkan_harvest_orphaned_resources_delete() {
  $results = db_query("SELECT node.nid AS nid FROM
  {node} node
  LEFT JOIN {field_data_field_dataset_ref} field_data_field_dataset_ref ON node.nid = field_data_field_dataset_ref.entity_id AND (field_data_field_dataset_ref.entity_type = 'node' AND field_data_field_dataset_ref.deleted = '0')
  WHERE (( (node.status = '1') AND (node.type IN  ('resource')) AND (field_data_field_dataset_ref.field_dataset_ref_target_id IS NULL ) ))
  ")->fetchAll();
  foreach ($results as $result) {
    drush_print(dt('Deleting nid !nid', array('!nid' => $result->nid)));
    node_delete($result->nid);
  }
  return TRUE;
}
