<?php

/**
 * @file
 *   Schema drush commands.
 */

/**
 * Implementation of hook_drush_command().
 */
function schema_drush_command() {
  $items = array();

  // the key in the $items array is the name of the command.
  $items['schema-inspect'] = array(
    'description' => "Show the Drupal schema definition for table(s)",
    'arguments' => array(
      'tables' => 'A comma delimited list of table names',
    ),
    'options' => array(
      'connection' => 'An alternate connection key to inspect',
    ),
  );

  $items['schema-compare'] = array(
    'description' => "List tables that match, mismatch, are missing or are extra",
    'arguments' => array(
      'type' => 'The type of tables to list, one of "match", "mismatch", "missing" or "extra".',
    ),
    'examples' => array(
      'drush schema-compare match' => 'List all tables in the database whose schemas match their schema definitions.',
      'drush schema-compare mismatch' => 'List all tables in the database whose schemas do not match their schema definitions.',
      'drush schema-compare missing' => 'List all missing tables from the database existing in schema definitions.',
      'drush schema-compare extra' => 'List all tables in the database whose schema definitions do not exist.',
    ),
  );
  return $items;
}

/**
 * Implementation of hook_drush_help().
 */
function schema_drush_help($section) {
  switch ($section) {
    case 'drush:schema-inspect':
      return dt("Show the Drupal schema definition for table(s).");
    case 'drush:schema-compare':
      return dt("List tables that match, mismatch, are missing or are extra.");
  }
}

/**
 * Implements drush_COMMANDFILE_COMMANDNAME().
 */
function drush_schema_inspect() {
  $args = func_get_args();
  $names = explode(',', $args[0]);
  $connection = drush_get_option('connection');
  if (!$connection) {
    $connection = 'default';
  }
  foreach ($names as $name) {
    if ($table = schema_dbobject($connection)->inspect(NULL, $name)) {
      $output = schema_phpprint_table($name, $table[$name]);
      drush_print($output);
    }
    else {
      drush_set_error(dt('Mising table: @table', array('@table' => $name)));
    }
  }
}

/*
 * Implementats drush_hook_COMMAND_validate().
 */
function drush_schema_compare_validate($type) {

  // Define the possible table types.
  $types = array(
    'match',
    'mismatch',
    'missing',
    'extra',
  );

  // Ensure that we were sent a valid one.
  if (!in_array($type, $types)) {
    return drush_set_error(
      'ERROR_TYPE_INVALID',
      dt('You must specify a valid table type.')
    );
  }
}

/**
 * Implements drush_COMMANDFILE_COMMANDNAME().
 */
function drush_schema_compare($type) {

  // Get the defined database schema.
  $schema = drupal_get_schema(NULL, TRUE);

  // Compare it with the actual database schema.
  $comparison = schema_compare_schemas($schema);

  // Change the user-friendly type ID to the machine-readable one.
  switch ($type) {
    case 'match':
      $type = 'same';
    break;
    case 'mismatch':
      $type = 'different';
    break;
    default:
      // Type ID is the same; it doesn't need to be changed.
    break;
  }

  // Different classes of tables must be handled differently.
  if (isset($comparison[$type])) {
    switch ($type) {

      // These table types have modules associated with them.
      // Display the module name in front.
      case 'same':
      case 'different':
      case 'missing':
        foreach ($comparison[$type] as $module => $tables) {
          foreach ($tables as $table => $data) {
            drush_print($module . ": " . $table);
          }
        }
      break;

      // These table types have no modules associated with them.
      // Display the table names only.
      case 'extra':
        foreach ($comparison[$type] as $table) {
          drush_print($table);
        }
      break;
    }
  }
}

