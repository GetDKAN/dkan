<?php

/**
 * @todo
 */
class delta_export_ui extends ctools_export_ui {
  /**
   * @todo
   */
  function list_form(&$form, &$form_state) {
    parent::list_form($form, $form_state);
    
    $form['top row']['theme'] = array(
      '#type' => 'select',
      '#title' => t('Theme'),
      '#options' => array('all' => t('- All -')) + _delta_ui_options_themes(),
      '#default_value' => 'all',
      '#weight' => -10,
    );
    
    $form['top row']['submit'] = $form['bottom row']['submit'];
    $form['top row']['reset'] = $form['bottom row']['reset'];
    $form['bottom row']['#access'] = FALSE;
  }
  
  /**
   * @todo
   */
  function list_filter($form_state, $item) {
    if (parent::list_filter($form_state, $item)) {
      return TRUE;
    }
    
    if ($form_state['values']['theme'] != 'all' && $form_state['values']['theme'] != $item->theme) {
      return TRUE;
    }
  }
  
  /**
   * @todo
   */
  function set_item_state($state, $js, $input, $item) {
    ctools_export_set_object_status($item, $state);

    if (!$js) {      
      drupal_goto(ctools_export_ui_plugin_base_path($this->plugin));
    }
    else {
      return $this->list_page($js, $input);
    }
  }
    
  /**
   * @todo
   */
  function edit_form_validate(&$form, &$form_state) {
    parent::edit_form_validate($form, $form_state);
    
    $values = $form_state['values'];
    
    if ($values['parent'] == '_none') {
      form_set_value(array('#parents' => array('parent')), '', $form_state);
    }
    else {
      form_set_value(array('#parents' => array('mode')), DELTA_PRESERVE, $form_state);
    }
  }
  
  /**
   * @todo
   * 
   * This function can be removed once the core CTools one
   * works properly and uses the right field for the machine 
   * name validation error.
   */
  function edit_finish_validate(&$form, &$form_state) {
    if ($form_state['op'] != 'edit') {
      $element = array(
        '#value' => $form_state['item']->{$this->plugin['export']['key']},
        '#parents' => array($this->plugin['export']['key']),
      );
      
      $form_state['plugin'] = $this->plugin;
      
      ctools_export_ui_edit_name_validate($element, $form_state);
    }
  }
  
  /**
   * @todo
   */
  function list_table_header() {
    $header = array();

    $header[] = array('data' => t('Name'), 'class' => array('ctools-export-ui-name'));
    $header[] = array('data' => t('Theme'), 'class' => array('ctools-export-ui-theme'));
    $header[] = array('data' => t('Ancestors'), 'class' => array('ctools-export-ui-ancestors'));
    $header[] = array('data' => t('Mode'), 'class' => array('ctools-export-ui-Mode'));
    $header[] = array('data' => t('Storage'), 'class' => array('ctools-export-ui-storage'));
    $header[] = array('data' => t('Operations'), 'class' => array('ctools-export-ui-operations'));

    return $header;
  }
  
  /**
   * @todo
   */
  function list_build_row($item, &$form_state, $operations) {
    $name = $item->machine_name;
    $theme = _delta_ui_theme_name($item->theme);    
    $modes = array(DELTA_PRESERVE => t('Preserve'), DELTA_OVERRIDE => t('Override'));
    $ancestors = _delta_ui_ancestors_names($name);
    
    array_shift($ancestors);
    
    $operations = array(
      'configure' => array(
      'title' => t('Configure'),
      'href' => 'admin/appearance/delta/list/' . $name . '/configure',
    )) + $operations;
    
    $this->rows[$name]['data'] = array();
    $this->rows[$name]['class'] = !empty($item->disabled) ? array('ctools-export-ui-disabled') : array('ctools-export-ui-enabled');
    $this->rows[$name]['data'][] = array(
      'data' => check_plain($item->name) . '<div class="description">' . check_plain($item->description) . "</div>",
      'class' => array('ctools-export-ui-name')
    );
    
    $this->rows[$name]['data'][] = array(
      'data' => $theme,
      'class' => array('ctools-export-ui-theme')
    );
    
    $this->rows[$name]['data'][] = array(
      'data' => implode(', ', $ancestors),
      'class' => array('ctools-export-ui-ancestors')
    );
    
    $this->rows[$name]['data'][] = array(
      'data' => $modes[$item->mode],
      'class' => array('ctools-export-ui-mode')
    );
    
    $this->rows[$name]['data'][] = array(
      'data' => check_plain($item->type),
      'class' => array('ctools-export-ui-storage')
    );
    
    $this->rows[$name]['data'][] = array(
      'data' => theme('links', array(
        'links' => $operations,
        'attributes' => array('class' => array('links inline'))
      )),
      'class' => array('ctools-export-ui-operations'),
    );

    $this->sorts[$name] = $name;
  }
  
  function hook_menu(&$items) {
    if (empty($this->plugin['schema'])) {
      return;
    }
    
    parent::hook_menu($items);

    $prefix = ctools_export_ui_plugin_base_path($this->plugin);
  }
}

/**
 * @todo
 */
function delta_ui_form(&$form, &$form_state) {
  $delta = $form_state['item'];
  $themes = _delta_ui_options_themes();
  
  if (isset($form_state['input']['theme'])) {
    $theme = $form_state['input']['theme'];
  }
  else if (isset($delta->theme)) {
    $theme = $delta->theme;
  }
  else {
    $theme = key($themes);
  }

  $parents = _delta_ui_options_parents($theme, $delta->machine_name);
  
  $form['info']['#type'] = 'fieldset';
  $form['info']['#title'] = delta_load($delta->machine_name) ? t('Edit an existing Delta template') : t('Add a new Delta template');
  $form['info']['#tree'] = FALSE;
  
  $form['info']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#description' => t('The human readable name for this template.'),
    '#default_value' => isset($delta->name) ? $delta->name : '',
    '#id' => 'edit-delta-name',
    '#required' => TRUE,
  );

  $form['info']['machine_name'] = array(
    '#type' => 'machine_name',
    '#title' => t('Machine name'),
    '#description' => t('A unique machine-readable name for this theme settings template. It must only contain lowercase letters, numbers, and underscores.'),
    '#default_value' => isset($delta->machine_name) ? $delta->machine_name : '',
    '#required' => TRUE,
    '#maxlength' => 32,
    '#access' => !delta_load($delta->machine_name),
    '#machine_name' => array(
      'source' => array('info', 'name'),
      'exists' => 'delta_load',
    ),
  );
  
  $form['info']['description'] = array(
    '#type' => 'textfield',
    '#title' => t('Description'),
    '#description' => t('A brief description of this theme settings template.'),
    '#default_value' => isset($delta->description) ? $delta->description : '',
  );
  
  $form['info']['theme'] = array(
    '#type' => 'select',
    '#title' => t('Theme'),
    '#required' => TRUE,
    '#description' => t('The theme that you want to create this template for.'),
    '#default_value' => $theme,
    '#options' => $themes,
    '#access' => !isset($delta->machine_name),
    '#ajax' => array(
      'callback' => '_delta_ui_parent_options_callback',
      'wrapper' => 'parent-options-wrapper',
      'method' => 'replace',
      'effect' => 'fade',
    ),
  );

  $form['info']['parent'] = array(
    '#type' => 'select',
    '#title' => t('Parent template'),
    '#description' => t('This option allows you to build hierarchical theme settings. Delta templates that have a parent will always operate in preserve ("Only override different values") mode.'),
    '#default_value' => isset($delta->parent) ? $delta->parent : array(),
    '#options' => array('_none' => t('- None -')) + $parents,
    '#prefix' => '<div id="parent-options-wrapper">',
    '#suffix' => '</div>',
  );
    
  $form['info']['mode'] = array(
    '#type' => 'radios',
    '#title' => t('Operation mode'),
    '#description' => t('This setting controls the way that settings are being stored and overriden.'),
    '#default_value' => isset($delta->mode) ? $delta->mode : DELTA_PRESERVE,
    '#options' => array(
      DELTA_PRESERVE => t('Only override different values'),
      DELTA_OVERRIDE => t('Override all values'),
    ),
    '#states' => array(
      'visible' => array(
        ':input[name="parent"]' => array('value' => '_none'),
      ),
    ),
  );
  
  $form['info']['settings'] = array(
    '#type' => 'value',
    '#value' => isset($delta->settings) ? $delta->settings : array(),
  );
}

/**
 * @todo
 */
function _delta_ui_parent_options_callback($form, $form_state) {
  return $form['info']['parent'];
}

/**
 * @todo
 */
function _delta_ui_ancestors_names($delta) {
  $output = array();
  foreach (delta_ancestors($delta) as $ancestor) {
    $output[$ancestor->machine_name] = $ancestor->name;
  }
  
  return $output;
}

/**
 * @todo
 */
function _delta_ui_theme_name($theme) {
  $themes = list_themes();
  
  return isset($themes[$theme]) ? $themes[$theme]->info['name'] : t('<span class="marker">Theme not found</span>');
}


/**
 * @todo
 */
function _delta_ui_options_themes() {
  $themes = list_themes();
  
  $options = array();
  foreach ($themes as $theme => $info) {
    if ($info->status) {
      $options[$theme] = $info->info['name'];
    }
  }
  
  return $options;
}

/**
 * @todo
 */
function _delta_ui_options_parents($key, $delta) {
  $options = array();
  foreach (delta_load_all() as $item) {
    
    if ($item->theme == $key && (!isset($delta) || ($item->machine_name != $delta && !in_array($delta, array_keys(delta_ancestors($item->parent)))))) {
      $options[$item->machine_name] = $item->name;
    }
  }
  
  return $options;
}