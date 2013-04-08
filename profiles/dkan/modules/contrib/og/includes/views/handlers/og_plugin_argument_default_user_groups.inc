<?php

/**
 * @file
 * Holds the class defining the Views plugin loading the groups the acting user
 * is member of.
 */

/**
 * The class defining the Views plugin loading the groups of the acting user.
 */
class og_plugin_argument_default_user_groups extends views_plugin_argument_default {

  /**
   * List the options relevant for this plugin.
   */
  function option_definition() {
    $options = parent::option_definition();
    $groups = og_get_all_group_entity();
    $options['group_type'] = array('default' => key($groups));
    $options['glue'] = array('default' => '+');

    return $options;
  }

  /**
   * Provide the default form for setting options.
   */
  function options_form(&$form, &$form_state) {
    $form['group_type'] = array(
      '#type' => 'select',
      '#title' => t('Group type'),
      '#description' => t('Select the group type.'),
      '#options' => og_get_all_group_entity(),
      '#default_value' => $this->options['group_type'],
      '#required' => og_get_all_group_entity(),
    );
    $form['glue'] = array(
      '#type' => 'select',
      '#title' => t('Concatenator'),
      '#description' => t('Select the concatenator used to merge multiple group IDs. Remember to turn on the "Allow multiple values" option in the "more" settings for this contextual filter.'),
      '#options' => array('+' => '+', ',' => ','),
      '#default_value' => $this->options['glue'],
    );
  }

  /**
   * Get the default argument.
   */
  function get_argument() {
    // Get the group IDs relevant for the acting user, and return them
    // concatenated.
    if ($gids = og_get_groups_by_user(NULL, $this->options['group_type'])) {
      return implode($this->options['glue'], $gids);
    }
    return FALSE;
  }
}
