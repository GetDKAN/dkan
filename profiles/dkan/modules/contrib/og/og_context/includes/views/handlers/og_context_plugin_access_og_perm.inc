<?php

/**
 * @file
 * Contains views access plugin for OG permissions
 */

/**
 * Allow views to allow access to only users with a specified permission in the
 * current group.
 */
class og_context_plugin_access_og_perm extends views_plugin_access {

  /**
   * Retrieve the options when this is a new access
   * control plugin
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['perm'] = array('default' => 'edit group');
    $options['group_type'] = array('default' => 'node');
    $options['group_id_arg'] = array('default' => FALSE);

    return $options;
  }

  /**
   * Provide the default form for setting options.
   */
  function options_form(&$form, &$form_state) {
    $perms = array();
    // Get list of permissions
    $module_info = system_get_info('module');
    foreach (og_get_permissions() as $perm => $info) {
      $module_name = $module_info[$info['module']]['name'];
      $perms[$module_name][$perm] = strip_tags($info['title']);
    }

    $form['perm'] = array(
      '#type' => 'select',
      '#options' => $perms,
      '#title' => t('OG permission'),
      '#default_value' => $this->options['perm'],
      '#description' => t('Only users with the selected permission flag in a group retrieved from context will be able to access this display.')
    );
    $form['group_type'] = array(
      '#type' => 'select',
      '#options' => og_get_all_group_entity(),
      '#title' => t('Group type'),
      '#default_value' => $this->options['group_type'],
      '#description' => t('Determine what entity type that group should be of.')
    );

    $current_display = $this->view->current_display;
    if ($this->view->display[$current_display]->handler->has_path()) {
      // Show the group ID argument position only for "Page" displays.
      $form['group_id_arg'] = array(
        '#type' => 'select',
        '#title' => t('Argument position for group ID'),
        '#default_value' => $this->options['group_id_arg'],
        '#options' => array(NULL => t('None')) + range(0, 9),
        '#description' => t('Group ID argument position with arg() function. e.g. if your dynamic path is "node/%/group/%/overview" and you are using the second "%" for group IDs you have to choose "3" like "arg(3)".'),
      );
    }
  }

  /**
   * Return a string to display as the clickable title for the
   * access control.
   */
  function summary_title() {
    $current_display = $this->view->current_display;
    if ($this->options['group_id_arg'] === FALSE || $this->view->display[$current_display]->display_plugin != 'page') {
      return $this->options['perm'];
    }

    $params = array(
      '@perm' => $this->options['perm'],
      '@arg' => $this->options['group_id_arg'],
    );

    return t('@perm, getting the group ID from argument position @arg', $params);
  }

  /**
   * Determine if the current user has access or not.
   */
  function access($account) {
    // Attempt to get the group from the current context and determine if the
    // user has the appropriate permission within the group
    if ($group = og_context($this->options['group_type'])) {
      return og_user_access($group['group_type'], $group['gid'], $this->options['perm'], $account);
    }
    return FALSE;
  }

  /**
   * Determine the access callback and arguments.
   */
  function get_access_callback() {
    return array('_og_context_views_page_access', array($this->options['group_type'], $this->options['perm'], $this->options['group_id_arg']));
  }
}
