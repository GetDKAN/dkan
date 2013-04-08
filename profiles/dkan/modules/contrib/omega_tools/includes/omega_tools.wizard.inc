<?php

/**
 * @todo
 */
function omega_tools_subtheme_wizard($subtheme, $step = NULL) {
  ctools_include('wizard');
  
  $info = array(
    'id' => 'omega_tools_subtheme_wizard',
    'path' => 'admin/appearance/omega-tools/edit/' . $subtheme->machine_name . '/%step',
    'free trail' => TRUE,
    'show trail' => TRUE,
    'show back' => TRUE,
    'show cancel' => TRUE,
    'show return' => FALSE,
    'next text' => t('Continue'),
    'next callback' => 'omega_tools_subtheme_wizard_next',
    'finish callback' => 'omega_tools_subtheme_wizard_finish',
    'cancel callback' => 'omega_tools_subtheme_wizard_cancel',
    'finish text' => t('Finish'),
    'order' => array(
      'info' => t('Step 1: Theme information'),   
      'finalize' => t('Step 2: Finalize'),
    ),
    'forms' => array(
      'info' => array(
        'form id' => 'omega_tools_subtheme_wizard_info_form',
      ),
      'finalize' => array(
        'form id' => 'omega_tools_subtheme_wizard_finalize_form',
      ),
      'finished' => array(
        'form id' => 'omega_tools_subtheme_wizard_finished_form',
      ),
    ),
  );
  
  $form_state = array('subtheme' => $subtheme);

  return ctools_wizard_multistep_form($info, $step, $form_state);
}

/**
 * @todo
 */
function omega_tools_subtheme_wizard_cancel(&$form_state) {
  $subtheme = &$form_state['subtheme'];
  
  $form_state['redirect'] = 'admin/appearance';
  
  omega_tools_cache_clear($subtheme->machine_name);
  file_unmanaged_delete_recursive($subtheme->path);
}

/**
 * @todo
 */
function omega_tools_subtheme_wizard_next(&$form_state) {
  omega_tools_cache_set($form_state['subtheme']->machine_name, $form_state['subtheme']);
}

/**
 * @todo
 */
function omega_tools_subtheme_wizard_finish(&$form_state) {
  $subtheme = &$form_state['subtheme'];
  
  if ($subtheme->automated) {
    omega_tools_write_info_file($subtheme->machine_name, $subtheme->info, $subtheme->path);
  
    omega_tools_cache_clear($subtheme->machine_name);
    file_unmanaged_delete_recursive($subtheme->destination);
    omega_tools_move($subtheme->path, $subtheme->destination);
    
    if ((!$subtheme->status || !$subtheme->default) && variable_get('theme_default') == $subtheme->machine_name) {
      $subtheme->default = FALSE;

      theme_enable(array('bartik'));
      variable_set('theme_default', 'bartik');
      drupal_set_message(t('%name is now the default theme.', array('%name' => 'Bartik')));
    }

    omega_tools_subtheme_process($subtheme);
  }
  
  $form_state['redirect'] = $subtheme->automated ? 'admin/appearance' : 'admin/appearance/omega-tools/edit/' . $subtheme->machine_name . '/finished';
  
  drupal_set_message(t('The configuration options have been saved.'));
}

/**
 * @todo
 */
function omega_tools_subtheme_wizard_info_form($form, &$form_state) {
  $subtheme = &$form_state['subtheme'];
  
  $form['info'] = array(
    '#type' => 'fieldset',
    '#title' => t('Theme information'),
  );
  
  $form['info']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#description' => t('The human-readable name for this theme.'),
    '#default_value' => isset($subtheme->info['name']) ? $subtheme->info['name'] : '',
    '#size' => 30,
    '#required' => TRUE,
  );
  
  $form['info']['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#description' => t('The description that will be shown on the theme listing page.'),
    '#default_value' => isset($subtheme->info['description']) ? $subtheme->info['description'] : '',
  );
  
  $form['info']['version'] = array(
    '#type' => 'textfield',
    '#title' => t('Version'),
    '#description' => t('The version of this theme.'),
    '#default_value' => isset($subtheme->info['version']) ? $subtheme->info['version'] : '1.x',
  );
  
  $form['buttons']['next']['#next'] = $form_state['next'];
  
  return $form;
}
/**
 * @todo
 */
function omega_tools_subtheme_wizard_info_form_submit($form, &$form_state) {
  $values = &$form_state['values'];
  
  $subtheme = &$form_state['subtheme'];
  $subtheme->info['name'] = $values['name'];
  $subtheme->info['description'] = $values['description'];
  $subtheme->info['version'] = $values['version'];
}

/**
 * @todo
 */
function omega_tools_subtheme_wizard_finalize_form($form, &$form_state) {
  $subtheme = &$form_state['subtheme'];
  
  $form['finalize'] = array(
    '#type' => 'fieldset',
    '#title' => t('Finalize'),
  );
  
  $form['finalize']['description'] = array(
    '#type' => 'item',
    '#markup' => t('This is the final step for configuring your subtheme. You are now able to alter the final result by changing the <em>Advanced configuration</em>.'),
  );
  
  $form['finalize']['enable'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enabled'),
    '#description' => t('Decide wether or not this theme should be enabled.'),
    '#default_value' => $subtheme->status,
    '#access' => $subtheme->automated,
  );
  
  $form['finalize']['default'] = array(
    '#type' => 'checkbox',
    '#title' => t('Default theme'),
    '#description' => t('Decide wether or not you want this theme to be your default theme.'),
    '#default_value' => $subtheme->default,
    '#access' => $subtheme->automated,
    '#states' => array(
      'visible' => array(
        ':input[name="enable"]' => array('checked' => TRUE),
      ),
    ),
  );
  
  $form['finalize']['advanced'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced configuration'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  
  $form['finalize']['advanced']['manipulate'] = array(
    '#type' => 'checkbox',
    '#title' => t('Manipulate the content of %file', array('%file' => $subtheme->machine_name . '.info')),
    '#description' => t('After enabling this option you are able to edit the textarea below.'),
    '#default_value' => FALSE,
  );
  
  $form['finalize']['advanced']['info'] = array(
    '#type' => 'textarea',
    '#title' => t('Content of %file', array('%file' => $subtheme->machine_name . '.info')),
    '#description' => t('Only change the content of this textarea if you know what you are doing.'),
    '#default_value' => omega_tools_build_info_file($subtheme->info),
    '#rows' => 20,
    '#states' => array(
      'enabled' => array(
        ':input[name="manipulate"]' => array('checked' => TRUE),
      ),
    ),
  );
  
  unset($form['buttons']['next']);
  
  return $form;
}

/**
 * @todo
 */
function omega_tools_subtheme_wizard_finalize_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $subtheme = &$form_state['subtheme'];  
  
  $subtheme->info = $values['manipulate'] ? drupal_parse_info_format($values['info']) : $subtheme->info;
  $subtheme->status = $values['enable'];
  $subtheme->default = $values['enable'] && $values['default'];
}

/**
 * @todo
 */
function omega_tools_subtheme_wizard_finished_form($form, &$form_state) {
  $subtheme = &$form_state['subtheme'];
  
  drupal_set_title(t('Your subtheme is ready for download'));
  
  unset($form['ctools_trail'], $form['buttons']['previous'], $form['buttons']['return']);
  
  $form['buttons']['cancel']['#value'] = t('Finish');
  $form['buttons']['next']['#value'] = t('Download');
  
  $form['message']['#markup'] = t('Please press <strong>Download</strong> to save the theme to your local hard disk. After the download has completed you can either <strong>upload the contained folder manually</strong> or install it by using the <strong>Drupal Update Manager</strong>.');
  
  $form['details'] = array(
    '#type' => 'fieldset',
    '#title' => t('Details'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  
  $form['details']['info'] = array(
    '#type' => 'textarea',
    '#title' => t('Content of the .info file'),
    '#default_value' => omega_tools_build_info_file($subtheme->info),
    '#disabled' => TRUE,
    '#rows' => 10,
  );
  
  return $form;
}

/**
 * @todo
 */
function omega_tools_subtheme_wizard_finished_form_submit($form, &$form_state) {
  $subtheme = &$form_state['subtheme'];
  $temporary = 'temporary://omega-tools/' . $subtheme->name;
  
  file_unmanaged_delete_recursive($temporary);
  omega_tools_copy_recursive($subtheme->path, $temporary);
  
  if ($download = omega_tools_write_archive($temporary, $subtheme->name)) {
    file_transfer($download, omega_tools_file_download($download));
  }
  
  file_unmanaged_delete_recursive($temporary);
}