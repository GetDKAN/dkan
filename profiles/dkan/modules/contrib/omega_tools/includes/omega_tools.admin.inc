<?php

/**
 * @todo
 */
function omega_tools_theme_reset_confirm($form, &$form_state, $theme) {
  $themes = list_themes();
  $form_state['theme'] = $theme;
  
  return confirm_form($form, 
    t('Are you sure you want to reset the theme %name?', array('%name' => $themes[$theme]->info['name'])), 
    'admin/appearance/settings/' . $theme, 
    t('<p>You are about to purge the theme settings stored in the database for this theme.</p><p>This will revert to whatever information is set in the .info file, and erase any un-exported customizations.</p>'),
    t('Reset'), 
    t('Cancel')
  );
}

/**
 * @todo
 */
function omega_tools_theme_reset_confirm_submit($form, &$form_state) {
  $themes = list_themes();
  $theme = $form_state['theme'];

  omega_tools_revert_theme_settings($theme);

  $form_state['redirect'] = 'admin/appearance/settings/' . $theme;
}

/**
 * @todo
 */
function omega_tools_theme_download($subtheme) {  
  file_download('temporary', file_uri_target(omega_tools_write_archive(drupal_get_path('theme', $subtheme), $subtheme)));
}

/**
 * @todo
 */
function omega_tools_theme_export($form, &$form_state, $theme) {
  $form_state['theme'] = $theme;  
  $info = drupal_parse_info_file(drupal_get_path('theme', $theme) . '/' . $theme . '.info');
  $settings = isset($info['settings']) ? $info['settings'] : array();
  $settings = variable_get('theme_' . $theme . '_settings', $settings);
  $editable = _omega_tools_is_editable($theme);
  
  $form['settings'] = array(
    '#type' => 'textarea',
    '#title' => t('Theme settings'),
    '#description' => $editable ? t('You may now edit the theme settings before proceeding with the export and eventually saving them to the .info file of your theme. The theme settings provided in this form will be written into the .info file of the %name theme. All settings that are currently present in that file will be overwritten.', array('%name' => $info['name'])) : t("This theme is not editable and therefore you won't be able to export the theme settings automatically."),
    '#default_value' => omega_tools_build_info_file($settings, 'settings'),
    '#rows' => 30,
    '#element_validate' => array('_omega_tools_validate_theme_settings'),
  );
  
  $form['revert'] = array(
    '#type' => 'checkbox',
    '#title' => t('Revert theme settings'),
    '#description' => t('This will purge the theme settings from the database after exporting them to the .info file of this theme.'),
    '#default_value' => TRUE,
    '#access' => $editable,
  );
  
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Export'),
    '#access' => $editable,
  );
  
  $form['actions']['cancel'] = array(
    '#markup' => l(t('Cancel'), 'admin/appearance/settings/' . $theme),
  );  
  
  return $form;
}

/**
 * @todo
 */
function omega_tools_theme_export_submit($form, &$form_state) {
  $theme = $form_state['theme'];  
  $info = drupal_parse_info_file(drupal_get_path('theme', $theme) . '/' . $theme . '.info');
  
  unset($info['settings']);
  
  omega_tools_write_info_file($theme, array_merge($info, drupal_parse_info_format($form_state['values']['settings'])));
  
  watchdog('theme', t('Theme settings for %theme exported and saved to .info file.', array('%theme' => $info['name'])));
  drupal_set_message(t('The theme settings for %theme have been exported and saved to the corresponding .info file.', array('%theme' => $info['name'])));

  if ($form_state['values']['revert']) {
    omega_tools_revert_theme_settings($theme);
  }
  
  $form_state['redirect'] = 'admin/appearance/settings/' . $theme;
}

/**
 * @todo
 */
function omega_tools_subtheme_add($form, &$form_state) {
  system_rebuild_theme_data();
  
  $themes = omega_tools_base_themes();
  $starterkits = array();
  
  if (!empty($themes)) {
    $base = isset($form_state['values']['base']) ? $form_state['values']['base'] : 'omega';
    $starterkits = omega_tools_starterkits($base);
    $starter = isset($form_state['values']['starterkit']) ? $form_state['values']['starterkit'] : key($starterkits);
    $starter = !empty($starter) ? $starter : '_none';
  }
  
  $form['info'] = array(
    '#type' => 'fieldset',
    '#title' => t('Theme information'),
  );
  
  $form['info']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#description' => t('The human-readable name for this theme.'),
    '#default_value' => isset($form_state['values']['name']) ? $form_state['values']['name'] : '',
    '#size' => 30,
    '#required' => TRUE,
  );
  
  $form['info']['machine_name'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($form_state['values']['machine_name']) ? $form_state['values']['machine_name'] : '',
    '#maxlength' => 30,
    '#machine_name' => array(
      'source' => array('info', 'name'),
    ),
    '#element_validate' => array('_omega_tools_validate_theme_name'),
  );
  
  $form['info']['automated'] = array(
    '#type' => 'checkbox',
    '#title' => t('Install automatically'),
    '#description' => t('Please select wether you want to install this theme manually or if it should be automatically placed in the selected destination. This feature requires you to have write permission for the selected destination.'),
    '#default_value' => TRUE,
    '#element_validate' => array('omega_tools_subtheme_validate_automation'),
  );
  
  $form['info']['destination'] = array(
    '#type' => 'select',
    '#title' => t('Destination'),
    '#description' => t('The theme will be place in the path selected here.'),
    '#options' => omega_tools_sites_options(),
    '#default_value' => 'all',
    '#states' => array(
      'visible' => array(
        ':input[name="automated"]' => array('checked' => TRUE),
      ),
    ),
  );
  
  $form['info']['base'] = array(
    '#type' => 'select',
    '#title' => t('Base theme'),
    '#description' => t('The base theme for this theme.'),
    '#options' => $themes,
    '#default_value' => $base,
    '#ajax' => array(
      'callback' => '_omega_tools_starterkit_options_callback',
      'wrapper' => 'starterkit-options-wrapper',
      'method' => 'replace',
      'effect' => 'fade',
    ),
  );

  $form['info']['starterkit_wrapper'] = array(
    '#prefix' => '<div id="starterkit-options-wrapper">',
    '#suffix' => '</div>',
  );
  
  $form['info']['starterkit_wrapper']['starterkit'] = array(
    '#type' => 'radios',
    '#title' => t('Starterkit'),
    '#options' => array('_none' => t('Proceed without using a starterkit') . '<div class="description">' . t('This is not recommended.') . '</div>') + $starterkits,
    '#description' => t('Starterkits simplify the process of creating new subthemes by providing a set of pre-defined files and settings. Choose wisely!'),
    '#default_value' => $starter,
    '#access' => !empty($starterkits),
  );

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save and continue'),
  );
  
  return $form;
}

/**
 * @todo
 */
function _omega_tools_starterkit_options_callback($form, $form_state) {
  return $form['info']['starterkit_wrapper'];
}

/**
 * @todo
 */
function omega_tools_subtheme_validate_automation($element, $form_state) {
  $path = 'sites/' . $form_state['values']['destination'] . '/themes';
  
  if ($element['#value'] == TRUE && !file_prepare_directory($path, NULL)) {
    form_error($element, t('The destination that you selected is not writable. Please choose a different destination, or select the manual installation method.'));
  }
}

/**
 * @todo
 */
function omega_tools_subtheme_add_submit($form, &$form_state) {
  $values = $form_state['values'];
  
  $subtheme = new stdClass;
  $subtheme->new = TRUE;
  $subtheme->name = $values['name'];
  $subtheme->machine_name = $values['machine_name'];
  $subtheme->starterkit = isset($values['starterkit']) && $values['starterkit'] != '_none' ? $values['starterkit'] : NULL;
  $subtheme->base = $values['base'];
  $subtheme->status = FALSE;
  $subtheme->default = FALSE;
  $subtheme->automated = $values['automated'];
  $subtheme->destination = $values['automated'] ? 'sites/' . $values['destination'] . '/themes/' . $subtheme->machine_name : NULL;
  $subtheme->path = 'temporary://omega-tools/' . $subtheme->machine_name . '-' . substr(hash('sha256', serialize($subtheme) . microtime()), 0, 8);

  if (omega_tools_subtheme_create($subtheme)) {
    omega_tools_cache_set($subtheme->machine_name, $subtheme);
    
    $form_state['redirect'] = 'admin/appearance/omega-tools/edit/' . $subtheme->machine_name;
  }
}