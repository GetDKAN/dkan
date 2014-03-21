<?php
/**
 * @file
 * Views hooks and utility functions.
 */

/**
 * Implements hook_views_data().
 *
 * Dynamically create views integration for any table Data manages.
 */
function data_views_data() {
  $data = array();

  $tables = data_get_all_tables();

  foreach ($tables as $table) {

    // Get schema and check wether there are field definitions.
    $schema = $table->get('table_schema');
    $meta = $table->get('meta');
    if (!isset($schema['fields'])) {
      continue;
    }

    $table_data = array();
    $table_data['table'] = array(
      'group' => check_plain($table->get('title')),
    );
    foreach ($schema['fields'] as $field_name => $field) {
      // If there is no label, generate one from field name.
      $title = empty($meta['fields'][$field_name]['label']) ? data_natural_name($field_name) : $meta['fields'][$field_name]['label'];

      $table_data[$field_name] = array(
        'title' => $title,
        'help' => $title,
        'field' => data_get_table_field_views_data('field', $table, $field_name),
        'filter' => data_get_table_field_views_data('filter', $table, $field_name),
        'argument' => data_get_table_field_views_data('argument', $table, $field_name),
        'sort' => data_get_table_field_views_data('sort', $table, $field_name),
      );
    }

    // Tables with a primary key are base tables.
    if (isset($schema['primary key']) && count($schema['primary key']) >= 1) {
      $table_data['table']['base'] = array(
        'field' => current($schema['primary key']),
        'title' => check_plain($table->get('title')),
        'help' => t('Data table'),
        'weight' => 10,
      );
    }

    // Add join information.
    if (isset($meta['join']) && is_array($meta['join'])) {
      $table_data['table']['join'] = array();
      foreach ($meta['join'] as $left_table => $join) {
        // @todo: See if left table has other tables it is linked to and link
        // all the way to the leftmost table.
        $table_data['table']['join'][$left_table] = array(
          'left_field' => $join['left_field'],
          'field' => $join['field'],
          'type' => $join['inner_join'] ? 'INNER' : 'LEFT',
        );
        // Add an explicit relationship for every join added.
        $table_data[$join['field']]['relationship'] = array(
          'label' => "{$table->get('name')}.{$join['field']} -> {$left_table}.{$join['left_field']}",
          'base' => $left_table,
          'base field' => $join['left_field'],
        );
      }
    }

    $data[$table->get('name')] = $table_data;
  }

  return $data;
}

/**
 * Get the hook_views_data() declaration for a field and handler type.
 *
 * Helper for our hook_views_data().
 *
 * @param $type
 *   The view handler type ('field', 'filter', 'sort', 'argument').
 * @param $table
 *   A data table object.
 * @param $field_name
 *   String: name of the field.
 * @param $default
 *   Boolean for whether to return the default handler for the given
 *   db column type.
 *
 * @return
 *   An array for a hook_views_data() handler declaration.
 *
 * @see data_views_data()
 */
function data_get_table_field_views_data($type, $table, $field_name, $default = FALSE) {
  $schema = $table->get('table_schema');
  $meta = $table->get('meta');

  // If there is no label, generate one from field name.
  $title = empty($meta['fields'][$field_name]['label']) ? data_natural_name($field_name) : $meta['fields'][$field_name]['label'];

  switch ($type) {
    case 'field':
      $views_data = array(
        'handler' => data_get_views_handler('field', $table, $field_name),
        'help' => $title,
        'click sortable' => TRUE,
      );
      break;

    case 'filter':
      $views_data = array(
        'handler' => data_get_views_handler('filter', $table, $field_name),
        'allow empty' => TRUE,
        'help' => t('Filter on %field', array('%field' => $title)),
      );
      break;

    case 'argument':
      $views_data = array(
        'handler' => data_get_views_handler('argument', $table, $field_name),
        'help' => $title,
      );
      break;

    case 'sort':
      $views_data = array(
        'handler' => data_get_views_handler('sort', $table, $field_name),
        'help' => t('Sort by %field', array('%field' => $title)),
      );
      break;
  }

  if (isset($meta['fields'][$field_name]['date'])) {
    if (!empty($meta['fields'][$field_name]['date']['sql_type'])) {
      $views_data['is date'] = TRUE;
    }
  }

  return $views_data;
}

/**
 * Return all available field handlers.
 *
 * @param $type
 *   Optional: the view handler type whose options should be provided
 *   ('field', 'filter', 'sort', 'argument'). If omitted, a full array
 *   keyed on type is returned.
 * @param $reset
 *   Boolean to reset the static cache.
 *
 * @return
 *   An array suitable for use as options in a FormAPI element.
 */
function data_get_views_handler_options($type = NULL, $reset = FALSE) {
  static $handlers;
  if (!isset($handlers) || $reset) {
    $handlers = array();
    module_load_include('inc', 'views', 'includes/base');
    module_load_include('inc', 'views', 'includes/handlers');
    module_load_include('inc', 'views', 'includes/cache');

    // The types of handler we are interested in.
    // If we wanted everything, we'd get it from view::display_objects().
    $handler_types = array(
      'field',
      'filter',
      'argument',
      'sort',
      'relationship',
    );

    // Get all of Views' data, wherein every field defines its handler.
    $views_data = _views_fetch_data();
    $handlers = array_fill_keys($handler_types, array());
    foreach ($views_data as $table => $table_definition) {
      foreach ($table_definition as $field => $field_definition) {
        // Skip the table definition itself.
        if ($field == 'table') {
          continue;
        }
        foreach ($handler_types as $handler_type) {
          if (isset($field_definition[$handler_type]['handler'])) {
            $handler = $field_definition[$handler_type]['handler'];
            $handlers[$handler_type][$handler] = $handler;
            $current_handler = $handler;
            // Work up the class's parentage, as there are many handler classes
            // not actually used by any views data fields.
            $finished = FALSE;
            while (!$finished) {
              // Try to find the parent class name of the current Views handler.
              $parent_handler = get_parent_class($current_handler);
              switch ($parent_handler) {
                case FALSE:
                  // Could not auto-load and/or find the parent handler in this scope.
                  watchdog('data', 'Unable to detect class ancestry of Views handler %handler for %field_name.', array(
                    '%handler' => $current_handler,
                    '%field_name' => $table . '.' . $field,
                  ));
                  // Fall through to finish climbing this class hierarchy.
                case 'views_handler':
                  // Reached the root views_handler class or cannot continue up the
                  // class hierarchy.
                  $finished = TRUE;
                  break;
                default:
                  // Add the class. This is going to be redundant in many cases
                  // as we'll have either already done this or be about to find
                  // it in the data, but this function is only called on an admin
                  // page so performance is not an issue.
                  $handlers[$handler_type][$parent_handler] = $current_handler = $parent_handler;
                  break;
              }
            }
          }
        }
      }
    }

    // Allow other modules to alter the list of available handlers.
    drupal_alter('data_views_handlers', $handlers);
    // Sort the final arrays.
    foreach ($handlers as $handler_type => &$data) {
      ksort($data);
    }
  }
  return isset($type) && isset($handlers[$type]) ? $handlers[$type] : $handlers;
}

/**
 * Implements hook_data_views_handlers_alter().
 *
 * We need to add our own handler here because nothing in hook_views_data()
 * declares it makes use of it.
 */
function data_data_views_handlers_alter(&$handlers) {
  $handlers['field']['views_handler_field_data_markup'] = 'views_handler_field_data_markup';
}

/**
 * Get the handler (class name) for a specified data table field.
 *
 * @param $type
 *   The view handler type ('field', 'filter', 'sort', 'argument').
 * @param $table
 *   A data table object.
 * @param $field_name
 *   String: name of the field.
 * @param $default
 *   Boolean for whether to return the default handler for the given
 *   db column type.
 *
 * @return
 *   String: A views handler class name.
 */
function data_get_views_handler($type, $table, $field_name, $default = FALSE) {
  // Return the handler's custom setting if available
  if (!$default) {
    $meta = $table->get('meta');
    if (isset($meta['fields'][$field_name]["views_{$type}_handler"])) {
      return $meta['fields'][$field_name]["views_{$type}_handler"];
    }
  }

  $schema = $table->get('table_schema');
  switch ($type) {

    case 'field':
      switch ($schema['fields'][$field_name]['type']) {
        case 'int':
        case 'float':
        case 'serial':
        case 'numeric':
          return 'views_handler_field_numeric';
        case 'datetime':
          return 'views_handler_field_date';
      }
      return 'views_handler_field';

    case 'filter':
      switch ($schema['fields'][$field_name]['type']) {
        case 'float':
        case 'numeric':
          return 'views_handler_filter_numeric';
        case 'int':
        case 'serial':
          return 'views_handler_filter_numeric';
        case 'datetime':
          return 'views_handler_filter_date';
      }
      return 'views_handler_filter_string';

    case 'argument':
      switch ($schema['fields'][$field_name]['type']) {
        case 'int':
        case 'float':
        case 'serial':
        case 'numeric':
          return 'views_handler_argument_numeric';
        case 'datetime':
          return 'views_handler_argument_date';
        case 'varchar':
          return 'views_handler_argument_string';
      }
      return 'views_handler_argument';

    case 'sort':
      switch ($schema['fields'][$field_name]['type']) {
        case 'datetime':
          return 'views_handler_sort_date';
      }
      return 'views_handler_sort';

  }
}
