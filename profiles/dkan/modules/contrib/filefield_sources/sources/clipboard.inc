<?php

/**
 * @file
 * A FileField extension to allow transfer of files through the clipboard.
 *
 * The "hooks" in this file are not true hooks, they're called individually
 * from the main filefield_sources.module in the corresponding hook by the
 * same name. Any of these hooks could be broken out into a separate module.
 */

/**
 * Implements hook_filefield_source_info().
 */
function filefield_source_clipboard_info() {
  $source = array();
  $source['clipboard'] = array(
    'name' => t('Paste from clipboard (<a href="http://drupal.org/node/1775902">limited browser support</a>)'),
    'label' => t('Clipboard'),
    'description' => t('Allow users to paste a file directly from the clipboard.'),
    'process' => 'filefield_source_clipboard_process',
    'value' => 'filefield_source_clipboard_value',
    'weight' => 1,
    'file' => 'includes/clipboard.inc',
  );
  return $source;
}


/**
 * Implements hook_menu().
 */
function filefield_source_clipboard_menu() {
  $items = array();
  $items['file/clipboard/%/%/%'] = array(
    'page callback' => 'filefield_source_clipboard_page',
    'page arguments' => array(2, 3, 4),
    'access callback' => '_filefield_sources_field_access',
    'access arguments' => array(2, 3, 4),
    'file' => 'sources/clipboard.inc',
    'type' => MENU_CALLBACK,
  );
  return $items;
}

/**
 * Implements hook_theme().
 */
function filefield_source_clipboard_theme() {
  return array(
    'filefield_source_clipboard_element' => array(
      'render element' => 'element',
      'file' => 'sources/clipboard.inc',
    ),
  );
}

/**
 * A #process callback to extend the filefield_widget element type.
 */
function filefield_source_clipboard_process($element, &$form_state, $form) {
  // If settings are needed later:
  //$instance = field_widget_instance($element, $form_state);
  //$settings = $instance['widget']['settings']['filefield_sources']['source_clipboard'];

  $element['filefield_clipboard'] = array(
    '#weight' => 100.5,
    '#theme' => 'filefield_source_clipboard_element',
    '#filefield_source' => TRUE, // Required for proper theming.
    '#filefield_sources_hint_text' => t('Enter filename then paste.'),
    '#description' => filefield_sources_element_validation_help($element['#upload_validators']),
  );

  $element['filefield_clipboard']['filename'] = array(
    '#type' => 'hidden',
    '#attributes' => array('class' => array('filefield-source-clipboard-filename')),
  );
  $element['filefield_clipboard']['contents'] = array(
    '#type' => 'hidden',
    '#attributes' => array('class' => array('filefield-source-clipboard-contents')),
  );
  $element['filefield_clipboard']['upload'] = array(
    '#type' => 'submit',
    '#value' => t('Upload'),
    '#ajax' => array(
      'path' => 'file/ajax/' . implode('/', $element['#array_parents']) . '/' . $form['form_build_id']['#value'],
      'wrapper' => $element['#id'] . '-ajax-wrapper',
      'effect' => 'fade',
      'progress' => array(
        'type' => 'throbber',
        'message' => t('Transfering file...'),
      ),
    ),
    '#validate' => array(),
    '#submit' => array('filefield_sources_field_submit'),
    '#limit_validation_errors' => array($element['#parents']),
    '#attributes' => array('style' => 'display: none;'),
  );

  return $element;
}

/**
 * A #filefield_value_callback function.
 */
function filefield_source_clipboard_value(&$element, &$item) {
  if (isset($item['filefield_clipboard']['contents']) && strlen($item['filefield_clipboard']['contents']) > 0) {
    // Check that the destination is writable.
    $temporary_directory = 'temporary://';
    if (!file_prepare_directory($temporary_directory, FILE_MODIFY_PERMISSIONS)) {
      watchdog('file', 'The directory %directory is not writable, because it does not have the correct permissions set.', array('%directory' => drupal_realpath($temporary_directory)));
      drupal_set_message(t('The file could not be transferred because the temporary directory is not writable.'), 'error');
      return;
    }
    // Check that the destination is writable.
    $directory = $element['#upload_location'];
    $mode = variable_get('file_chmod_directory', 0775);

    // This first chmod check is for other systems such as S3, which don't work
    // with file_prepare_directory().
    if (!drupal_chmod($directory, $mode) && !file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
      watchdog('file', 'File %file could not be copied, because the destination directory %destination is not configured correctly.', array('%file' => $url, '%destination' => drupal_realpath($directory)));
      drupal_set_message(t('The specified file %file could not be copied, because the destination directory is not properly configured. This may be caused by a problem with file or directory permissions. More information is available in the system log.', array('%file' => $url)), 'error');
      return;
    }

    // Split the file information in mimetype and base64 encoded binary.
    $base64_data = $item['filefield_clipboard']['contents'];
    $comma_position = strpos($base64_data, ',');
    $semicolon_position = strpos($base64_data, ';');
    $file_contents = base64_decode(substr($base64_data, $comma_position + 1));
    $mimetype = substr($base64_data, 5, $semicolon_position - 5);

    include_once('./includes/file.mimetypes.inc');
    $mime_mapping = file_mimetype_mapping();
    $mime_key = array_search($mimetype, $mime_mapping['mimetypes']);
    $extension = array_search($mime_key, $mime_mapping['extensions']);

    $filename = trim($item['filefield_clipboard']['filename']);
    $filename = preg_replace('/\.[a-z0-9]{3,4}$/', '', $filename);
    $filename = (empty($filename) ? 'paste_' . REQUEST_TIME : $filename). '.' . $extension;
    $filepath = file_create_filename($filename, $temporary_directory);

    $copy_success = FALSE;
    if ($fp = @fopen($filepath, 'w')) {
      fwrite($fp, $file_contents);
      fclose($fp);
      $copy_success = TRUE;
    }

    if ($copy_success && $file = filefield_sources_save_file($filepath, $element['#upload_validators'], $element['#upload_location'])) {
      $item = array_merge($item, (array) $file);
    }

    // Remove the temporary file generated from paste.
    @unlink($filepath);
  }
}

/**
 * Handles the uploading of a file through a POST request.
 */
function filefield_source_clipboard_page($entity_type, $bundle_name, $field_name) {
  global $conf;

  // Check access.
  if (!$instance = field_info_instance($entity_type, $field_name, $bundle_name)) {
    return drupal_access_denied();
  }
  $field = field_info_field($field_name);

  module_load_include('inc', 'imce', 'inc/imce.page');
  return imce($field['settings']['uri_scheme']);
}

/**
 * Theme the output of the clipboard field.
 */
function theme_filefield_source_clipboard_element($variables) {
  $element = $variables['element'];

  $capture = '<div class="filefield-source-clipboard-capture" contenteditable="true"><span class="hint">example_filename.png</span></div>';
  $element['#field_suffix'] = drupal_render($element['upload']) . ' <span class="hint">' . t('ctrl + v') . '</span>';
  $element['#description'] = t('Enter a file name and paste an image from the clipboard. This feature only works in <a href="http://drupal.org/node/1775902">limited browsers</a>.');
  $element['#children'] = $capture . drupal_render_children($element);
  return '<div class="filefield-source filefield-source-clipboard clear-block">' . theme('form_element', array('element' => $element)) . '</div>';
}