<?php

/**
 * Page callback that shows an overview of defined servers and indexes.
 */
function search_api_admin_overview() {
  $base_path = drupal_get_path('module', 'search_api') . '/';
  drupal_add_css($base_path . 'search_api.admin.css');
  drupal_add_js($base_path . 'search_api.admin.js');

  $servers = search_api_server_load_multiple(FALSE);
  $indexes = array();
  // When any entity was not normally created in the database, then show status for all.
  $show_config_status = FALSE;
  foreach (search_api_index_load_multiple(FALSE) as $index) {
    $indexes[$index->server][$index->machine_name] = $index;
    if (!$show_config_status && $index->status != ENTITY_CUSTOM) {
      $show_config_status = TRUE;
    }
  }
  // Show disabled servers after enabled ones.
  foreach ($servers as $id => $server) {
    if (!$server->enabled) {
      unset($servers[$id]);
      $servers[$id] = $server;
    }
    if (!$show_config_status && $server->status != ENTITY_CUSTOM) {
      $show_config_status = TRUE;
    }
  }

  $rows = array();
  $t_server = array('data' => t('Server'), 'colspan' => 2);
  $t_index = t('Index');
  $t_enabled['data'] = array(
    '#theme' => 'image',
    '#path' => $base_path . 'enabled.png',
    '#alt' => t('enabled'),
    '#title' => t('enabled'),
  );
  $t_enabled['class'] = array('search-api-status');
  $t_disabled['data'] = array(
    '#theme' => 'image',
    '#path' => $base_path . 'disabled.png',
    '#alt' => t('disabled'),
    '#title' => t('disabled'),
  );
  $t_disabled['class'] = array('search-api-status');
  $t_enable = t('enable');
  $t_disable = t('disable');
  $t_edit = t('edit');
  $pre_server = 'admin/config/search/search_api/server';
  $pre_index = 'admin/config/search/search_api/index';
  $enable = '/enable';
  $disable = '/disable';
  $edit = '/edit';
  $edit_link_options['attributes']['class'][] = 'search-api-edit-menu-toggle';
  foreach ($servers as $server) {
    $url = $pre_server . '/' . $server->machine_name;
    $row = array();
    $row[] = $server->enabled ? $t_enabled : $t_disabled;
    if ($show_config_status) {
      $row[] = theme('entity_status', array('status' => $server->status));
    }
    $row[] = $t_server;
    $row[] = l($server->name, $url);
    $row[] = $server->enabled ? l($t_disable, $url . $disable) : l($t_enable, $url . $enable, array('query' => array('token' => drupal_get_token($server->machine_name))));
    $row[] = l($t_edit, $url . $edit);
    $row[] = _search_api_admin_delete_link($server);
    $rows[] = $row;
    if (!empty($indexes[$server->machine_name])) {
      foreach ($indexes[$server->machine_name] as $index) {
        $url = $pre_index . '/' . $index->machine_name;
        $row = array();
        $row[] = $index->enabled ? $t_enabled : $t_disabled;
        if ($show_config_status) {
          $row[] = theme('entity_status', array('status' => $index->status));
        }
        $row[] = '';
        $row[] = $t_index;
        $row[] = l($index->name, $url);
        $row[] = $index->enabled
            ? l($t_disable, $url . $disable)
            : ($server->enabled ? l($t_enable, $url . $enable, array('query' => array('token' => drupal_get_token($index->machine_name)))) : '');
        $row[] = l($t_edit, $url . $edit, $edit_link_options) .
            '<div class="search-api-edit-menu collapsed">' .
            theme('links', array('links' => menu_contextual_links('search-api-index', $pre_index, array($index->machine_name)))) .
            '</div>';
        $row[] = _search_api_admin_delete_link($index);
        $rows[] = $row;
      }
    }
  }
  if (!empty($indexes[''])) {
    foreach ($indexes[''] as $index) {
      $url = $pre_index . '/' . $index->machine_name;
      $row = array();
      $row[] = $t_disabled;
      if ($show_config_status) {
        $row[] = theme('entity_status', array('status' => $index->status));
      }
      $row[] = array('data' => $t_index, 'colspan' => 2);
      $row[] = l($index->name, $url);
      $row[] = '';
      $row[] = l($t_edit, $url . $edit, $edit_link_options) .
            '<div class="search-api-edit-menu collapsed">' .
            theme('links', array('links' => menu_contextual_links('search-api-index', $pre_index, array($index->machine_name)))) .
            '</div>';
      $row[] = _search_api_admin_delete_link($index);
      $rows[] = $row;
    }
  }

  $header = array();
  $header[] = t('Status');
  if ($show_config_status) {
    $header[] = t('Configuration');
  }
  $header[] = array('data' => t('Type'), 'colspan' => 2);
  $header[] = t('Name');
  $header[] = array('data' => t('Operations'), 'colspan' => 3);

  return array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('There are no search servers or indexes defined yet.'),
  );
}

/**
 * @param Entity $entity
 *   The index or server for which a link should be generated.
 *
 * @return string
 *   A link to a delete form for the entity, if applicable.
 */
function _search_api_admin_delete_link(Entity $entity) {
  // Delete link only makes sense if entity is in the database (custom or overridden).
  if ($entity->hasStatus(ENTITY_CUSTOM)) {
    $type = $entity instanceof SearchApiServer ? 'server' : 'index';
    $url = 'admin/config/search/search_api/' . $type . '/' . $entity->machine_name . '/delete';
    $title = $entity->hasStatus(ENTITY_IN_CODE) ? t('revert') : t('delete');
    return l($title, $url);
  }
  return '';
}

/**
 * Form callback showing a form for adding a server.
 */
function search_api_admin_add_server(array $form, array &$form_state) {
  drupal_set_title(t('Add server'));

  $class = empty($form_state['values']['class']) ? '' : $form_state['values']['class'];
  $form_state['server'] = entity_create('search_api_server', array());

  if (empty($form_state['storage']['step_one'])) {
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Server name'),
      '#description' => t('Enter the displayed name for the new server.'),
      '#maxlength' => 50,
      '#required' => TRUE,
    );

    $form['machine_name'] = array(
      '#type' => 'machine_name',
      '#maxlength' => 50,
      '#machine_name' => array(
        'exists' => 'search_api_server_load',
      ),
    );

    $form['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#description' => t('Select if the new server will be enabled after creation.'),
      '#default_value' => TRUE,
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('Server description'),
      '#description' => t('Enter a description for the new server.'),
    );
    $form['class'] = array(
      '#type' => 'select',
      '#title' => t('Service class'),
      '#description' => t('Choose a service class to use for this server.'),
      '#options' => array('' => '< ' . t('Choose a service class') . ' >'),
      '#required' => TRUE,
      '#default_value' => $class,
      '#ajax' => array(
        'callback' => 'search_api_admin_add_server_ajax_callback',
        'wrapper' => 'search-api-class-options',
      ),
    );
  }
  elseif (!$class) {
    $class = $form_state['storage']['step_one']['class'];
  }

  foreach (search_api_get_service_info() as $id => $info) {
    if (empty($form_state['storage']['step_one'])) {
      $form['class']['#options'][$id] = $info['name'];
    }

    if (!$class || $class != $id) {
      continue;
    }

    $service = NULL;
    if (class_exists($info['class'])) {
      $service = new $info['class']($form_state['server']);
    }
    if (!($service instanceof SearchApiServiceInterface)) {
      watchdog('search_api', t('Service class @id specifies an illegal class: @class', array('@id' => $id, '@class' => $info['class'])), NULL, WATCHDOG_ERROR);
      continue;
    }
    $service_form = isset($form['options']['form']) ? $form['options']['form'] : array();
    $service_form = $service->configurationForm($service_form, $form_state);
    $form['options']['form'] = $service_form ? $service_form : array('#markup' => t('There are no configuration options for this service class.'));
    $form['options']['class']['#type'] = 'value';
    $form['options']['class']['#value'] = $class;
    $form['options']['#type'] = 'fieldset';
    $form['options']['#tree'] = TRUE;
    $form['options']['#collapsible'] = TRUE;
    $form['options']['#title'] = $info['name'];
    $form['options']['#description'] = $info['description'];
  }
  $form['options']['#prefix'] = '<div id="search-api-class-options">';
  $form['options']['#suffix'] = '</div>';

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create server'),
  );

  return $form;
}

/**
 * AJAX callback that just returns the "options" array of the already built form
 * array.
 */
function search_api_admin_add_server_ajax_callback(array $form, array &$form_state) {
  return $form['options'];
}

/**
 * Form validation callback for adding a server.
 *
 * Validates the machine name and calls the service class' validation handler.
 */
function search_api_admin_add_server_validate(array $form, array &$form_state) {
  if (!empty($form_state['values']['machine_name'])) {
    $name = $form_state['values']['machine_name'];
    if (is_numeric($name)) {
      form_set_error('machine_name', t('The machine name must not be a pure number.'));
    }
  }

  if (empty($form_state['values']['options']['class'])) {
    return;
  }
  $class = $form_state['values']['options']['class'];
  $info = search_api_get_service_info($class);
  $service = NULL;
  if (class_exists($info['class'])) {
    $service = new $info['class']($form_state['server']);
  }
  if (!($service instanceof SearchApiServiceInterface)) {
    form_set_error('class', t('There seems to be something wrong with the selected service class.'));
    return;
  }
  $form_state['values']['options']['service'] = $service;
  $values = isset($form_state['values']['options']['form']) ? $form_state['values']['options']['form'] : array();
  $service->configurationFormValidate($form['options']['form'], $values, $form_state);
}

/**
 * Form submit callback for adding a server.
 */
function search_api_admin_add_server_submit(array $form, array &$form_state) {
  form_state_values_clean($form_state);
  $values = $form_state['values'];

  if (!empty($form_state['storage']['step_one'])) {
    $values += $form_state['storage']['step_one'];
    unset($form_state['storage']);
  }

  if (empty($values['options']) || ($values['class'] != $values['options']['class'])) {
    unset($values['options']);
    $form_state['storage']['step_one'] = $values;
    $form_state['rebuild'] = TRUE;
    drupal_set_message(t('Please configure the used service.'));
    return;
  }

  $options = isset($values['options']['form']) ? $values['options']['form'] : array();
  unset($values['options']);
  $form_state['server']  = $server = entity_create('search_api_server', $values);
  $server->configurationFormSubmit($form['options']['form'], $options, $form_state);
  $server->save();
  $form_state['redirect'] = 'admin/config/search/search_api/server/' . $server->machine_name;
  drupal_set_message(t('The server was successfully created.'));
}

/**
 * Title callback for viewing or editing a server or index.
 */
function search_api_admin_item_title($object) {
  return $object->name;
}

/**
 * Displays a server's details.
 *
 * @param SearchApiServer $server
 *   The server to display.
 * @param $action
 *   One of 'enable', 'disable', 'delete'; or NULL if the server is only viewed.
 */
function search_api_admin_server_view(SearchApiServer $server, $action = NULL) {
  if (!empty($action)) {
    if ($action == 'enable') {
      if (isset($_GET['token']) && drupal_valid_token($_GET['token'], $server->machine_name)) {
        if ($server->update(array('enabled' => 1))) {
          drupal_set_message(t('The server was successfully enabled.'));
        }
        else {
          drupal_set_message(t('The server could not be enabled. Check the logs for details.'), 'error');
        }
        drupal_goto('admin/config/search/search_api/server/' . $server->machine_name);
      }
      else {
        return MENU_ACCESS_DENIED;
      }
    }
    else {
      $ret = drupal_get_form('search_api_admin_confirm', 'server', $action, $server);
      if ($ret) {
        return $ret;
      }
    }
  }

  drupal_set_title(search_api_admin_item_title($server));
  $class = search_api_get_service_info($server->class);
  $options = $server->viewSettings();
  return array(
    '#theme' => 'search_api_server',
    '#id' => $server->id,
    '#name' => $server->name,
    '#machine_name' => $server->machine_name,
    '#description' => $server->description,
    '#enabled' => $server->enabled,
    '#class_name' => $class['name'],
    '#class_description' => $class['description'],
    '#options' => $options,
    '#status' => $server->status,
  );
}

/**
 * Theme function for displaying a server.
 *
 * @param array $variables
 *   An associative array containing:
 *   - id: The server's id.
 *   - name: The server's name.
 *   - machine_name: The server's machine name.
 *   - description: The server's description.
 *   - enabled: Boolean indicating whether the server is enabled.
 *   - class_name: The used service class' display name.
 *   - class_description: The used service class' description.
 *   - options: An HTML string or render array containing information about the
 *     server's service-specific settings.
 *   - status: The entity configuration status (in database, in code, etc.).
 */
function theme_search_api_server(array $variables) {
  extract($variables);
  $output = '';

  $output .= '<h3>' . check_plain($name) . '</h3>' . "\n";

  $output .= '<dl>' . "\n";

  $output .= '<dt>' . t('Status') . '</dt>' . "\n";
  $output .= '<dd>';
  if ($enabled) {
    $output .= t('enabled (!disable_link)', array('!disable_link' => l(t('disable'), 'admin/config/search/search_api/server/' . $machine_name . '/disable')));
  }
  else {
    $output .= t('disabled (!enable_link)', array('!enable_link' => l(t('enable'), 'admin/config/search/search_api/server/' . $machine_name . '/enable', array('query' => array('token' => drupal_get_token($machine_name))))));
  }
  $output .= '</dd>' . "\n";

  $output .= '<dt>' . t('Machine name') . '</dt>' . "\n";
  $output .= '<dd>' . check_plain($machine_name) . '</dd>' . "\n";

  if (!empty($description)) {
    $output .= '<dt>' . t('Description') . '</dt>' . "\n";
    $output .= '<dd>' . nl2br(check_plain($description)) . '</dd>' . "\n";
  }

  if (!empty($class_name)) {
    $output .= '<dt>' . t('Service class') . '</dt>' . "\n";
    $output .= '<dd><em>' . check_plain($class_name) . '</em>';
    if (!empty($class_description)) {
      $output .= '<p class="description">' . $class_description . '</p>';
    }
    $output .= '</dd>' . "\n";
  }

  if (!empty($options)) {
    $output .= '<dt>' . t('Service options') . '</dt>' . "\n";
    $output .= '<dd>' . "\n";
    $output .= render($options);
    $output .= '</dd>' . "\n";
  }

  $output .= '<dt>' . t('Configuration status') . '</dt>' . "\n";
  $output .= '<dd>' . "\n";
  $output .= theme('entity_status', array('status' => $status));
  $output .= '</dd>' . "\n";

  $output .= '</dl>';

  return $output;
}

/**
 * Edit a server's settings.
 *
 * @param SearchApiServer $server
 *   The server to edit.
 */
function search_api_admin_server_edit(array $form, array &$form_state, SearchApiServer $server) {
  $form_state['server'] = $server;

  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Server name'),
    '#description' => t('Enter the displayed name for the  server.'),
    '#maxlength' => 50,
    '#default_value' => $server->name,
    '#required' => TRUE,
  );
  $form['enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enabled'),
    '#default_value' => $server->enabled,
  );
  $form['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Server description'),
    '#description' => t('Enter a description for the new server.'),
    '#default_value' => $server->description,
  );

  $class = search_api_get_service_info($server->class);

  $service_options = array();
  $service_options = $server->configurationForm($service_options, $form_state);
  if ($service_options) {
    $form['options']['form'] = $service_options;
  }
  $form['options']['#type'] = 'fieldset';
  $form['options']['#tree'] = TRUE;
  $form['options']['#collapsible'] = TRUE;
  $form['options']['#title'] = $class['name'];
  $form['options']['#description'] = $class['description'];

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save settings'),
  );

  return $form;
}

/**
 * Validation function for search_api_admin_server_edit.
 */
function search_api_admin_server_edit_validate(array $form, array &$form_state) {
  $form_state['server']->configurationFormValidate($form['options']['form'], $form_state['values']['options']['form'], $form_state);
}

/**
 * Submit function for search_api_admin_server_edit.
 */
function search_api_admin_server_edit_submit(array $form, array &$form_state) {
  form_state_values_clean($form_state);
  $values = $form_state['values'];

  $server = $form_state['server'];
  if (isset($values['options'])) {
    $server->configurationFormSubmit($form['options']['form'], $values['options']['form'], $form_state);
  }
  unset($values['options']);

  $server->update($values);
  $form_state['redirect'] = 'admin/config/search/search_api/server/' . $server->machine_name;
  drupal_set_message(t('The search server was successfully edited.'));
}

/**
 * Form callback showing a form for adding an index.
 */
function search_api_admin_add_index(array $form, array &$form_state) {
  drupal_set_title(t('Add index'));

  $form['#attached']['css'][] = drupal_get_path('module', 'search_api') . '/search_api.admin.css';
  $form['#tree'] = TRUE;
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Index name'),
    '#maxlength' => 50,
    '#required' => TRUE,
  );

  $form['machine_name'] = array(
    '#type' => 'machine_name',
    '#maxlength' => 50,
    '#machine_name' => array(
      'exists' => 'search_api_index_load',
    ),
  );

  $form['item_type'] = array(
    '#type' => 'select',
    '#title' => t('Item type'),
    '#description' => t('Select the type of items that will be indexed in this index. ' .
        'This setting cannot be changed afterwards.'),
    '#options' => array(),
    '#required' => TRUE,
  );
  foreach (search_api_get_item_type_info() as $type => $info) {
    $form['item_type']['#options'][$type] = $info['name'];
  }
  $form['enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enabled'),
    '#description' => t('This will only take effect if the selected server is also enabled.'),
    '#default_value' => TRUE,
  );
  $form['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Index description'),
  );
  $form['server'] = array(
    '#type' => 'select',
    '#title' => t('Server'),
    '#description' => t('Select the server this index should reside on.'),
    '#default_value' => '',
    '#options' => array('' => t('< No server >'))
  );
  $servers = search_api_server_load_multiple(FALSE);
  // List enabled servers first.
  foreach ($servers as $server) {
    if ($server->enabled) {
      $form['server']['#options'][$server->machine_name] = $server->name;
    }
  }
  foreach ($servers as $server) {
    if (!$server->enabled) {
      $form['server']['#options'][$server->machine_name] = t('@server_name (disabled)', array('@server_name' => $server->name));
    }
  }
  $form['read_only'] = array(
    '#type' => 'checkbox',
    '#title' => t('Read only'),
    '#description' => t('Do not write to this index or track the status of items in this index.'),
    '#default_value' => FALSE,
  );
  $form['options']['index_directly'] = array(
    '#type' => 'checkbox',
    '#title' => t('Index items immediately'),
    '#description' => t('Immediately index new or updated items instead of waiting for the next cron run. ' .
        'This might have serious performance drawbacks and is generally not advised for larger sites.'),
    '#default_value' => FALSE,
  );
  $form['options']['cron_limit'] = array(
    '#type' => 'textfield',
    '#title' => t('Cron batch size'),
    '#description' => t('Set how many items will be indexed at once when indexing items during a cron run. ' .
        '"0" means that no items will be indexed by cron for this index, "-1" means that cron should index all items at once.'),
    '#default_value' => SEARCH_API_DEFAULT_CRON_LIMIT,
    '#size' => 4,
    '#attributes' => array('class' => array('search-api-cron-limit')),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create index'),
  );

  return $form;
}

/**
 * Validation callback for search_api_admin_add_index.
 */
function search_api_admin_add_index_validate(array $form, array &$form_state) {
  $name = $form_state['values']['machine_name'];
  if (is_numeric($name)) {
    form_set_error('machine_name', t('The machine name must not be a pure number.'));
  }

  $cron_limit = $form_state['values']['options']['cron_limit'];
  if ($cron_limit != '' . ((int) $cron_limit)) {
    // We don't enforce stricter rules and treat all negative values as -1.
    form_set_error('options[cron_limit]', t('The cron batch size must be an integer.'));
  }
}

/**
 * Submit callback for search_api_admin_add_index.
 */
function search_api_admin_add_index_submit(array $form, array &$form_state) {
  form_state_values_clean($form_state);

  $values = $form_state['values'];

  // Validation of whether the server of an enabled index is also enabled is
  // done in the *_insert() function.
  search_api_index_insert($values);

  drupal_set_message(t('The index was successfully created. Please set up its indexed fields now.'));
  $form_state['redirect'] = 'admin/config/search/search_api/index/' . $values['machine_name'] . '/fields';
}

/**
 * Displays an index' details.
 *
 * @param SearchApiIndex $index
 *   The index to display.
 */
function search_api_admin_index_view(SearchApiIndex $index = NULL, $action = NULL) {
  if (empty($index)) {
    return MENU_NOT_FOUND;
  }

  if (!empty($action)) {
    if ($action == 'enable') {
      if (isset($_GET['token']) && drupal_valid_token($_GET['token'], $index->machine_name)) {
        if ($index->update(array('enabled' => 1))) {
          drupal_set_message(t('The index was successfully enabled.'));
        }
        else {
          drupal_set_message(t('The index could not be enabled. Check the logs for details.'), 'error');
        }
        drupal_goto('admin/config/search/search_api/index/' . $index->machine_name);
      }
      else {
        return MENU_ACCESS_DENIED;
      }
    }
    else {
      $ret = drupal_get_form('search_api_admin_confirm', 'index', $action, $index);
      if ($ret) {
        return $ret;
      }
    }
  }

  $ret = array(
    '#theme' => 'search_api_index',
    '#id' => $index->id,
    '#name' => $index->name,
    '#machine_name' => $index->machine_name,
    '#description' => $index->description,
    '#item_type' => $index->item_type,
    '#enabled' => $index->enabled,
    '#server' => $index->server(),
    '#options' => $index->options,
    '#fields' => $index->getFields(),
    '#status' => $index->status,
    '#read_only' => $index->read_only,
  );

  return $ret;
}

/**
 * Theme function for displaying an index.
 *
 * @param array $variables
 *   An associative array containing:
 *   - id: The index's id.
 *   - name: The index' name.
 *   - machine_name: The index' machine name.
 *   - description: The index' description.
 *   - item_type: The type of items stored in this index.
 *   - enabled: Boolean indicating whether the index is enabled.
 *   - server: The server this index currently rests on, if any.
 *   - options: The index' options, like cron limit.
 *   - fields: All indexed fields of the index.
 *   - indexed_items: The number of items already indexed in their latest
 *     version on this index.
 *   - total_items: The total number of items that have to be indexed for this
 *     index.
 *   - status: The entity configuration status (in database, in code, etc.).
 *   - read_only: Boolean indicating whether this index is read only.
 */
function theme_search_api_index(array $variables) {
  extract($variables);

  $output = '';

  $output .= '<h3>' . check_plain($name) . '</h3>' . "\n";

  $output .= '<dl>' . "\n";

  $output .= '<dt>' . t('Status') . '</dt>' . "\n";
  $output .= '<dd>';
  if ($enabled) {
    $output .= t('enabled (!disable_link)', array('!disable_link' => l(t('disable'), 'admin/config/search/search_api/index/' . $machine_name . '/disable')));
  }
  elseif ($server && $server->enabled) {
    $output .= t('disabled (!enable_link)', array('!enable_link' => l(t('enable'), 'admin/config/search/search_api/index/' . $machine_name . '/enable', array('query' => array('token' => drupal_get_token($machine_name))))));
  }
  else {
    $output .= t('disabled');
  }
  $output .= '</dd>' . "\n";

  $output .= '<dt>' . t('Machine name') . '</dt>' . "\n";
  $output .= '<dd>' . check_plain($machine_name) . '</dd>' . "\n";

  $output .= '<dt>' . t('Item type') . '</dt>' . "\n";
  $type = search_api_get_item_type_info($item_type);
  $type = $type['name'];
  $output .= '<dd>' . check_plain($type) . '</dd>' . "\n";

  if (!empty($description)) {
    $output .= '<dt>' . t('Description') . '</dt>' . "\n";
    $output .= '<dd>' . nl2br(check_plain($description)) . '</dd>' . "\n";
  }

  if (!empty($server)) {
    $output .= '<dt>' . t('Server') . '</dt>' . "\n";
    $output .= '<dd>' . l($server->name, 'admin/config/search/search_api/server/' . $server->machine_name);
    if (!empty($server->description)) {
      $output .= '<p class="description">' . nl2br(check_plain($server->description)) . '</p>';
    }
    $output .= '</dd>' . "\n";
  }

  if (!$read_only && !empty($options)) {
    $output .= '<dt>' . t('Index options') . '</dt>' . "\n";
    $output .= '<dd><dl>' . "\n";
    $output .= '<dt>' . t('Cron batch size') . '</dt>' . "\n";
    if (empty($options['cron_limit'])) {
      $output .= '<dd>' . t("Don't index during cron runs") . '</dd>' . "\n";
    }
    elseif ($options['cron_limit'] < 0) {
      $output .= '<dd>' . t('Unlimited') . '</dd>' . "\n";
    }
    else {
      $output .= '<dd>' . format_plural($options['cron_limit'], '1 item per cron batch.', '@count items per cron batch.') . '</dd>' . "\n";
    }

    if (!empty($fields)) {
      $fields_list = array();
      foreach ($fields as $name => $field) {
        if (search_api_is_text_type($field['type'])) {
          $fields_list[] = t('@field (@boost x)', array('@field' => $field['name'], '@boost' => $field['boost']));
        }
        else {
          $fields_list[] = check_plain($field['name']);
        }
      }
      if ($fields_list) {
        $output .= '<dt>' . t('Indexed fields') . '</dt>' . "\n";
        $output .= '<dd>' . implode(', ', $fields_list) . '</dd>' . "\n";
      }
    }

    $output .= '</dl></dd>' . "\n";
  }
  elseif ($read_only) {
    $output .= '<dt>' . t('Read only') . '</dt>' . "\n";
    $output .= '<dd>' . t('This index is read-only.') . '</dd>' . "\n";
  }

  $output .= '<dt>' . t('Configuration status') . '</dt>' . "\n";
  $output .= '<dd>' . "\n";
  $output .= theme('entity_status', array('status' => $status));
  $output .= '</dd>' . "\n";

  $output .= '</dl>';

  return $output;
}

/**
 * Form function for displaying an index status form.
 *
 * @param SearchApiIndex $index
 *   The index whose status should be displayed.
 */
function search_api_admin_index_status_form(array $form, array &$form_state, SearchApiIndex $index) {
  $enabled = !empty($index->enabled);
  $status = search_api_index_status($index);
  $server = $index->server();

  $form['#attached']['css'][] = drupal_get_path('module', 'search_api') . '/search_api.admin.css';
  $form_state['index'] = $index;

  $form['status_message'] = array(
    '#type' => 'item',
    '#title' => t('Status'),
    '#description' => $enabled ? t('The index is currently enabled.') : t('The index is currently disabled.'),
  );
  if (!empty($server->enabled)) {
    $form['status'] = array(
      '#type' => 'submit',
      '#value' => $enabled ? t('Disable') : t('Enable'),
    );
  }

  if ($index->read_only) {
    $form['read_only'] = array(
      '#type' => 'item',
      '#title' => t('Read only'),
      '#description' => t('The index is currently in read-only mode. ' .
          'No new items will be indexed, nor will old ones be deleted.'),
    );

    return $form;
  }

  if ($enabled) {
    $form['progress'] = array(
      '#type' => 'item',
      '#title' => t('Progress'),
    );
    $all = ($status['indexed'] == $status['total']);
    if ($all) {
      $form['progress']['#description'] = t('All items have been indexed (@total / @total).',
          array('@total' => $status['total']));
    }
    elseif (!$status['indexed']) {
      $form['progress']['#description'] = t('All items still need to be indexed (@total total).',
          array('@total' => $status['total']));
    }
    else {
      $percentage = (int) (100 * $status['indexed'] / $status['total']);
      $form['progress']['#description'] = t('About @percentage% of all items have been indexed in their latest version (@indexed / @total).',
          array('@indexed' => $status['indexed'], '@total' => $status['total'], '@percentage' => $percentage));
    }

    if (!$all) {
      $form['index'] = array(
        '#type' => 'fieldset',
        '#title' => t('Index now'),
        '#collapsible' => TRUE,
      );
      $form['index']['settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Advanced settings'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );
      $form['index']['settings']['limit'] = array(
        '#type' => 'textfield',
        '#title' => t('Number of items to index'),
        '#default_value' => -1,
        '#size' => 4,
        '#attributes' => array('class' => array('search-api-limit')),
        '#description' => t('Number of items to index. Set to -1 for all items.'),
      );
      $batch_size = empty($index->options['cron_limit']) ? SEARCH_API_DEFAULT_CRON_LIMIT : $index->options['cron_limit'];
      $form['index']['settings']['batch_size'] = array(
        '#type' => 'textfield',
        '#title' => t('Number of items per batch run'),
        '#default_value' => $batch_size,
        '#size' => 4,
        '#attributes' => array('class' => array('search-api-batch-size')),
        '#description' => t('Number of items per batch run. Set to -1 for all items at once (not recommended). Defaults to the cron batch size of the index.'),
      );
      $form['index']['button'] = array(
        '#type' => 'submit',
        '#value' => t('Index now'),
      );
      $form['index']['total'] = array(
        '#type' => 'value',
        '#value' => $status['total'],
      );
      $form['index']['remaining'] = array(
        '#type' => 'value',
        '#value' => $status['total'] - $status['indexed'],
      );
    }
  }

  if ($server) {
    if ($enabled && $status['indexed'] > 0) {
      $form['reindex'] = array(
        '#type' => 'fieldset',
        '#title' => t('Re-indexing'),
        '#collapsible' => TRUE,
      );
      $form['reindex']['message'] = array(
        '#type' => 'item',
        '#description' => t('This will add all items to the index again (overwriting the index), but existing items in the index will remain searchable.'),
      );
      $form['reindex']['button'] = array(
        '#type' => 'submit',
        '#value' => t('Re-index content'),
      );
    }

    $form['clear'] = array(
      '#type' => 'fieldset',
      '#title' => t('Clear index'),
      '#collapsible' => TRUE,
    );
    $form['clear']['message'] = array(
      '#type' => 'item',
      '#description' => t('All items will be deleted from the index and have to be inserted again by normally indexing them. ' .
          'Until all items are re-indexed, searches on this index will return incomplete results.<br />' .
          'Use with care, in most cases rebuilding the index might be enough.'),
    );
    $form['clear']['button'] = array(
      '#type' => 'submit',
      '#value' => t('Clear index'),
    );
  }

  return $form;
}

/**
 * Validation function for search_api_admin_index_status_form.
 */
function search_api_admin_index_status_form_validate(array $form, array &$form_state) {
  if ($form_state['values']['op'] == t('Index now') && !$form_state['values']['limit']) {
    form_set_error('number', t('You have to set the number of items to index. Set to -1 for indexing all items.'));
  }
}

/**
 * Submit function for search_api_admin_index_status_form.
 */
function search_api_admin_index_status_form_submit(array $form, array &$form_state) {
  $redirect = &$form_state['redirect'];
  $values = $form_state['values'];
  $index = $form_state['index'];
  $pre = 'admin/config/search/search_api/index/' . $index->machine_name;
  switch ($values['op']) {
    case t('Enable'):
      $redirect = $pre . '/enable';
      break;
    case t('Disable'):
      $redirect = $pre . '/disable';
      break;
    case t('Index now'):
      if (!_search_api_batch_indexing_create($index, $values['batch_size'], $values['limit'], $values['remaining'])) {
        drupal_set_message(t("Couldn't create a batch, please check the batch size and limit."), 'warning');
      }
      $redirect = $pre . '/status';
      break;
    case t('Re-index content'):
      if ($index->reindex()) {
        drupal_set_message(t('The index was successfully scheduled for re-indexing.'));
      }
      else {
        drupal_set_message(t('An error has occurred while performing the desired action. Check the logs for details.'), 'error');
      }
      $redirect = $pre . '/status';
      break;
    case t('Clear index'):
      if ($index->clear()) {
        drupal_set_message(t('The index was successfully cleared.'));
      }
      else {
        drupal_set_message(t('An error has occurred while performing the desired action. Check the logs for details.'), 'error');
      }
      $redirect = $pre . '/status';
      break;

    default:
      throw new SearchApiException(t('Unknown action.'));
  }
}

/**
 * Edit an index' settings.
 *
 * @param SearchApiIndex $index
 *   The index to edit.
 */
function search_api_admin_index_edit(array $form, array &$form_state, SearchApiIndex $index) {
  $form_state['index'] = $index;

  $form['#attached']['css'][] = drupal_get_path('module', 'search_api') . '/search_api.admin.css';
  $form['#tree'] = TRUE;
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Index name'),
    '#maxlength' => 50,
    '#default_value' => $index->name,
    '#required' => TRUE,
  );
  $form['enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enabled'),
    '#default_value' => $index->enabled,
    // Can't enable an index lying on a disabled server, or no server at all.
    '#disabled' => !$index->enabled && (!$index->server() || !$index->server()->enabled),
  );
  $form['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Index description'),
    '#default_value' => $index->description,
  );
  $form['server'] = array(
    '#type' => 'select',
    '#title' => t('Server'),
    '#description' => t('Select the server this index should reside on.'),
    '#default_value' => $index->server,
    '#options' => array('' => t('< No server >'))
  );
  $servers = search_api_server_load_multiple(FALSE);
  // List enabled servers first.
  foreach ($servers as $server) {
    if ($server->enabled) {
      $form['server']['#options'][$server->machine_name] = $server->name;
    }
  }
  foreach ($servers as $server) {
    if (!$server->enabled) {
      $form['server']['#options'][$server->machine_name] = t('@server_name (disabled)', array('@server_name' => $server->name));
    }
  }
  $form['read_only'] = array(
    '#type' => 'checkbox',
    '#title' => t('Read only'),
    '#description' => t('Do not write to this index or track the status of items in this index.'),
    '#default_value' => $index->read_only,
  );
  $form['options']['index_directly'] = array(
    '#type' => 'checkbox',
    '#title' => t('Index items immediately'),
    '#description' => t('Immediately index new or updated items instead of waiting for the next cron run. ' .
        'This might have serious performance drawbacks and is generally not advised for larger sites.'),
    '#default_value' => !empty($index->options['index_directly']),
    '#states' => array(
      'invisible' => array(':input[name="read_only"]' => array('checked' => TRUE)),
    ),
  );
  $form['options']['cron_limit'] = array(
    '#type' => 'textfield',
    '#title' => t('Cron batch size'),
    '#description' => t('Set how many items will be indexed at once when indexing items during a cron run. ' .
        '"0" means that no items will be indexed by cron for this index, "-1" means that cron should index all items at once.'),
    '#default_value' => isset($index->options['cron_limit']) ? $index->options['cron_limit'] : SEARCH_API_DEFAULT_CRON_LIMIT,
    '#size' => 4,
    '#attributes' => array('class' => array('search-api-cron-limit')),
    '#element_validate' => array('_element_validate_integer'),
    '#states' => array(
      'invisible' => array(':input[name="read_only"]' => array('checked' => TRUE)),
    ),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save settings'),
  );

  return $form;
}

/**
 * Submit callback for search_api_admin_index_edit.
 */
function search_api_admin_index_edit_submit(array $form, array &$form_state) {
  form_state_values_clean($form_state);

  $values = $form_state['values'];
  $index = $form_state['index'];
  $values['options'] += $index->options;

  $ret = $index->update($values);
  $form_state['redirect'] = 'admin/config/search/search_api/index/' . $index->machine_name;
  if ($ret) {
    drupal_set_message(t('The search index was successfully edited.'));
  }
  else {
    drupal_set_message(t('No values were changed.'));
  }
}

/**
 * Edit an index' workflow (data alter callbacks, pre-/postprocessors, and their
 * order).
 *
 * @param SearchApiIndex $index
 *   The index to edit.
 */
// Copied from filter_admin_format_form
function search_api_admin_index_workflow(array $form, array &$form_state, SearchApiIndex $index) {
  $callback_info = search_api_get_alter_callbacks();
  $processor_info = search_api_get_processors();
  $options = empty($index->options) ? array() : $index->options;

  $form_state['index'] = $index;
  $form['#tree'] = TRUE;
  $form['#attached']['js'][] = drupal_get_path('module', 'search_api') . '/search_api.admin.js';

  // Callbacks

  $callbacks = empty($options['data_alter_callbacks']) ? array() : $options['data_alter_callbacks'];
  $callback_objects = isset($form_state['callbacks']) ? $form_state['callbacks'] : array();
  foreach ($callback_info as $name => $callback) {
    if (!isset($callbacks[$name])) {
      $callbacks[$name]['status'] = 0;
      $callbacks[$name]['weight'] = $callback['weight'];
    }
    $settings = empty($callbacks[$name]['settings']) ? array() : $callbacks[$name]['settings'];
    if (empty($callback_objects[$name]) && class_exists($callback['class'])) {
      $callback_objects[$name] = new $callback['class']($index, $settings);
    }
    if (!(class_exists($callback['class']) && $callback_objects[$name] instanceof SearchApiAlterCallbackInterface)) {
      watchdog('search_api', t('Data alteration @id specifies illegal callback class @class.', array('@id' => $name, '@class' => $callback['class'])), NULL, WATCHDOG_WARNING);
      unset($callback_info[$name]);
      unset($callbacks[$name]);
      unset($callback_objects[$name]);
      continue;
    }
    if (!$callback_objects[$name]->supportsIndex($index)) {
      unset($callback_info[$name]);
      unset($callbacks[$name]);
      unset($callback_objects[$name]);
      continue;
    }
  }
  $form_state['callbacks'] = $callback_objects;
  $form['#callbacks'] = $callbacks;
  $form['callbacks'] = array(
    '#type' => 'fieldset',
    '#title' => t('Data alterations'),
    '#description' => t('Select the alterations that will be executed on indexed items, and their order.'),
    '#collapsible' => TRUE,
  );

  // Callback status.
  $form['callbacks']['status'] = array(
    '#type' => 'item',
    '#title' => t('Enabled data alterations'),
    '#prefix' => '<div class="search-api-status-wrapper">',
    '#suffix' => '</div>',
  );
  foreach ($callback_info as $name => $callback) {
    $form['callbacks']['status'][$name] = array(
      '#type' => 'checkbox',
      '#title' => $callback['name'],
      '#default_value' => $callbacks[$name]['status'],
      '#parents' => array('callbacks', $name, 'status'),
      '#description' => $callback['description'],
      '#weight' => $callback['weight'],
    );
  }

  // Callback order (tabledrag).
  $form['callbacks']['order'] = array(
    '#type' => 'item',
    '#title' => t('Data alteration processing order'),
    '#theme' => 'search_api_admin_item_order',
    '#table_id' => 'search-api-callbacks-order-table',
  );
  foreach ($callback_info as $name => $callback) {
    $form['callbacks']['order'][$name]['item'] = array(
      '#markup' => $callback['name'],
    );
    $form['callbacks']['order'][$name]['weight'] = array(
      '#type' => 'weight',
      '#delta' => 50,
      '#default_value' => $callbacks[$name]['weight'],
      '#parents' => array('callbacks', $name, 'weight'),
    );
    $form['callbacks']['order'][$name]['#weight'] = $callbacks[$name]['weight'];
  }

  // Callback settings.
  $form['callbacks']['settings_title'] = array(
    '#type' => 'item',
    '#title' => t('Callback settings'),
  );
  $form['callbacks']['settings'] = array(
    '#type' => 'vertical_tabs',
  );

  foreach ($callback_info as $name => $callback) {
    $settings_form = $callback_objects[$name]->configurationForm();
    if (!empty($settings_form)) {
      $form['callbacks']['settings'][$name] = array(
        '#type' => 'fieldset',
        '#title' => $callback['name'],
        '#parents' => array('callbacks', $name, 'settings'),
        '#weight' => $callback['weight'],
      );
      $form['callbacks']['settings'][$name] += $settings_form;
    }
  }

  // Processors

  $processors = empty($options['processors']) ? array() : $options['processors'];
  $processor_objects = isset($form_state['processors']) ? $form_state['processors'] : array();
  foreach ($processor_info as $name => $processor) {
    if (!isset($processors[$name])) {
      $processors[$name]['status'] = 0;
      $processors[$name]['weight'] = $processor['weight'];
    }
    $settings = empty($processors[$name]['settings']) ? array() : $processors[$name]['settings'];
    if (empty($processor_objects[$name]) && class_exists($processor['class'])) {
      $processor_objects[$name] = new $processor['class']($index, $settings);
    }
    if (!(class_exists($processor['class']) && $processor_objects[$name] instanceof SearchApiProcessorInterface)) {
      watchdog('search_api', t('Processor @id specifies illegal processor class @class.', array('@id' => $name, '@class' => $processor['class'])), NULL, WATCHDOG_WARNING);
      unset($processor_info[$name]);
      unset($processors[$name]);
      unset($processor_objects[$name]);
      continue;
    }
    if (!$processor_objects[$name]->supportsIndex($index)) {
      unset($processor_info[$name]);
      unset($processors[$name]);
      unset($processor_objects[$name]);
      continue;
    }
  }
  $form_state['processors'] = $processor_objects;
  $form['#processors'] = $processors;
  $form['processors'] = array(
    '#type' => 'fieldset',
    '#title' => t('Processors'),
    '#description' => t('Select processors which will pre- and post-process data at index and search time, and their order. ' .
        'Most processors will only influence fulltext fields, but refer to their individual descriptions for details regarding their effect.'),
    '#collapsible' => TRUE,
  );

  // Processor status.
  $form['processors']['status'] = array(
    '#type' => 'item',
    '#title' => t('Enabled processors'),
    '#prefix' => '<div class="search-api-status-wrapper">',
    '#suffix' => '</div>',
  );
  foreach ($processor_info as $name => $processor) {
    $form['processors']['status'][$name] = array(
      '#type' => 'checkbox',
      '#title' => $processor['name'],
      '#default_value' => $processors[$name]['status'],
      '#parents' => array('processors', $name, 'status'),
      '#description' => $processor['description'],
      '#weight' => $processor['weight'],
    );
  }

  // Processor order (tabledrag).
  $form['processors']['order'] = array(
    '#type' => 'item',
    '#title' => t('Processor processing order'),
    '#description' => t('Set the order in which preprocessing will be done at index and search time. ' .
        'Postprocessing of search results will be in the exact opposite direction.'),
    '#theme' => 'search_api_admin_item_order',
    '#table_id' => 'search-api-processors-order-table',
  );
  foreach ($processor_info as $name => $processor) {
    $form['processors']['order'][$name]['item'] = array(
      '#markup' => $processor['name'],
    );
    $form['processors']['order'][$name]['weight'] = array(
      '#type' => 'weight',
      '#delta' => 50,
      '#default_value' => $processors[$name]['weight'],
      '#parents' => array('processors', $name, 'weight'),
    );
    $form['processors']['order'][$name]['#weight'] = $processors[$name]['weight'];
  }

  // Processor settings.
  $form['processors']['settings_title'] = array(
    '#type' => 'item',
    '#title' => t('Processor settings'),
  );
  $form['processors']['settings'] = array(
    '#type' => 'vertical_tabs',
  );

  foreach ($processor_info as $name => $processor) {
    $settings_form = $processor_objects[$name]->configurationForm();
    if (!empty($settings_form)) {
      $form['processors']['settings'][$name] = array(
        '#type' => 'fieldset',
        '#title' => $processor['name'],
        '#parents' => array('processors', $name, 'settings'),
        '#weight' => $processor['weight'],
      );
      $form['processors']['settings'][$name] += $settings_form;
    }
  }

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save configuration'));

  return $form;
}

/**
 * Returns HTML for a processor/callback order form.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: A render element representing the form.
 */
function theme_search_api_admin_item_order(array $variables) {
  $element = $variables['element'];

  $rows = array();
  foreach (element_children($element, TRUE) as $name) {
    $element[$name]['weight']['#attributes']['class'][] = 'search-api-order-weight';
    $rows[] = array(
      'data' => array(
        drupal_render($element[$name]['item']),
        drupal_render($element[$name]['weight']),
      ),
      'class' => array('draggable'),
    );
  }
  $output = drupal_render_children($element);
  $output .= theme('table', array('rows' => $rows, 'attributes' => array('id' => $element['#table_id'])));
  drupal_add_tabledrag($element['#table_id'], 'order', 'sibling', 'search-api-order-weight', NULL, NULL, TRUE);

  return $output;
}

/**
 * Validation callback for search_api_admin_index_workflow.
 */
function search_api_admin_index_workflow_validate(array $form, array &$form_state) {
  // Call validation functions.
  foreach ($form_state['callbacks'] as $name => $callback) {
    if (isset($form['callbacks']['settings'][$name]) && isset($form_state['values']['callbacks'][$name]['settings'])) {
      $callback->configurationFormValidate($form['callbacks']['settings'][$name], $form_state['values']['callbacks'][$name]['settings'], $form_state);
    }
  }
  foreach ($form_state['processors'] as $name => $processor) {
    if (isset($form['processors']['settings'][$name]) && isset($form_state['values']['processors'][$name]['settings'])) {
      $processor->configurationFormValidate($form['processors']['settings'][$name], $form_state['values']['processors'][$name]['settings'], $form_state);
    }
  }
}

/**
 * Submit callback for search_api_admin_index_workflow.
 */
function search_api_admin_index_workflow_submit(array $form, array &$form_state) {
  $values = $form_state['values'];
  unset($values['callbacks']['settings']);
  unset($values['processors']['settings']);
  $index = $form_state['index'];

  $options = empty($index->options) ? array() : $index->options;
  $fields_set = !empty($options['fields']);

  // Store callback and processor settings.
  foreach ($form_state['callbacks'] as $name => $callback) {
    $callback_form = isset($form['callbacks']['settings'][$name]) ? $form['callbacks']['settings'][$name] : array();
    $values['callbacks'][$name] += array('settings' => array());
    $values['callbacks'][$name]['settings'] = $callback->configurationFormSubmit($callback_form, $values['callbacks'][$name]['settings'], $form_state);
  }
  foreach ($form_state['processors'] as $name => $processor) {
    $processor_form = isset($form['processors']['settings'][$name]) ? $form['processors']['settings'][$name] : array();
    $values['processors'][$name] += array('settings' => array());
    $values['processors'][$name]['settings'] = $processor->configurationFormSubmit($processor_form, $values['processors'][$name]['settings'], $form_state);
  }

  $types = search_api_field_types();
  foreach ($form_state['callbacks'] as $name => $callback) {
    // Check whether callback status has changed.
    if ($values['callbacks'][$name]['status'] == empty($options['data_alter_callbacks'][$name]['status'])) {
      if ($values['callbacks'][$name]['status']) {
        // Callback was just enabled, add its fields.
        $properties = $callback->propertyInfo();
        if ($properties) {
          foreach ($properties as $key => $field) {
            $type = $field['type'];
            $inner = search_api_extract_inner_type($type);
            if ($inner != 'token' && empty($types[$inner])) {
              // Someone apparently added a structure or entity as a property in a data-alter callback.
              continue;
            }
            if ($inner == 'token' || (search_api_is_text_type($inner) && !empty($field['options list']))) {
              $old = $type;
              $type = 'string';
              while (search_api_is_list_type($old)) {
                $old = substr($old, 5, -1);
                $type = "list<$type>";
              }
            }
            $index->options['fields'][$key] = array(
              'type' => $type,
            );
          }
        }
      }
      else {
        // Callback was just disabled, remove its fields.
        $properties = $callback->propertyInfo();
        if ($properties) {
          foreach ($properties as $key => $field) {
            unset($index->options['fields'][$key]);
          }
        }

      }
    }
  }

  if (!isset($options['data_alter_callbacks']) || !isset($options['processors'])
      || $options['data_alter_callbacks'] != $values['callbacks']
      || $options['processors'] != $values['processors']) {
    $index->options['data_alter_callbacks'] = $values['callbacks'];
    $index->options['processors'] = $values['processors'];

    // Save the already sorted arrays to avoid having to sort them at each use.
    uasort($index->options['data_alter_callbacks'], 'search_api_admin_element_compare');
    uasort($index->options['processors'], 'search_api_admin_element_compare');

    // Reset the index's internal property cache to correctly incorporate the
    // new data alterations.
    $index->resetCaches();

    $index->save();
    $index->reindex();
    drupal_set_message(t("The search index' workflow was successfully edited. " .
        'All content was scheduled for re-indexing so the new settings can take effect.'));
  }
  else {
    drupal_set_message(t('No values were changed.'));
  }

  $form_state['redirect'] = 'admin/config/search/search_api/index/' . $index->machine_name . '/workflow';
}

/**
 * Sort callback sorting array elements by their "weight" key, if present.
 *
 * @see element_sort
 */
function search_api_admin_element_compare($a, $b) {
  $a_weight = (is_array($a) && isset($a['weight'])) ? $a['weight'] : 0;
  $b_weight = (is_array($b) && isset($b['weight'])) ? $b['weight'] : 0;
  if ($a_weight == $b_weight) {
    return 0;
  }
  return ($a_weight < $b_weight) ? -1 : 1;
}

/**
 * Select the indexed fields.
 *
 * @param SearchApiIndex $index
 *   The index to edit.
 */
function search_api_admin_index_fields(array $form, array &$form_state, SearchApiIndex $index) {
  $options = $index->getFields(FALSE, TRUE);
  $fields = $options['fields'];
  $additional = $options['additional fields'];

  // An array of option arrays for types, keyed by nesting level.
  $types = array(0 => search_api_field_types());
  $fulltext_type = array(0 => 'text');
  $entity_types = entity_get_info();
  $default_types = search_api_default_field_types();
  $boosts = drupal_map_assoc(array('0.1', '0.2', '0.3', '0.5', '0.8', '1.0', '2.0', '3.0', '5.0', '8.0', '13.0', '21.0'));

  $form_state['index'] = $index;
  $form['#theme'] = 'search_api_admin_fields_table';
  $form['#tree'] = TRUE;
  $form['description'] = array(
    '#type' => 'item',
    '#title' => t('Select fields to index'),
    '#description' => t('<p>The datatype of a field determines how it can be used for searching and filtering. ' .
        'The boost is used to give additional weight to certain fields, e.g. titles or tags. It only takes effect for fulltext fields.</p>' .
        '<p>Whether detailed field types are supported depends on the type of server this index resides on. ' .
        'In any case, fields of type "Fulltext" will always be fulltext-searchable.</p>'),
  );
  if ($index->server) {
    $form['description']['#description'] .= '<p>' . t('Check the <a href="@server-url">' . "server's</a> service class description for details.",
        array('@server-url' => url('admin/config/search/search_api/server/' . $index->server))) . '</p>';
  }
  foreach ($fields as $key => $info) {
    $form['fields'][$key]['title']['#markup'] = check_plain($info['name']);
    if (isset($info['description'])) {
      $form['fields'][$key]['description'] = array(
        '#type' => 'value',
        '#value' => $info['description'],
      );
    }
    $form['fields'][$key]['indexed'] = array(
      '#type' => 'checkbox',
      '#default_value' => $info['indexed'],
    );
    if (empty($info['entity_type'])) {
      // Determine the correct type options (i.e., with the correct nesting level).
      $level = search_api_list_nesting_level($info['type']);
      if (empty($types[$level])) {
        $type_prefix = str_repeat('list<', $level);
        $type_suffix = str_repeat('>', $level);
        $types[$level] = array();
        foreach ($types[0] as $type => $name) {
          // We use the singular name for list types, since the user usually doesn't care about the nesting level.
          $types[$level][$type_prefix . $type . $type_suffix] = $name;
        }
        $fulltext_type[$level] = $type_prefix . 'text' . $type_suffix;
      }
      $css_key = '#edit-fields-' . drupal_clean_css_identifier($key);
      $form['fields'][$key]['type'] = array(
        '#type' => 'select',
        '#options' => $types[$level],
        '#default_value' => isset($info['real_type']) ? $info['real_type'] : $info['type'],
        '#states' => array(
          'visible' => array(
            $css_key . '-indexed' => array('checked' => TRUE),
          ),
        ),
      );
      $form['fields'][$key]['boost'] = array(
        '#type' => 'select',
        '#options' => $boosts,
        '#default_value' => $info['boost'],
        '#states' => array(
          'visible' => array(
            $css_key . '-indexed' => array('checked' => TRUE),
            $css_key . '-type' => array('value' => $fulltext_type[$level]),
          ),
        ),
      );
    }
    else {
      // This is an entity.
      $label = $entity_types[$info['entity_type']]['label'];
      if (!isset($entity_description_added)) {
        $form['description']['#description'] .= '<p>' .
            t('Note that indexing an entity-valued field (like %field, which has type %type) directly will only index the entity ID. ' .
            'This will be used for filtering and also sorting (which might not be what you expect). ' .
            'The entity label will usually be used when displaying the field, though. ' .
            'Use the "Add related fields" option at the bottom for indexing other fields of related entities.',
            array('%field' => $info['name'], '%type' => $label)) . '</p>';
        $entity_description_added = TRUE;
      }
      $form['fields'][$key]['type'] = array(
        '#type' => 'value',
        '#value' => $info['type'],
      );
      $form['fields'][$key]['entity_type'] = array(
        '#type' => 'value',
        '#value' => $info['entity_type'],
      );
      $form['fields'][$key]['type_name'] = array(
        '#markup' => check_plain($label),
      );
      $form['fields'][$key]['boost'] = array(
        '#type' => 'value',
        '#value' => $info['boost'],
      );
      $form['fields'][$key]['boost_text'] = array(
        '#markup' => '&nbsp;',
      );
    }
    if ($key == 'search_api_language') {
      // Is treated specially to always index the language.
      $form['fields'][$key]['type']['#default_value'] = 'string';
      $form['fields'][$key]['type']['#disabled'] = TRUE;
      $form['fields'][$key]['boost']['#default_value'] = '1.0';
      $form['fields'][$key]['boost']['#disabled'] = TRUE;
      $form['fields'][$key]['indexed']['#default_value'] = 1;
      $form['fields'][$key]['indexed']['#disabled'] = TRUE;
    }
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save changes'),
  );

  if ($additional) {
    reset($additional);
    $form['additional'] = array(
      '#type' => 'fieldset',
      '#title' => t('Add related fields'),
      '#description' => t('There are entities related to entities of this type. ' .
          'You can add their fields to the list above so they can be indexed too.') . '<br />',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#attributes' => array('class' => array('container-inline')),
      'field' => array(
        '#type' => 'select',
        '#options' => $additional,
        '#default_value' => key($additional),
      ),
      'add' => array(
        '#type' => 'submit',
        '#value' => t('Add fields'),
      ),
    );
  }

  return $form;
}

/**
 * Helper function for building the field list for an index.
 *
 * @deprecated Use SearchApiIndex::getFields() instead.
 */
function _search_api_admin_get_fields(SearchApiIndex $index, EntityMetadataWrapper $wrapper) {
  $fields = empty($index->options['fields']) ? array() : $index->options['fields'];
  $additional = array();
  $entity_types = entity_get_info();

  // First we need all already added prefixes.
  $added = array();
  foreach (array_keys($fields) as $key) {
    $key = substr($key, 0, strrpos($key, ':'));
    $added[$key] = TRUE;
  }

  // Then we walk through all properties and look if they are already contained in one of the arrays.
  // Since this uses an iterative instead of a recursive approach, it is a bit complicated, with three arrays tracking the current depth.

  // A wrapper for a specific field name prefix, e.g. 'user:' mapped to the user wrapper
  $wrappers = array('' => $wrapper);
  // Display names for the prefixes
  $prefix_names = array('' => '');
    // The list nesting level for entities with a certain prefix
  $nesting_levels = array('' => 0);

  $types = search_api_default_field_types();
  $flat = array();
  while ($wrappers) {
    foreach ($wrappers as $prefix => $wrapper) {
      $prefix_name = $prefix_names[$prefix];
      // Deal with lists of entities.
      $nesting_level = $nesting_levels[$prefix];
      $type_prefix = str_repeat('list<', $nesting_level);
      $type_suffix = str_repeat('>', $nesting_level);
      if ($nesting_level) {
        $info = $wrapper->info();
        // The real nesting level of the wrapper, not the accumulated one.
        $level = search_api_list_nesting_level($info['type']);
        for ($i = 0; $i < $level; ++$i) {
          $wrapper = $wrapper[0];
        }
      }
      // Now look at all properties.
      foreach ($wrapper as $property => $value) {
        $info = $value->info();
        // We hide the complexity of multi-valued types from the user here.
        $type = search_api_extract_inner_type($info['type']);
        // Treat Entity API type "token" as our "string" type.
        // Also let text fields with limited options be of type "string" by default.
        if ($type == 'token' || ($type == 'text' && !empty($info['options list']))) {
          // Inner type is changed to "string".
          $type = 'string';
          // Set the field type accordingly.
          $info['type'] = search_api_nest_type('string', $info['type']);
        }
        $info['type'] = $type_prefix . $info['type'] . $type_suffix;
        $key = $prefix . $property;
        if (isset($types[$type]) || isset($entity_types[$type])) {
          if (isset($fields[$key])) {
            // This field is already known in the index configuration.
            $fields[$key]['name'] = $prefix_name . $info['label'];
            $fields[$key]['description'] = empty($info['description']) ? NULL : $info['description'];
            $flat[$key] = $fields[$key];
            // Update its type.
            if (isset($entity_types[$type])) {
              // Always enforce the proper entity type.
              $flat[$key]['type'] = $info['type'];
            }
            else {
              // Else, only update the nesting level.
              $set_type = search_api_extract_inner_type(isset($flat[$key]['real_type']) ? $flat[$key]['real_type'] : $flat[$key]['type']);
              $flat[$key]['type'] = $info['type'];
              $flat[$key]['real_type'] = search_api_nest_type($set_type, $info['type']);
            }
          }
          else {
            $flat[$key] = array(
              'name'    => $prefix_name . $info['label'],
              'description' => empty($info['description']) ? NULL : $info['description'],
              'type'    => $info['type'],
              'boost' => '1.0',
              'indexed' => FALSE,
            );
          }
        }
        if (empty($types[$type])) {
          if (isset($added[$key])) {
            // Visit this entity/struct in a later iteration.
            $wrappers[$key . ':'] = $value;
            $prefix_names[$key . ':'] = $prefix_name . $info['label'] . ' » ';
            $nesting_levels[$key . ':'] = search_api_list_nesting_level($info['type']);
          }
          else {
            $name = $prefix_name . $info['label'];
            // Add machine names to discern fields with identical labels.
            if (isset($used_names[$name])) {
              if ($used_names[$name] !== FALSE) {
                $additional[$used_names[$name]] .= ' [' . $used_names[$name] . ']';
                $used_names[$name] = FALSE;
              }
              $name .= ' [' . $key . ']';
            }
            $additional[$key] = $name;
            $used_names[$name] = $key;
          }
        }
      }
      unset($wrappers[$prefix]);
    }
  }

  $options = array();
  $options['fields'] = $flat;
  $options['additional fields'] = $additional;
  return $options;
}

/**
 * Returns HTML for a field list form.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: A render element representing the form.
 */
function theme_search_api_admin_fields_table($variables) {
  $form = $variables['element'];
  $header = array(t('Field'), t('Indexed'), t('Type'), t('Boost'));

  $rows = array();
  foreach (element_children($form['fields']) as $name) {
    $row = array();
    foreach (element_children($form['fields'][$name]) as $field) {
      if ($cell = render($form['fields'][$name][$field])) {
        $row[] = $cell;
      }
    }
    if (empty($form['fields'][$name]['description']['#value'])) {
      $rows[] = $row;
    }
    else {
      $rows[] = array(
        'data' => $row,
        'title' => strip_tags($form['fields'][$name]['description']['#value']),
      );
    }
  }

  $submit = $form['submit'];
  $additional = isset($form['additional']) ? $form['additional'] : FALSE;
  unset($form['submit'], $form['additional']);
  $output = drupal_render_children($form);
  $output .= theme('table', array('header' => $header, 'rows' => $rows));
  $output .= render($submit);
  if ($additional) {
    $output .= render($additional);
  }

  return $output;
}

/**
 * Submit function for search_api_admin_index_fields.
 */
function search_api_admin_index_fields_submit(array $form, array &$form_state) {
  $index = $form_state['index'];
  $options = isset($index->options) ? $index->options : array();
  if ($form_state['values']['op'] == t('Save changes')) {
    $fields = $form_state['values']['fields'];
    $default_types = search_api_default_field_types();
    $custom_types = search_api_get_data_type_info();
    foreach ($fields as $name => $field) {
      if (empty($field['indexed'])) {
        unset($fields[$name]);
      }
      else {
        // Don't store the description. "indexed" is implied.
        unset($fields[$name]['description'], $fields[$name]['indexed']);
        // For non-default types, set type to the fallback and only real_type to
        // the custom type.
        $inner_type = search_api_extract_inner_type($field['type']);
        if (!isset($default_types[$inner_type])) {
          $fields[$name]['real_type'] = $field['type'];
          $fields[$name]['type'] = search_api_nest_type($custom_types[$inner_type]['fallback'], $field['type']);
        }
        // Boost defaults to 1.0.
        if ($field['boost'] == '1.0') {
          unset($fields[$name]['boost']);
        }
      }
    }
    $options['fields'] = $fields;
    unset($options['additional fields']);
    $ret = $index->update(array('options' => $options));

    if ($ret) {
      drupal_set_message(t('The indexed fields were successfully changed. ' .
          'The index was cleared and will have to be re-indexed with the new settings.'));
    }
    else {
      drupal_set_message(t('No values were changed.'));
    }
    if (isset($index->options['data_alter_callbacks']) || isset($index->options['processors'])) {
      $form_state['redirect'] = 'admin/config/search/search_api/index/' . $index->machine_name . '/fields';
    }
    else {
      drupal_set_message(t('Please set up the index workflow.'));
      $form_state['redirect'] = 'admin/config/search/search_api/index/' . $index->machine_name . '/workflow';
    }
    return;
  }
  // Adding a related entity's fields.
  $prefix = $form_state['values']['additional']['field'];
  $options['additional fields'][$prefix] = $prefix;
  $ret = $index->update(array('options' => $options));

  if ($ret) {
    drupal_set_message(t('The available fields were successfully changed.'));
  }
  else {
    drupal_set_message(t('No values were changed.'));
  }
  $form_state['redirect'] = 'admin/config/search/search_api/index/' . $index->machine_name . '/fields';
}


/**
 * Helper function for displaying a generic confirmation form.
 *
 * @return
 *   Either a form array, or FALSE if this combination of type and action is
 *   not supported.
 */
function search_api_admin_confirm(array $form, array &$form_state, $type, $action, Entity $entity) {
  switch ($type) {
    case 'server':
      switch ($action) {
        case 'disable':
          $text = array(
            t('Disable server @name', array('@name' => $entity->name)),
            t('Do you really want to disable this server?'),
            t('This will disable both the server and all associated indexes. ' .
                "Searches on these indexes won't be available until they are re-enabled."),
            t('The server and its indexes were successfully disabled.'),
          );
          break;
        case 'delete':
          if ($entity->hasStatus(ENTITY_OVERRIDDEN)) {
            $text = array(
              t('Revert server @name', array('@name' => $entity->name)),
              t('Do you really want to revert this server?'),
              t('This will revert all settings for this server back to the defaults. This action cannot be undone.'),
              t('The server settings have been successfully reverted.'),
            );
          }
          else {
            $text = array(
              t('Delete server @name', array('@name' => $entity->name)),
              t('Do you really want to delete this server?'),
              t('This will delete the server and disable all associated indexes. ' .
                  "Searches on these indexes won't be available until they are moved to another server and re-enabled."),
              t('The server was successfully deleted.'),
            );
          }
          break;
        default:
          return FALSE;
      }
      break;
    case 'index':
      switch ($action) {
        case 'disable':
          $text = array(
            t('Disable index @name', array('@name' => $entity->name)),
            t('Do you really want to disable this index?'),
            t("Searches on this index won't be available until it is re-enabled."),
            t('The index was successfully disabled.'),
          );
          break;
        case 'delete':
          if ($entity->hasStatus(ENTITY_OVERRIDDEN)) {
            $text = array(
              t('Revert index @name', array('@name' => $entity->name)),
              t('Do you really want to revert this index?'),
              t('This will revert all settings on this index back to the defaults. This action cannot be undone.'),
              t('The index settings have been successfully reverted.'),
            );
          }
          else {
            $text = array(
              t('Delete index @name', array('@name' => $entity->name)),
              t('Do you really want to delete this index?'),
              t('This will remove the index from the server and delete all settings. ' .
                  'All data on this index will be lost.'),
              t('The index has been successfully deleted.'),
            );
          }
          break;
        default:
          return FALSE;
      }
      break;
    default:
      return FALSE;
  }

  $form = array(
    'type' => array(
      '#type' => 'value',
      '#value' => $type,
    ),
    'action' => array(
      '#type' => 'value',
      '#value' => $action,
    ),
    'id' => array(
      '#type' => 'value',
      '#value' => $entity->machine_name,
    ),
    'message' => array(
      '#type' => 'value',
      '#value' => $text[3],
    ),
  );
  $desc = "<h3>{$text[1]}</h3><p>{$text[2]}</p>";
  return confirm_form($form, $text[0], "admin/config/search/search_api/$type/{$entity->machine_name}", $desc);
}

/**
 * Submit function for search_api_admin_confirm().
 */
function search_api_admin_confirm_submit(array $form, array &$form_state) {
  $values = $form_state['values'];

  $type = $values['type'];
  $action = $values['action'];
  $id = $values['id'];

  $function = "search_api_{$type}_{$action}";
  if ($function($id)) {
    drupal_set_message($values['message']);
  }
  else {
    drupal_set_message(t('An error has occurred while performing the desired action. Check the logs for details.'), 'error');
  }

  $form_state['redirect'] = $action == 'delete'
      ? "admin/config/search/search_api"
      : "admin/config/search/search_api/$type/$id";
}
