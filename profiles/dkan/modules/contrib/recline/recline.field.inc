<?php

/**
 * @file
 * Implements Recline.js field, based on file module's file field.
 */

/**
 * Implements hook_field_info().
 */
function recline_field_info() {
  return array(
    'recline_field' => array(
      'label' => t('Recline.js vizualization'),
      'description' => t('Visualize data with Recline.js.'),
      'settings' => array(
        'uri_scheme' => variable_get('file_default_scheme', 'public'),
      ),
      'instance_settings' => array(
        'file_extensions' => 'csv',
        'file_directory' => '',
        'max_filesize' => '',
        'title_field' => 0,
        'alt_field' => 0,
      ),
      'property_type' => 'file',
      'default_widget' => 'recline_widget',
      'default_formatter' => 'recline_default_formatter',
    ),
  );
}

/**
 * Implements hook_field_formatter_info().
 */
function recline_field_formatter_info() {
  $formatters = array(
    'recline_default_formatter' => array(
      'label' => t('Recline.js visualization'),
      'field types' => array('recline_field', 'file'),
      // TODO: add some settings.
      'settings' => array(),
    ),
  );
  return $formatters;
}

/**
 * Implements hook_field_widget_info().
 */
function recline_field_widget_info() {
  return array(
    'recline_widget' => array(
      'label' => t('Recline upload'),
      'field types' => array('recline_field'),
      'settings' => array(
        'progress_indicator' => 'throbber',
      ),
      'behaviors' => array(
        'multiple values' => FIELD_BEHAVIOR_CUSTOM,
        'default value' => FIELD_BEHAVIOR_NONE,
      ),
    ),
  );
}

/**
 * Implements hook_field_instance_settings_form().
 */
function recline_field_instance_settings_form($field, $instance) {
  // TODO: only allow certain views from Recline.js here.
  $settings = $instance['settings'];

  // Use the file field instance settings form as a basis.
  $form = file_field_instance_settings_form($field, $instance);

  return $form;
}

/**
 * Implements hook_field_widget_form().
 */
function recline_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {

  $defaults = array(
    'fid' => 0,
    'display' => !empty($field['settings']['display_default']),
    'description' => '',
  );

  // Load the items for form rebuilds from the field state as they might not be
  // in $form_state['values'] because of validation limitations. Also, they are
  // only passed in as $items when editing existing entities.
  $field_state = field_form_get_state($element['#field_parents'], $field['field_name'], $langcode, $form_state);
  if (isset($field_state['items'])) {
    $items = $field_state['items'];
  }

  // Essentially we use the managed_file type, extended with some enhancements.
  $element_info = element_info('managed_file');
  $element += array(
    '#type' => 'managed_file',
    '#upload_location' => file_field_widget_uri($field, $instance),
    '#upload_validators' => file_field_widget_upload_validators($field, $instance),
    '#value_callback' => 'file_field_widget_value',
    '#process' => array_merge($element_info['#process'], array('file_field_widget_process')),
    '#progress_indicator' => $instance['widget']['settings']['progress_indicator'],
    // Allows this field to return an array instead of a single value.
    '#extended' => TRUE,
  );

  if ($field['cardinality'] == 1) {
    // Set the default value.
    $element['#default_value'] = !empty($items) ? $items[0] : $defaults;
    // If there's only one field, return it as delta 0.
    if (empty($element['#default_value']['fid'])) {
      $element['#description'] = theme('file_upload_help', array('description' => $element['#description'], 'upload_validators' => $element['#upload_validators']));
    }
    $elements = array($element);
  }
  else {
    // If there are multiple values, add an element for each existing one.
    foreach ($items as $item) {
      $elements[$delta] = $element;
      $elements[$delta]['#default_value'] = $item;
      $elements[$delta]['#weight'] = $delta;
      $delta++;
    }
    // And then add one more empty row for new uploads except when this is a
    // programmed form as it is not necessary.
    if (($field['cardinality'] == FIELD_CARDINALITY_UNLIMITED || $delta < $field['cardinality']) && empty($form_state['programmed'])) {
      $elements[$delta] = $element;
      $elements[$delta]['#default_value'] = $defaults;
      $elements[$delta]['#weight'] = $delta;
      $elements[$delta]['#required'] = ($element['#required'] && $delta == 0);
    }
    // The group of elements all-together need some extra functionality
    // after building up the full list (like draggable table rows).
    $elements['#file_upload_delta'] = $delta;
    $elements['#theme'] = 'file_widget_multiple';
    $elements['#theme_wrappers'] = array('fieldset');
    $elements['#process'] = array('file_field_widget_process_multiple');
    $elements['#title'] = $element['#title'];
    $elements['#description'] = $element['#description'];
    $elements['#field_name'] = $element['#field_name'];
    $elements['#language'] = $element['#language'];
    $elements['#display_field'] = $field['settings']['display_field'];

    // Add some properties that will eventually be added to the file upload
    // field. These are added here so that they may be referenced easily through
    // a hook_form_alter().
    $elements['#file_upload_title'] = t('Add a new file');
    $elements['#file_upload_description'] = theme('file_upload_help', array('description' => '', 'upload_validators' => $elements[0]['#upload_validators']));
  }
  $elements[$delta]['view'] = array(
    '#type' => 'fieldset',
    '#title' => t('Recline Views'),
    '#description' => t('These are views (as in MVC, not Drupal Views module) made available from Recline.js.'),
  );
  // Add option for which views are exposed.
  foreach (recline_view_options() as $view => $label) {
    // This gives us the value if someone clicks on a view and then uploads
    // a file. We lose any new values if someone removes the file. We
    // could fix this by writing our own insert function.
    // If file is already uploaded.
    if (isset($elements[$delta]['#default_value'][$view])) {
      $default_value = $elements[$delta]['#default_value'][$view];
    }
    // If file is already uploaded, but removed.
    elseif (isset($element['#entity']->{$element['#field_name']}) && $element['#entity']->{$element['#field_name']}) {
      $default_value = $element['#entity']->{$element['#field_name']}[$element['#language']][$delta][$view];
    }
    // If selections are made, then file is uploaded.
    elseif (isset($elements[$delta]['#default_value']['view'][$view])) {
      $default_value = $elements[$delta]['#default_value']['view'][$view];
    }
    else {
      $default_value = '';
    }
    $elements[$delta]['view'][$view] = array(
      '#title' => $label,
      '#type' => 'checkbox',
      '#weight' => 0,
      '#default_value' => $default_value,
    );
  }
  $embed_value = isset($elements[$delta]['#default_value']['embed']) ? $elements[$delta]['#default_value']['embed'] : '';

  $elements[$delta]['embed'] = array(
    '#title' => t('Embed'),
    '#description' => t('Provide an embed link?'),
    '#type' => 'checkbox',
    '#weight' => 5,
    '#default_value' => $embed_value,
  );

  $delimiter = isset($elements[$delta]['#default_value']['delimiter']) ? $elements[$delta]['#default_value']['delimiter'] : FALSE;

  $elements[$delta]['delimiter'] = array(
    '#title' => 'Delimiter',
    '#description' => t('Select delimiter for file if applicable.'),
    '#type' => 'select',
    '#options' => recline_delimiters(),
    '#weight' => 1,
    '#default_value' => $delimiter,
  );

  return $elements;
}

/**
 * Adds columns to the field schema.
 */
function recline_add_field_columns(&$columns) {
  drupal_alter('recline_field_columns', $columns);
}


/**
 * Implements hook_field_load().
 */
function recline_field_load($entity_type, $entities, $field, $instances, $langcode, &$items, $age) {
  file_field_load($entity_type, $entities, $field, $instances, $langcode, $items, $age);
}

/**
 * Implements hook_field_is_empty().
 */
function recline_field_is_empty($item, $field) {
  return file_field_is_empty($item, $field);
}

/**
 * Implements hook_field_presave().
 */
function recline_field_presave($entity_type, $entity, $field, $instance, $langcode, &$items) {
  // In the field definition we've put the views in their own fieldset. Here we
  // put them back into the same level as the rest of the results.
  foreach ($items as $delta => $item) {
    if (isset($item['view'])) {
      foreach ($item['view'] as $view_name => $view_value) {
        $items[$delta][$view_name] = $view_value;
      }
    }
  }
  file_field_presave($entity_type, $entity, $field, $instance, $langcode, $items);
}

/**
 * Implements hook_field_insert().
 */
function recline_field_insert($entity_type, $entity, $field, $instance, $langcode, &$items) {
  file_field_insert($entity_type, $entity, $field, $instance, $langcode, $items);
}

/**
 * Implements hook_field_update().
 */
function recline_field_update($entity_type, $entity, $field, $instance, $langcode, &$items) {
  file_field_update($entity_type, $entity, $field, $instance, $langcode, $items);
}

/**
 * Implements hook_field_delete().
 */
function recline_field_delete($entity_type, $entity, $field, $instance, $langcode, &$items) {
  file_field_delete($entity_type, $entity, $field, $instance, $langcode, $items);
}

/**
 * Implements hook_field_formatter_view().
 */
function recline_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {

  $element = array();

  foreach ($items as $delta => $item) {
    $item['entity'] = $entity;
    $element[$delta] = array(
      '#theme' => 'recline_default_formatter',
      '#item' => $item,
    );
  }

  return $element;
}

/**
 * Returns HTML for an recline field formatter.
 *
 * @param array $variables
 *   An associative array containing:
 *   - item: Associative array of recline field
 *
 * @ingroup themeable
 */
function theme_recline_default_formatter($variables) {
  $output = recline_default_formatter_output($variables);
  return drupal_render($output);
}

/**
 * Builds output render array for field formatter.
 */
function recline_default_formatter_output($variables) {
  $file = $variables['item'];

  $url = file_create_url($file['uri']);

  $icon = '';

  $format = recline_get_data_type($file['filemime']);

  // Set options as per anchor format described at
  // http://microformats.org/wiki/file-format-examples
  $options = array(
    'attributes' => array(
      'type' => $file['filemime'] . '; length=' . $file['filesize'],
      'data-format' => $format,
      'class' => array('format-label'),
    ),
  );

  // Use the description as the link text if available.
  if (empty($file['description'])) {
    $link_text = $file['filename'];
  }
  else {
    $link_text = $file->description;
    $options['attributes']['title'] = check_plain($file['filename']);
  }
  $dkan = $table = FALSE;
  // See if datastore is loaded, if so, prepare recline to view from it.
  if (module_exists('dkan_datastore_api') && module_exists('feeds_flatstore_processor') && function_exists('dkan_datastore_api_get_feeds_source')) {
    $source_id = dkan_datastore_api_get_feeds_source($variables['item']['entity']->nid);
    if ($table = feeds_flatstore_processor_table_name($source_id, $variables['item']['entity']->nid)) {
      if (db_table_exists($table)) {
        $dkan = $table;
      }
    }
  }
  $recline = '';
  if (isset($variables['item']['grid']) &&
    isset($variables['item']['graph']) &&
    isset($variables['item']['map']) &&
    isset($variables['item']['embed'])) {
    if ($variables['item']['map'] || $variables['item']['grid'] || $variables['item']['graph'] || $variables['item']['embed']) {
      $uuid = isset($variables['item']['entity']->uuid) ? $variables['item']['entity']->uuid : FALSE;
      drupal_add_library('system', 'ui.sortable');
      $recline = libraries_load('recline');
      $module_path = drupal_get_path('module', 'recline');
      drupal_add_js($module_path . '/backend.ckan_get.js');
      drupal_add_js($module_path . '/recline.app.js');
      drupal_add_css($module_path . '/recline.css');
      $file_path = isset($variables['item']['uri']) ? file_create_url($variables['item']['uri']) : "";
      $settings['recline'] = array(
        'file' => $file_path,
        'delimiter' => $variables['item']['delimiter'],
        'grid' => (int) $variables['item']['grid'],
        'graph' => (int) $variables['item']['graph'],
        'map' => (int) $variables['item']['map'],
        'embed' => (int) $variables['item']['embed'],
        'uuid' => $uuid,
        'fileType' => $variables['item']['filemime'],
        'dkan' => $dkan,
      );
      drupal_add_js($settings, 'setting');
    }
  }
  else {
    $uuid = isset($variables['item']['entity']->uuid) ? $variables['item']['entity']->uuid : FALSE;
    drupal_add_library('system', 'ui.sortable');
    $recline = libraries_load('recline');
    $module_path = drupal_get_path('module', 'recline');
    drupal_add_js($module_path . '/backend.ckan_get.js');
    drupal_add_js($module_path . '/recline.app.js');
    drupal_add_css($module_path . '/recline.css');
    $file_path = isset($variables['item']['uri']) ? file_create_url($variables['item']['uri']) : "";
    $delimiter = isset($variables['item']['delimiter']) ? $variables['item']['delimiter'] : ",";
    $settings['recline'] = array(
      'file' => $file_path,
      'delimiter' => $delimiter,
      'uuid' => $uuid,
      'grid' => 1,
      'graph' => 1,
      'map' => 1,
      'embed' => 1,
      'fileType' => $variables['item']['filemime'],
      'dkan' => $dkan,
    );
    drupal_add_js($settings, 'setting');
  }
  if ($recline) {
    // Necessary to fix https://github.com/mapbox/mapbox.js/issues/339
    $leaflet_imgs = $recline['library path'] . '/vendor/leaflet/0.4.4/images';
    drupal_add_js('L.Icon.Default.imagePath = "/' . $leaflet_imgs . '"', 'inline');
  }

  $file = l($file['filename'], $url, $options);

  $node = $variables['item']['entity'];
  $embed = theme('recline_embed_button', array('node' => $node));

  $output = array(
    'download' => array(
      '#type' => 'markup',
      '#markup' => '<div class="download">' . $file . '</div>',
    ),
  );
  if ($settings['recline']['embed']){
    $output['embed'] = array(
      '#type' => 'markup',
      '#markup' => $embed,
    );
  }
  $output['data_explorer'] = array(
    '#type' => 'markup',
    '#markup' => '<span class="data-explorer"></span>',
  );
  return $output;
}

/**
 * Implements hook_preprocess_HOOK().
 *
 * Process variables for recline-embed-button.tpl.php.
 */
function template_preprocess_recline_embed_button(&$variables) {
  $node = $variables['node'];
  $variables['embed_url'] = url('node/' . $node->nid . '/recline-embed', array('absolute' => TRUE));
}

/**
 * Returns HTML for an recline field widget.
 *
 * @param array $variables
 *   An associative array containing:
 *   - item: Associative array of recline field
 *
 * @ingroup themeable
 */
function theme_recline_widget($variables) {
  $element = $variables['element'];

  $output = '';
  $output .= '<div class="recline-widget form-managed-file clearfix">';
  $output .= drupal_render_children($element);
  $output .= '</div>';

  return $output;
}

/**
 * Returns array of possible view types supported by Recline.
 */
function recline_view_options() {
  $options = array(
    'grid' => t('Grid'),
    'graph' => t('Graph'),
    'map' => t('Map'),
  );
  return $options + module_invoke_all('recline_view_options');
}
