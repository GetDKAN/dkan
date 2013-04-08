<?php

/**
 * Expose themes as context reactions.
 */
class context_reaction_delta extends context_reaction {
  /**
   * @todo
   */
  function options_form($context) {
    $values = $this->fetch_from_context($context);
    $options = _delta_options();
    
    $form = array(
      '#tree' => TRUE,
      '#title' => t('Delta Override Template'),
    );
    
    $form['delta_template'] = array(
      '#type' => count($options) > 10 ? 'select' : 'radios',
      '#title' => t('Select Custom theme settings template.'),
      '#description' => empty($options) ? t('There are no valid (enabled) Delta templates available.') : t('The theme settings of the selected Delta template will override the default theme settings for all pages that match this context.'),
      '#default_value' => isset($values['delta_template']) ? $values['delta_template'] : NULL,
      '#options' => $options,
    );
    
    return $form;
  }

  /**
   * @todo
   */
  function execute() {
    foreach (context_active_contexts() as $context) {
      if (!empty($context->reactions['delta']['delta_template'])) {
        $template = $context->reactions['delta']['delta_template'];
        
        if (delta_enabled($template)) {
          delta_inject($template);      
        }
      }
    }
  }
}

/**
 * @todo
 */
function _delta_options() {  
  $options = array();
  foreach (delta_load_all() as $item) {
    if (delta_enabled($item->machine_name)) {
      $options[$item->machine_name] = $item->name;
    }
  }
  
  return $options;
}