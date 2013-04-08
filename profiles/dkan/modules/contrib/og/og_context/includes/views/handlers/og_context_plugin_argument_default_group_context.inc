<?php

/**
 * @file
 * Contains the group context argument default plugin.
 */

/**
 * The group context argument default handler.
 */
class og_context_plugin_argument_default_group_context extends views_plugin_argument_default {

  /**
   * Retrieve the options when this is a new access
   * control plugin
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['group_type'] = array('default' => 'node');

    return $options;
  }

  /**
   * Provide the default form for setting options.
   */
  function options_form(&$form, &$form_state) {
    $form['group_type'] = array(
      '#type' => 'select',
      '#options' => og_get_all_group_entity(),
      '#title' => t('Group type'),
      '#default_value' => $this->options['group_type'],
      '#description' => t('Determine what entity type that group should be of.')
    );
  }

  /**
   * Return the group context argument.
   */
  function get_argument() {
    if ($group = og_context($this->options['group_type'])) {
      return $group['gid'];
    }
    return FALSE;
  }
}
