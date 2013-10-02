<?php

/**
 * @file
 * A FileField extension to allow use of files within a server directory.
 *
 * The "hooks" in this file are not true hooks, they're called individually
 * from the main filefield_sources.module in the corresponding hook by the
 * same name. Any of these hooks could be broken out into a separate module.
 */

/**
 * Implements hook_filefield_source_info().
 */
function filefield_source_attach_info() {
  $source = array();
  $source['attach'] = array(
    'name' => t('File attach from server directory'),
    'label' => t('File attach'),
    'description' => t('Select a file from a directory on the server.'),
    'process' => 'filefield_source_attach_process',
    'value' => 'filefield_source_attach_value',
    'weight' => 3,
    'file' => 'includes/attach.inc',
  );
  return $source;
}

/**
 * Implements hook_theme().
 */
function filefield_source_attach_theme() {
  return array(
    'filefield_source_attach_element' => array(
      'render element' => 'element',
      'file' => 'sources/attach.inc',
    ),
  );
}

/**
 * Implements hook_filefield_source_settings().
 */
function filefield_source_attach_settings($op, $instance) {
  $return = array();

  if ($op == 'form') {
    $settings = $instance['widget']['settings']['filefield_sources'];

    $return['source_attach'] = array(
      '#title' => t('File attach settings'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#description' => t('File attach allows for selecting a file from a directory on the server, commonly used in combination with FTP.') . ' <strong>' . t('This file source will ignore file size checking when used.') . '</strong>',
      '#element_validate' => array('_filefield_source_attach_file_path_validate'),
      '#weight' => 3,
    );
    $return['source_attach']['path'] = array(
      '#type' => 'textfield',
      '#title' => t('File attach path'),
      '#default_value' => $settings['source_attach']['path'],
      '#size' => 60,
      '#maxlength' => 128,
      '#description' => t('The directory within the <em>File attach location</em> that will contain attachable files.'),
    );
    if (module_exists('token')) {
      $return['source_attach']['tokens'] = array(
        '#theme' => 'token_tree',
        '#token_types' => array('user'),
      );
    }
    $return['source_attach']['absolute'] = array(
      '#type' => 'radios',
      '#title' => t('File attach location'),
      '#options' => array(
        0 => t('Within the files directory'),
        1 => t('Absolute server path'),
      ),
      '#default_value' => $settings['source_attach']['absolute'],
      '#description' => t('The <em>File attach path</em> may be with the files directory (%file_directory) or from the root of your server. If an absolute path is used and it does not start with a "/" your path will be relative to your site directory: %realpath.', array('%file_directory' => drupal_realpath(file_default_scheme() . '://'), '%realpath' => realpath('./'))),
    );
    $return['source_attach']['attach_mode'] = array(
      '#type' => 'radios',
      '#title' => t('Attach method'),
      '#options' => array(
        'move' => t('Move the file directly to the final location'),
        'copy' => t('Leave a copy of the file in the attach directory'),
      ),
      '#default_value' => isset($settings['source_attach']['attach_mode']) ? $settings['source_attach']['attach_mode'] : 'move',
    );
  }
  elseif ($op == 'save') {
    $return['source_attach']['path'] = 'file_attach';
    $return['source_attach']['absolute'] = 0;
    $return['source_attach']['attach_mode'] = 'move';
  }

  return $return;
}

function _filefield_source_attach_file_path_validate($element, &$form_state) {
  // Only validate if this source is enabled.
  if (!$form_state['values']['instance']['widget']['settings']['filefield_sources']['filefield_sources']['attach']) {
    return;
  }

  // Strip slashes from the end of the file path.
  $filepath = rtrim($element['path']['#value'], '\\/');
  form_set_value($element['path'], $filepath, $form_state);
  $filepath = _filefield_source_attach_directory($form_state['values']['instance']);

  // Check that the directory exists and is writable.
  if (!file_prepare_directory($filepath, FILE_CREATE_DIRECTORY)) {
    form_error($element['path'], t('Specified file attach path must exist or be writable.'));
  }
}

/**
 * A #process callback to extend the filefield_widget element type.
 */
function filefield_source_attach_process($element, &$form_state, $form) {
  $instance = field_widget_instance($element, $form_state);
  $settings = $instance['widget']['settings']['filefield_sources']['source_attach'];

  $element['filefield_attach'] = array(
    '#weight' => 100.5,
    '#theme' => 'filefield_source_attach_element',
    '#filefield_source' => TRUE, // Required for proper theming.
  );

  $path = _filefield_source_attach_directory($instance);
  $options = _filefield_source_attach_options($path);

  // If we have built this element before, append the list of options that we
  // had previously. This allows files to be deleted after copying them and
  // still be considered a valid option during the validation and submit.
  if (!isset($form_state['triggering_element']) && isset($form_state['filefield_sources'][$instance['field_name']]['attach_options'])) {
    $options = $options + $form_state['filefield_sources'][$instance['field_name']]['attach_options'];
  }
  // On initial form build and rebuilds after processing input, save the
  // original list of options so they can be restored in the line above.
  else {
    $form_state['filefield_sources'][$instance['field_name']]['attach_options'] = $options;
  }

  $description = t('This method may be used to attach files that exceed the file size limit. Files may be attached from the %directory directory on the server, usually uploaded through FTP.', array('%directory' => realpath($path)));

  // Error messages.
  if ($options === FALSE || empty($settings['path'])) {
    $attach_message = t('A file attach directory could not be located.');
    $attach_description = t('Please check your settings for the %field field.',  array('%field' => $instance['label']));
  }
  elseif (!count($options)) {
    $attach_message = t('There currently are no files to attach.');
    $attach_description = $description;
  }

  if (isset($attach_message)) {
    $element['filefield_attach']['attach_message'] = array(
      '#markup' => $attach_message,
    );
    $element['filefield_attach']['#description'] = $attach_description;
  }
  else {
    $validators = $element['#upload_validators'];
    if (isset($validators['file_validate_size'])) {
      unset($validators['file_validate_size']);
    }
    $description .= '<br />' . filefield_sources_element_validation_help($validators);
    $element['filefield_attach']['filename'] = array(
      '#type' => 'select',
      '#options' => $options,
    );
    $element['filefield_attach']['#description'] = $description;
  }

  $element['filefield_attach']['attach'] = array(
    '#name' => implode('_', $element['#array_parents']) . '_attach',
    '#type' => 'submit',
    '#value' => t('Attach'),
    '#validate' => array(),
    '#submit' => array('filefield_sources_field_submit'),
    '#limit_validation_errors' => array($element['#parents']),
    '#ajax' => array(
      'path' => 'file/ajax/' . implode('/', $element['#array_parents']) . '/' . $form['form_build_id']['#value'],
      'wrapper' => $element['#id'] . '-ajax-wrapper',
       'method' => 'replace',
       'effect' => 'fade',
    ),
  );

  return $element;
}

function _filefield_source_attach_options($path) {
  if (!file_prepare_directory($path, FILE_CREATE_DIRECTORY)) {
    drupal_set_message(t('Specified file attach path must exist or be writable.'), 'error');
    return FALSE;
  }

  $options = array();
  $file_attach = file_scan_directory($path, '/.*/', array('key' => 'filename'), 0);

  if (count($file_attach)) {
    $options = array('' => t('-- Select file --'));
    foreach ($file_attach as $filename => $fileinfo) {
      $filename = basename($filename);
      $options[$fileinfo->uri] = str_replace($path . '/', '', $fileinfo->uri);
    }
  }

  natcasesort($options);
  return $options;
}

/**
 * A #filefield_value_callback function.
 */
function filefield_source_attach_value($element, &$item) {
  if (!empty($item['filefield_attach']['filename'])) {
    $instance = field_info_instance($element['#entity_type'], $element['#field_name'], $element['#bundle']);
    $filepath = $item['filefield_attach']['filename'];

    // Check that the destination is writable.
    $directory = $element['#upload_location'];
    $mode = variable_get('file_chmod_directory', 0775);

    // This first chmod check is for other systems such as S3, which don't work
    // with file_prepare_directory().
    if (!drupal_chmod($directory, $mode) && !file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
      watchdog('file', 'File %file could not be copied, because the destination directory %destination is not configured correctly.', array('%file' => $filepath, '%destination' => drupal_realpath($directory)));
      drupal_set_message(t('The specified file %file could not be copied, because the destination directory is not properly configured. This may be caused by a problem with file or directory permissions. More information is available in the system log.', array('%file' => $filepath)), 'error');
      return;
    }

    // Clean up the file name extensions and transliterate.
    $original_filepath = $filepath;
    $new_filepath = filefield_sources_clean_filename($filepath, $instance['settings']['file_extensions']);
    rename($filepath, $new_filepath);
    $filepath = $new_filepath;

    // Run all the normal validations, minus file size restrictions.
    $validators = $element['#upload_validators'];
    if (isset($validators['file_validate_size'])) {
      unset($validators['file_validate_size']);
    }

    // Save the file to the new location.
    if ($file = filefield_sources_save_file($filepath, $validators, $directory)) {
      $item = array_merge($item, (array) $file);

      // Delete the original file if "moving" the file instead of copying.
      if ($instance['widget']['settings']['filefield_sources']['source_attach']['attach_mode'] !== 'copy') {
        @unlink($filepath);
      }
    }

    // Restore the original file name if the file still exists.
    if (file_exists($filepath) && $filepath != $original_filepath) {
      rename($filepath, $original_filepath);
    }

    $item['filefield_attach']['filename'] = '';
  }
}

/**
 * Theme the output of the autocomplete field.
 */
function theme_filefield_source_attach_element($variables) {
  $element = $variables['element'];

  if (isset($element['attach_message'])) {
    $output = $element['attach_message']['#markup'];
  }
  else {
    $select = '';
    $size = !empty($element['filename']['#size']) ? ' size="' . $element['filename']['#size'] . '"' : '';
    _form_set_class($element['filename'], array('form-select'));
    $multiple = !empty($element['#multiple']);
    $output = '<select name="'. $element['filename']['#name'] .''. ($multiple ? '[]' : '') .'"'. ($multiple ? ' multiple="multiple" ' : '') . drupal_attributes($element['filename']['#attributes']) .' id="'. $element['filename']['#id'] .'" '. $size .'>'. form_select_options($element['filename']) .'</select>';
  }
  $output .= drupal_render($element['attach']);
  $element['#children'] = $output;
  return '<div class="filefield-source filefield-source-attach clear-block">' . theme('form_element', array('element' => $element)) . '</div>';
}

function _filefield_source_attach_directory($instance, $account = NULL) {
  $field = field_info_field($instance['field_name']);
  $account = isset($account) ? $account : $GLOBALS['user'];
  $path = $instance['widget']['settings']['filefield_sources']['source_attach']['path'];
  $absolute = !empty($instance['widget']['settings']['filefield_sources']['source_attach']['absolute']);

  // Replace user level tokens.
  // Node level tokens require a lot of complexity like temporary storage
  // locations when values don't exist. See the filefield_paths module.
  if (module_exists('token')) {
    $path = token_replace($path, array('user' => $account));
  }

  return $absolute ? $path : file_default_scheme() . '://' . $path;
}
