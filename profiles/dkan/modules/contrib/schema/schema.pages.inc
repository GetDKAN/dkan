<?php

/**
 * @file
 * Page callbacks for the Schema module.
 */

/**
 * "Compare" menu callback.
 *
 * This function just massages the data returned by shema_compare_schemas()
 * into HTML.
 *
 * @todo Convert this into a true page callback. Somehow fieldsets do not work
 * without being used with drupal_get_form().
 */
function schema_compare() {
  $build = array();

  $states = array(
    'same' => t('Match'),
    'different' => t('Mismatch'),
    'missing' => t('Missing'),
  );
  $descs = array(
    'same' => t('Tables for which the schema and database agree.'),
    'different' => t('Tables for which the schema and database are different.'),
    'missing' => t('Tables in the schema that are not present in the database.'),
  );

  $schema = drupal_get_schema(NULL, TRUE);
  $info = schema_compare_schemas($schema);

  // The info array is keyed by state (same/different/missing/extra/warn). For missing,
  // the value is a simple array of table names. For warn, it is a simple array of warnings.
  // Get those out of the way first
  if (isset($info['warn'])) {
    foreach ($info['warn'] as $message) {
      drupal_set_message($message, 'warning');
    }
    unset($info['warn']);
  }

  $build['extra'] = array(
    '#type' => 'fieldset',
    '#title' => t('Extra (@count)', array('@count' => isset($info['extra']) ? count($info['extra']) : 0)),
    '#description' => t('Tables in the database that are not present in the schema. This indicates previously installed modules that are disabled but not un-installed or modules that do not use the Schema API.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#weight' => 50,
  );
  $build['extra']['tablelist'] = array(
    '#theme' => 'item_list',
    '#items' => isset($info['extra']) ? $info['extra'] : array(),
  );
  unset($info['extra']);

  // For the other states, the value is an array keyed by module name. Each value
  // in that array is an array keyed by tablename, and each of those values is an
  // array containing 'status' (same as the state), an array of reasons, and an array of notes.
  $weight = 0;
  foreach ($info as $state => $modules) {
    // We'll fill in the fieldset title below, once we have the counts
    $build[$state] = array(
      '#type' => 'fieldset',
      '#description' => $descs[$state],
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#weight' => $weight++,
    );
    $counts[$state] = 0;

    foreach ($modules as $module => $tables) {
      $counts[$state] += count($tables);
      $build[$state][$module] = array(
        '#type' => 'fieldset',
        '#title' => $module,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );
      switch ($state) {
        case 'same':
        case 'missing':
          $build[$state][$module]['tablelist'] = array(
            '#theme' => 'item_list',
            '#items' => array_keys($tables),
          );
          break;

        case 'different':
          $items = array();
          foreach ($tables as $name => $stuff) {
            $build[$state][$module][$name] = array(
              '#type' => 'fieldset',
              '#collapsible' => TRUE,
              '#collapsed' => TRUE,
              '#title' => $name,
            );
            $build[$state][$module][$name]['reasons'] = array(
              '#theme' => 'item_list',
              '#items' => array_merge($tables[$name]['reasons'], $tables[$name]['notes']),
            );
          }
          break;
      }
    }
  }

  // Fill in counts in titles
  foreach ($states as $state => $description) {
    $build[$state]['#title'] = t('@state (@count)', array('@state' => $states[$state], '@count' => isset($counts[$state]) ? $counts[$state] : 0));
  }

  return $build;
}


/**
 * "Describe" menu callback.
 *
 * @todo Convert this into a true page callback. Somehow fieldsets do not work
 * without being used with drupal_get_form().
 */
function schema_describe() {
  $build = array();

  $schema = drupal_get_schema(NULL, TRUE);
  ksort($schema);
  $row_hdrs = array(t('Name'), t('Type[:Size]'), t('Null?'), t('Default'));

  $default_table_description = t('TODO: please describe this table!');
  $default_field_description = t('TODO: please describe this field!');
  foreach ($schema as $t_name => $t_spec) {
    $rows = array();
    foreach ($t_spec['fields'] as $c_name => $c_spec) {
      $row = array();
      $row[] = $c_name;
      $type = $c_spec['type'];
      if (!empty($c_spec['length'])) {
        $type .= '(' . $c_spec['length'] . ')';
      }
      if (!empty($c_spec['scale']) && !empty($c_spec['precision'])) {
        $type .= '(' . $c_spec['precision'] . ', ' . $c_spec['scale'] . ' )';
      }
      if (!empty($c_spec['size']) && $c_spec['size'] != 'normal') {
        $type .= ':' . $c_spec['size'];
      }
      if ($c_spec['type'] == 'int' && !empty($c_spec['unsigned'])) {
        $type .= ', unsigned';
      }
      $row[] = $type;
      $row[] = !empty($c_spec['not null']) ? 'NO' : 'YES';
      $row[] = isset($c_spec['default']) ? (is_string($c_spec['default']) ? '\'' . $c_spec['default'] . '\'' : $c_spec['default']) : '';
      $rows[] = $row;
      if (!empty($c_spec['description']) && $c_spec['description'] != $default_field_description) {
        $desc = _schema_process_description($c_spec['description']);
        $rows[] = array(array('colspan' => count($row_hdrs), 'data' => $desc));
      }
      else {
        drupal_set_message(_schema_process_description(t('Field {!table}.@field has no description.', array('!table' => $t_name, '@field' => $c_name))), 'warning');
      }
    }

    if (empty($t_spec['description']) || $t_spec['description'] == $default_table_description) {
      drupal_set_message(_schema_process_description(t('Table {!table} has no description.', array('!table' => $t_name))), 'warning');
    }

    $build[$t_name] = array(
      '#type' => 'fieldset',
      '#title' => t('@table (@module module)',
        array('@table' => $t_name, '@module' => isset($t_spec['module']) ? $t_spec['module'] : '')),
      '#description' => !empty($t_spec['description']) ? _schema_process_description($t_spec['description']) : '',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#attributes' => array('id' => 'table-' . $t_name),
    );
    $build[$t_name]['content'] = array(
      '#theme' => 'table',
      '#header' => $row_hdrs,
      '#rows' => $rows,
    );
  }

  return $build;
}

/**
 * "Inspect" menu callback.
 *
 * @todo Convert this into a true page callback. Somehow fieldsets do not work
 * without being used with drupal_get_form().
 */
function schema_inspect() {
  $build = array();

  $mods = module_list();
  sort($mods);
  $mods = array_flip($mods);
  $schema = drupal_get_schema(NULL, TRUE);
  $inspect = schema_dbobject()->inspect();
  foreach ($inspect as $name => $table) {
    $module = isset($schema[$name]['module']) ? $schema[$name]['module'] : 'Unknown';
    if (!isset($build[$module])) {
      $build[$module] = array(
        '#type' => 'fieldset',
        '#access' => TRUE,
        '#title' => check_plain($module),
        '#collapsible' => TRUE,
        '#collapsed' => ($module != 'Unknown'),
        '#weight' => ($module == 'Unknown' ? 0 : $mods[$module]+1),
      );
    }
    $build[$module][$name] = array(
      '#type' => 'markup',
      '#markup' => '<textarea style="width:100%" rows="10">' . check_plain(schema_phpprint_table($name, $table)) . '</textarea>',
    );
  }

  return $build;
}


/**
 * "SQL" menu callback.
 */
function schema_sql($engine = NULL) {
  $schema = drupal_get_schema(NULL, TRUE);
  $connection = Database::getConnection();
  $sql = '';
  foreach ($schema as $name => $table) {
    if (substr($name, 0, 1) == '#') {
      continue;
    }
    if ($engine) {
      $stmts = call_user_func('schema_' . $engine . '_create_table_sql', $table);
    }
    else {
      $stmts = schema_dbobject()->getCreateTableSql($name, $table);
    }

    $sql .= implode(";\n", $stmts) . ";\n\n";
  }

  return "<textarea style=\"width:100%\" rows=\"30\">$sql</textarea>";
}

/**
 * "Show" menu callback.
 *
 * Displays drupal schema as php code, so you can reuse it
 * as you need.
 */
function schema_show() {
  $schema = drupal_get_schema(NULL, TRUE);
  $show = var_export($schema, 1);

  return "<textarea style=\"width:100%\" rows=\"30\">$show</textarea>";
}

function _schema_process_description($desc) {
  return preg_replace('@{([a-z_]+)}@i', '<a href="#" onclick="Drupal.toggleFieldset($(\'#table-$1\')[0]); return false;">$1</a>', $desc);
}
