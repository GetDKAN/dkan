<?php

/**
 * @file
 * A FileField extension to allow referencing of files from IMCE.
 *
 * The "hooks" in this file are not true hooks, they're called individually
 * from the main filefield_sources.module in the corresponding hook by the
 * same name. Any of these hooks could be broken out into a separate module.
 */

/**
 * Implements hook_filefield_source_info().
 */
function filefield_source_imce_info() {
  $source = array();

  if (!module_exists('imce') || !imce_access()) {
    return $source;
  }

  $source['imce'] = array(
    'name' => t('IMCE file browser'),
    'label' => t('File browser'),
    'description' => t('Select a file to use from a file browser.'),
    'process' => 'filefield_source_imce_process',
    'value' => 'filefield_source_imce_value',
    'weight' => -1,
    'file' => 'includes/imce.inc',
  );
  return $source;
}

/**
 * Implements hook_menu().
 */
function filefield_source_imce_menu() {
  $items = array();
  $items['file/imce/%/%/%'] = array(
    'page callback' => 'filefield_source_imce_page',
    'page arguments' => array(2, 3, 4),
    'access callback' => '_filefield_sources_field_access',
    'access arguments' => array(2, 3, 4),
    'file' => 'sources/imce.inc',
    'type' => MENU_CALLBACK,
  );
  return $items;
}

/**
 * Implements hook_theme().
 */
function filefield_source_imce_theme() {
  return array(
    'filefield_source_imce_element' => array(
      'render element' => 'element',
      'file' => 'sources/imce.inc',
    ),
 );
}

/**
 * Implements hook_filefield_source_settings().
 */
function filefield_source_imce_settings($op, $instance) {
  $return = array();

  if ($op == 'form') {
    $settings = $instance['widget']['settings']['filefield_sources'];

    $return['source_imce'] = array(
      '#title' => t('IMCE file browser settings'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#access' => module_exists('imce'),
    );

    $return['source_imce']['imce_mode'] = array(
      '#type' => 'radios',
      '#title' => t('File browser mode'),
      '#options' => array(
        0 => t('Restricted: Users can only browse the field directory. No file operations are allowed.'),
        1 => t('Full: Browsable directories are defined by <a href="!imce-admin-url">IMCE configuration profiles</a>. File operations are allowed.', array('!imce-admin-url' => url('admin/config/media/imce'))),
      ),
      '#default_value' => isset($settings['source_imce']['imce_mode']) ? $settings['source_imce']['imce_mode'] : 0,
    );
  }
  elseif ($op == 'save') {
    $return['source_imce']['imce_mode'] = 0;
  }

  return $return;

}

/**
 * A #process callback to extend the filefield_widget element type.
 */
function filefield_source_imce_process($element, &$form_state, $form) {
  $instance = field_widget_instance($element, $form_state);

  $element['filefield_imce'] = array(
    '#weight' => 100.5,
    '#theme' => 'filefield_source_imce_element',
    '#filefield_source' => TRUE, // Required for proper theming.
    '#description' => filefield_sources_element_validation_help($element['#upload_validators']),
  );

  $filepath_id = $element['#id'] . '-imce-path';
  $display_id = $element['#id'] . '-imce-display';
  $select_id = $element['#id'] . '-imce-select';
  $element['filefield_imce']['file_path'] = array(
    // IE doesn't support onchange events for hidden fields, so we use a
    // textfield and hide it from display.
    '#type' => 'textfield',
    '#value' => '',
    '#attributes' => array(
      'id' => $filepath_id,
      'onblur' => "if (this.value.length > 0) { jQuery('#$select_id').triggerHandler('mousedown'); }",
      'style' => 'position:absolute; left: -9999em',
    ),
  );

  $imce_function = 'window.open(\'' . url('file/imce/' . $element['#entity_type']  . '/' . $element['#bundle'] . '/' . $element['#field_name'], array('query' => array('app' => $instance['label'] . '|url@' . $filepath_id))) . '\', \'\', \'width=760,height=560,resizable=1\'); return false;';
  $element['filefield_imce']['display_path'] = array(
    '#type' => 'markup',
    '#markup' => '<span id="' . $display_id . '" class="filefield-sources-imce-display">' . t('No file selected') . '</span> (<a class="filefield-sources-imce-browse" href="#" onclick="' . $imce_function . '">' . t('browse') . '</a>)',
  );

  $element['filefield_imce']['select'] = array(
    '#name' => implode('_', $element['#array_parents']) . '_imce_select',
    '#type' => 'submit',
    '#value' => t('Select'),
    '#validate' => array(),
    '#submit' => array('filefield_sources_field_submit'),
    '#limit_validation_errors' => array($element['#parents']),
    '#name' => $element['#name'] . '[filefield_imce][button]',
    '#id' => $select_id,
    '#attributes' => array('style' => 'display: none;'),
    '#ajax' => array(
      'path' => 'file/ajax/' . implode('/', $element['#array_parents']) . '/' . $form['form_build_id']['#value'],
      'wrapper' => $element['#id'] . '-ajax-wrapper',
      'method' => 'replace',
      'effect' => 'fade',
    ),
  );

  return $element;
}

/**
 * A #filefield_value_callback function.
 */
function filefield_source_imce_value($element, &$item) {
  if (isset($item['filefield_imce']['file_path']) && $item['filefield_imce']['file_path'] != '') {
    $field = field_info_field($element['#field_name']);

    $scheme = $field['settings']['uri_scheme'];
    $wrapper = file_stream_wrapper_get_instance_by_scheme($scheme);
    $file_directory_prefix = $scheme == 'private' ? 'system/files' : $wrapper->getDirectoryPath();
    $uri = preg_replace('/^' . preg_quote(base_path() . $file_directory_prefix . '/', '/') . '/', $scheme . '://', $item['filefield_imce']['file_path']);

    // Resolve the file path to an FID.
    $fid = db_select('file_managed', 'f')
      ->condition('uri', rawurldecode($uri))
      ->fields('f', array('fid'))
      ->execute()
      ->fetchField();
    if ($fid) {
      $file = file_load($fid);
      if (filefield_sources_element_validate($element, $file)) {
        $item = array_merge($item, (array) $file);
      }
    }
    else {
      form_error($element, t('The selected file could not be used because the file does not exist in the database.'));
    }
    // No matter what happens, clear the value from the file path field.
    $item['filefield_imce']['file_path'] = '';
  }
}

/**
 * Theme the output of the autocomplete field.
 */
function theme_filefield_source_imce_element($variables) {
  $element = $variables['element'];

  $output = drupal_render_children($element);;
  return '<div class="filefield-source filefield-source-imce clear-block">' . $output . '</div>';
}

/**
 * Outputs the IMCE browser for FileField.
 */
function filefield_source_imce_page($entity_type, $bundle_name, $field_name) {
  global $conf;

  // Check access.
  if (!module_exists('imce') || !imce_access() || !$instance = field_info_instance($entity_type, $field_name, $bundle_name)) {
    return drupal_access_denied();
  }
  $field = field_info_field($field_name);

  // Full mode
  if (!empty($instance['widget']['settings']['filefield_sources']['source_imce']['imce_mode'])) {
    $conf['imce_custom_scan'] = 'filefield_source_imce_custom_scan_full';
  }
  // Restricted mode
  else {
    $conf['imce_custom_scan'] = 'filefield_source_imce_custom_scan_restricted';
    $conf['imce_custom_field'] = $field + array('_uri' => file_field_widget_uri($field, $instance));
  }

  // Disable absolute URLs.
  $conf['imce_settings_absurls'] = 0;

  module_load_include('inc', 'imce', 'inc/imce.page');
  return imce($field['settings']['uri_scheme']);
}

/**
 * Scan and return files, subdirectories, and total size for "full" mode.
 */
function filefield_source_imce_custom_scan_full($dirname, &$imce) {
  // Get a list of files in the database for this directory.
  $scheme = $imce['scheme'];
  $sql_uri_name = $dirname == '.' ? $scheme . '://' : $scheme . '://' . $dirname . '/';

  $result = db_select('file_managed', 'f')
    ->fields('f', array('uri'))
    ->condition('f.uri', $sql_uri_name . '%', 'LIKE')
    ->condition('f.uri', $sql_uri_name . '_%/%', 'NOT LIKE')
    ->execute();

  $db_files = array();
  foreach ($result as $row) {
    $db_files[basename($row->uri)] = 1;
  }

  // Get the default IMCE directory scan, then filter down to database files.
  $directory = imce_scan_directory($dirname, $imce);
  foreach ($directory['files'] as $filename => $file) {
    if (!isset($db_files[$filename])) {
      unset($directory['files'][$filename]);
      $directory['dirsize'] -= $file['size'];
    }
  }

  return $directory;
}

/**
 * Scan directory and return file list, subdirectories, and total size for Restricted Mode.
 */
function filefield_source_imce_custom_scan_restricted($dirname, &$imce) {
  $field = $GLOBALS['conf']['imce_custom_field'];
  $root = $imce['scheme'] . '://';
  $field_uri = $field['_uri'];
  $is_root = $field_uri == $root;

  // Process IMCE. Make field directory the only accessible one.
  $imce['dir'] = $is_root ? '.' : substr($field_uri, strlen($root));
  $imce['directories'] = array();
  if (!empty($imce['perm'])) {
    filefield_source_imce_disable_perms($imce, array('browse'));
  }

  // Create directory info
  $directory = array('dirsize' => 0, 'files' => array(), 'subdirectories' => array(), 'error' => FALSE);

  if (isset($field['storage']['details']['sql']['FIELD_LOAD_CURRENT'])) {
    $storage = $field['storage']['details']['sql']['FIELD_LOAD_CURRENT'];
    $table_info = reset($storage);
    $table = key($storage);
    $sql_uri = $field_uri . ($is_root ? '' : '/');
    $query = db_select($table, 'cf');
    $query->innerJoin('file_managed', 'f', 'f.fid = cf.' . $table_info['fid']);
    $result = $query->fields('f')
      ->condition('f.status', 1)
      ->condition('f.uri', $sql_uri . '%', 'LIKE')
      ->condition('f.uri', $sql_uri . '%/%', 'NOT LIKE')
      ->execute();
    foreach ($result as $file) {
      // Get real name
      $name = basename($file->uri);
      // Get dimensions
      $width = $height = 0;
      if ($img = imce_image_info($file->uri)) {
        $width = $img['width'];
        $height = $img['height'];
      }
      $directory['files'][$name] = array(
        'name' => $name,
        'size' => $file->filesize,
        'width' => $width,
        'height' => $height,
        'date' => $file->timestamp,
      );
      $directory['dirsize'] += $file->filesize;
    }
  }

   return $directory;
 }

/**
 * Disable IMCE profile permissions.
 */
function filefield_source_imce_disable_perms(&$imce, $exceptions = array()) {
  $disable_all = empty($exceptions);
  foreach ($imce['perm'] as $name => $val) {
    if ($disable_all || !in_array($name, $exceptions)) {
      $imce['perm'][$name] = 0;
    }
  }
  $imce['directories'][$imce['dir']] = array('name' => $imce['dir']) + $imce['perm'];
}
