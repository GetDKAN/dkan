<?php

/**
 * Output context debug information.
 */
class context_reaction_debug extends context_reaction {
  function options_form($context) {
    return array('debug' => array('#type' => 'value', '#value' => TRUE));
  }

  function options_form_submit($values) {
    return array('debug' => 1);
  }

  /**
   * Settings form for variables.
   */
  function settings_form() {
    $form = array();
    $form['context_reaction_debug_enable_global'] = array(
      '#title' => t('Enable debug reaction on all contexts'),
      '#type' => 'checkbox',
      '#default_value' => variable_get('context_reaction_debug_enable_global', FALSE),
      '#description' => t('Enable the debug reaction on all contexts.')
    );
    return $form;
  }

  /**
   * Output a list of active contexts.
   */
  function execute() {
    $contexts = context_active_contexts();
    foreach ($contexts as $context) {
      if (!empty($context->reactions['debug']) || variable_get('context_reaction_debug_enable_global', FALSE)) {
        if (user_access('administer site configuration') && module_exists('context_ui')) {
          $name = l($context->name, "admin/structure/context/list/{$context->name}", array('query' => array('destination' => $_GET['q'])));
        }
        else {
          $name = check_plain($context->name);
        }
        drupal_set_message(t("Active context: !name", array('!name' => $name)));
      }
    }
  }
}
