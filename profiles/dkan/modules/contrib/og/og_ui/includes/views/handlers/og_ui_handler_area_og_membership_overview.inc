<?php

/**
 * Overview of the group memberships (e.g. group manager, total memebrs).
 */
class og_ui_handler_area_og_membership_overview extends views_handler_area {

  function option_definition() {
    $options = parent::option_definition();

    // Undefine the empty option.
    unset($options['empty']);

    $options['manager'] = array('default' => TRUE);

    foreach (og_group_content_states() as $state => $label) {
      $options["total_members_$state"] = array('default' => FALSE);
    }

    $options['total_members'] = array('default' => TRUE);
    $options['total_content'] = array('default' => TRUE);

    return $options;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['manager'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show group manager'),
      '#default_value' => $this->options['manager'],
    );

    foreach (og_group_content_states() as $state => $label) {
      $form["total_members_$state"] = array(
        '#type' => 'checkbox',
        '#title' => t('Show total @label members', array('@label' => strtolower($label))),
        '#default_value' => $this->options["total_$state"],
      );
    }

    $form['total_members'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show total members'),
      '#default_value' => $this->options['total_members'],
    );

    $form['total_content'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show total content'),
      '#default_value' => $this->options['total_content'],
    );

    // Don't display a form element for the undefined empty option.
    unset($form['empty']);
  }


  function render($empty = FALSE) {

    // Get arguments.
    foreach ($this->view->argument as $key => $handler) {
      if ($key == 'group_type') {
        $group_type = $handler->get_value();
      }
      elseif ($key == 'gid') {
        $gid = $handler = $handler->get_value();
      }
    }
    if (empty($group_type) || empty($gid)) {
      // No group type and group ID provided.
      return;
    }

    $group = entity_load_single($group_type, $gid);
    if (!$group || !og_is_group($group_type, $group)) {
      // Arguments are not a valid group.
      return;
    }

    $items = array();

    if (!empty($group->uid) && $this->options['manager']) {
      // Group manager.
      $account = user_load($group->uid);
      $items[] = array('data' => t('Group manager: !manager', array('!manager' => theme('username', array('account' => $account)))));
    }

    $base_query = new EntityFieldQuery();
    $base_query
      ->entityCondition('entity_type', 'og_membership')
      ->propertyCondition('group_type', $group_type, '=')
      ->propertyCondition('gid', $gid, '=')
      ->count();

    foreach (og_group_content_states() as $state => $label) {
      // Total members by state.
      if ($this->options["total_members_$state"]) {
        $query = clone $base_query;
        $count = $query
          ->propertyCondition('entity_type', 'user', '=')
          ->propertyCondition('state', $state, '=')
          ->execute();

        $params = array(
          '%label' => strtolower($label),
          '@count' => $count,
        );

        $items[] = array('data' => t('Total %label members: @count', $params));
      }
    }

    if ($this->options['total_members']) {
      // Total members.
      $query = clone $base_query;
      $count = $query
        ->propertyCondition('entity_type', 'user', '=')
        ->execute();
      $items[] = array('data' => t('Total members: @count', array('@count' => $count)));
    }

   if ($this->options['total_content']) {
     // Total nodes.
      $query = clone $base_query;
      $count = $query
        ->propertyCondition('entity_type', 'node', '=')
        ->execute();

      $items[] = array('data' => t('Total content: @count', array('@count' => $count)));
    }

    return theme('item_list', array('items' => $items, 'title' => t('Group overview')));
  }
}
