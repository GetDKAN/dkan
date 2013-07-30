<?php

/**
 * @file
 * Admin settings for Organic groups module.
 */

/**
 * Menu callback; Admin settings form.
 */
function og_ui_admin_settings($form_state) {
  $form = array();

  $form['og_group_manager_full_access'] = array(
    '#type' => 'checkbox',
    '#title' => t('Group manager full permissions'),
    '#description' => t('When enabled the group manager will have all the permissions in the group.'),
    '#default_value' => variable_get('og_group_manager_full_access', TRUE),
  );

  $form['og_node_access_strict'] = array(
    '#type' => 'checkbox',
    '#title' => t('Strict node access permissions'),
    '#description' => t('When enabled Organic groups will restrict permissions for creating, updating and deleting according to the  Organic groups access settings. Example: A content editor with the <em>Edit any page content</em> permission who is not a member of a group would be denied access to modifying page content in that group. (For restricting view access use the Organic groups access control module.)'),
    '#default_value' => variable_get('og_node_access_strict', TRUE),
  );

  $form['og_ui_admin_people_view'] = array(
    '#type' => 'select',
    '#title' => t('Admin people View'),
    '#description' => t('Select the View that should be used to show and control the people in the group.'),
    '#options' => views_get_views_as_options(),
    '#default_value' => variable_get('og_ui_admin_people_view', 'og_members_admin:default'),
    '#required' => TRUE,
  );
  if ($group_bundles = og_get_all_group_bundle()) {
    $form['og_group_manager_rids'] = array(
      '#type' => 'fieldset',
      '#title' => t('Group manager default roles'),
      '#description' => t('Select the role(s) a group manager will be granted upon creating a new group.'),
    );
    // Add group manager default roles.
    $entity_info = entity_get_info();
    foreach ($group_bundles as $entity_type => $bundles) {
      foreach ($bundles as $bundle_name => $bundle_label) {
        $og_roles = og_roles($entity_type, $bundle_name, 0, FALSE, FALSE);
        if (!$og_roles) {
         continue;
        }

        $params = array(
          '@entity-label' => $entity_info[$entity_type]['label'],
          '@bundle-label' => $bundle_label,
        );

        $name = 'og_group_manager_default_rids_' . $entity_type . '_' . $bundle_name;
        $form['og_group_manager_rids'][$name] = array(
          '#type' => 'select',
          '#title' => t('Roles in @entity-label - @bundle-label', $params),
          '#options' => $og_roles,
          '#multiple' => TRUE,
          '#default_value' => variable_get($name, array()),
        );
      }
    }
  }

  $form['og_features_ignore_og_fields'] = array(
    '#type' => 'checkbox',
    '#title' => t('Prevent "Features" export piping'),
    '#description' => t('When exporting using Features module a content-type, this will prevent from OG related fields to be exported.'),
    '#default_value' => variable_get('og_features_ignore_og_fields', FALSE),
    '#access' => module_exists('features'),
  );

  $form['og_use_queue'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use queue'),
    '#description' => t("Use the core's queue process to operations such as deleting memberships when groups are deleted."),
    '#default_value' => variable_get('og_use_queue', FALSE),
  );

  $form['og_orphans_delete'] = array(
    '#type' => 'checkbox',
    '#title' => t('Delete orphans'),
    '#description' => t('Delete "Orphan" group-content (not including useres), when the group is deleted.'),
    '#default_value' => variable_get('og_orphans_delete', FALSE),
    '#states' => array(
      'visible' => array(
        ':input[name="og_use_queue"]' => array('checked' => TRUE),
      ),
    ),
    '#attributes' => array(
      'class' => array('entityreference-settings'),
    ),
  );

  // Re-use Entity-reference CSS for indentation.
  $form['#attached']['css'][] = drupal_get_path('module', 'entityreference') . '/entityreference.admin.css';
  return system_settings_form($form);
}

/**
 * Provide an overview of the administrator menu items.
 *
 * @param $entity_type
 *   The entity type.
 * @param $etid
 *   The entity ID.
 */
function og_ui_group_admin_overview($entity_type, $etid) {
  og_set_breadcrumb($entity_type, $etid);

  $items = og_ui_get_group_admin($entity_type, $etid);

  foreach ($items as &$item) {
    // Re-format the URL.
    $item['href'] = "group/$entity_type/$etid/" . $item['href'];
    // Imitate the menu_get_item() content, so we can pass it to
    // theme_admin_block_content(), so we add the localized_options key.
    $item['localized_options'] = array();
  }

  $form['admin_block_content'] = array('#theme' => 'admin_block_content', '#content' => $items);
  return $form;
}

/**
 * Add users to group form.
 */
function og_ui_add_users($form, &$form_state, $group_type, $gid) {
  og_set_breadcrumb($group_type, $gid, array(l(t('Group'), "$group_type/$gid/group")));
  $group = entity_load_single($group_type, $gid);
  $label = entity_label($group_type, $group);
  list(,, $bundle) = entity_extract_ids($group_type, $group);

  $form['group_type'] = array('#type' => 'value', '#value' => $group_type);
  $form['gid'] = array('#type' => 'value', '#value' => $gid);

  $form['og_user'] = array(
    '#type' => 'fieldset',
    '#title' => t('Add a group member to %group', array('%group' => $label)),
  );
  $form['og_user']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('User name'),
    '#autocomplete_path' => 'user/autocomplete',
    '#required' => TRUE,
  );

  $form['og_user']['state'] = array(
    '#type' => 'value',
    '#value' => OG_STATE_ACTIVE,
  );

  // Get all the non-default roles.
  if ($og_roles = og_roles($group_type, $bundle, $gid, FALSE, FALSE)) {
    $form['og_user']['roles'] = array(
      '#type' => 'checkboxes',
      '#options' => $og_roles,
      '#title' => t('Roles'),
    );
  }

  $field_names = og_get_group_audience_fields('user', 'user', $group_type, $bundle);
  $field_name = !empty($form_state['values']['field_name']) ? $form_state['values']['field_name'] : key($field_names);

  if (count($field_names) > 1) {
   $form['og_user']['field_name'] = array(
      '#type' => 'select',
      '#title' => t('Field name'),
      '#options' => $field_names,
      '#default_value' => $field_name,
      '#description' => t('Select the field name, the group membership should be registered in.'),
      '#ajax' => array(
        'callback' => 'og_ui_admin_add_user_ajax_callback',
        'wrapper' => 'og-ui-field-name',
      ),
    );
  }
  else {
    // There is only a single field name, so just pass it as a value.
    $form['og_user']['field_name'] = array(
      '#type' => 'value',
      '#value' => $field_name,
    );
  }

  // Add group membership form.
  $values = array();

  // Add group membership form. We still don't have the user or state.
  $og_membership = og_membership_create($group_type, $gid, 'user', 0, $field_name, $values);
  $form_state['og_membership'] = $og_membership;

  $form['membership_fields'] = array(
    '#prefix' => '<div id="og-ui-field-name">',
    '#suffix' => '</div>',
    '#tree' => TRUE,
    '#parents' => array('membership_fields'),
  );
  field_attach_form('og_membership', $og_membership, $form['membership_fields'], $form_state);

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Add users'));

  return $form;
}

/**
 * AJAX callback to attach the group membership type fields to the form.
 *
 * Since the controlling logic for populating the form is in the form builder
 * function, all we do here is select the element and return it to be updated.
 */
function og_ui_admin_add_user_ajax_callback(array $form, array &$form_state) {
  return $form['membership_fields'];
}

/**
 * Validate handler; Add users to group.
 */
function og_ui_add_users_validate($form, &$form_state) {
  $account = user_load_by_name($form_state['values']['name']);
  $group_type = $form_state['values']['group_type'];
  $gid = $form_state['values']['gid'];
  $field_name = $form_state['values']['field_name'];
  $params = array('%name' => format_username($account));
  if (!$account) {
    form_set_error('name', t('You have entered an invalid user name.'));
    return;
  }
  elseif (og_get_membership($group_type, $gid, 'user', $account->uid)) {
    form_set_error('name', t('User %name is already subscribed to group.', $params));
  }
  // Check field cardinality.
  elseif (!og_check_field_cardinality('user', $account, $field_name)) {
    $instance = field_info_instance('user', $field_name, 'user');
    $params['%field'] = $instance['label'];
    form_set_error('field_name', t('%name has reached maximum values in field %field.', $params));
  }
  else {
    $og_membership = $form_state['og_membership'];
    field_attach_form_validate('og_membership', $og_membership, $form['membership_fields'], $form_state);
  }
}

/**
 * Submit handler; Add users to group.
 */
function og_ui_add_users_submit($form, &$form_state) {
  $group_type = $form_state['values']['group_type'];
  $gid = $form_state['values']['gid'];
  $state = $form_state['values']['state'];
  $og_membership = $form_state['og_membership'];
  // Set the user and state.
  field_attach_submit('og_membership', $og_membership, $form['membership_fields'], $form_state);
  $account = user_load_by_name($form_state['values']['name']);
  // Ungroup user, in case they were already registered.
  og_ungroup($group_type, $gid, 'user', $account);

  $og_membership->etid = $account->uid;
  $og_membership->state = $state;
  $og_membership->save();
  // Assign roles.
  if (!empty($form_state['values']['roles'])) {
    foreach ($form_state['values']['roles'] as $rid) {
      og_role_grant($group_type, $gid, $og_membership->etid, $rid);
    }
  }

  $group = entity_load_single($group_type, $gid);
  drupal_set_message(t('%user has been added to the group %group-title.', array('%user' => format_username($account), '%group-title' => entity_label($group_type, $group))));
}

/**
 * Add Edit membership form.
 */
function og_ui_edit_membership($form, &$form_state, $group_type, $gid, $og_membership) {
  og_set_breadcrumb($group_type, $gid, array(l(t('Group'), "$group_type/$gid/group")));
  $group = entity_load_single($group_type, $gid);
  $label = entity_label($group_type, $group);
  $account = user_load($og_membership->etid);
  list(,, $bundle) = entity_extract_ids($group_type, $group);
  // Get all the non-default roles.
  $og_roles = og_roles($group_type, $bundle, $gid, FALSE, FALSE);

  $form['group_type'] = array('#type' => 'value', '#value' => $group_type);
  $form['gid'] = array('#type' => 'value', '#value' => $gid);
  $form['id'] = array('#type' => 'value', '#value' => $og_membership->id);

  $form['og_user'] = array(
    '#type' => 'fieldset',
    '#title' => t('Edit a group membership in %group', array('%group' => $label)),
  );
  $form['og_user']['name'] = array(
    '#type' => 'markup',
    '#title' => t('User name'),
    '#markup' => $account->name,
  );
  if ($og_roles) {
    $form['og_user']['roles'] = array(
      '#type' => 'checkboxes',
      '#options' => $og_roles,
      '#title' => t('Roles'),
      '#default_value' => array_keys(og_get_user_roles($group_type, $gid, $account->uid)),
    );
  }

 // Add group membership form.
  $values = array();

  // Add group membership form. We still don't have the user or state.
  $form_state['og_membership'] = $og_membership;

  $form['membership_fields'] = array(
    '#prefix' => '<div id="og-ui-field-name">',
    '#suffix' => '</div>',
    '#tree' => TRUE,
    '#parents' => array('membership_fields'),
  );
  field_attach_form('og_membership', $og_membership, $form['membership_fields'], $form_state);

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Update membership'));

  return $form;
}

/**
 * Validate handler; Edit membership in group.
 */
function og_ui_edit_membership_validate($form, &$form_state) {
  $og_membership = $form_state['og_membership'];
  field_attach_form_validate('og_membership', $og_membership, $form['membership_fields'], $form_state);
}

/**
 * Submit handler; Edit membership in group.
 */
function og_ui_edit_membership_submit($form, &$form_state) {
  $group_type = $form_state['values']['group_type'];
  $gid = $form_state['values']['gid'];
  $og_membership = $form_state['og_membership'];
  field_attach_submit('og_membership', $og_membership, $form['membership_fields'], $form_state);
  $og_membership->save();
  $account = user_load($og_membership->etid);
  // Assign roles.
  $og_roles = og_get_user_roles($group_type, $gid, $account->uid);
  foreach (array_keys($og_roles) as $rid) {
    if (!in_array($rid, $form_state['values']['roles'])) {
      og_role_revoke($group_type, $gid, $account->uid, $rid);
    }
  }
  if (!empty($form_state['values']['roles'])) {
    foreach ($form_state['values']['roles'] as $rid) {
      og_role_grant($group_type, $gid, $og_membership->etid, $rid);
    }
  }
  drupal_set_message(t('The membership has been updated.'));
}



/**
 * Form builder; OG user administration page.
 */
function og_ui_admin_account($group_type, $gid) {
  og_set_breadcrumb($group_type, $gid, array(l(t('Group'), "$group_type/$gid/group")));
  // $view = explode(':', variable_get)
  list($view_name, $display) = explode(':', variable_get('og_ui_admin_people_view', 'og_members_admin:default'));
  // We can't use views_embed_view() as we have exposed filter, and we need to
  // make sure the path is overriden to the current URL.
  // @see http://drupal.org/node/525592#comment-1968604
  $view = views_get_view($view_name, $display);
  $view->set_arguments(array($group_type, $gid));
  $view->override_url = $_GET['q'];
  return $view->preview();
}

/**
 * Display an overview of group types with edit link.
 *
 * @param $type
 *   Either 'roles' or 'permissions'. Determines the edit link url.
 *
 * @return
 *   Renderable table of group types.
 */
function og_ui_group_types_overview($type) {
  $header = array(t('Group type'), t('Operations'));
  $rows = array();

  foreach (og_get_all_group_bundle() as $entity_type => $bundles) {
    $entity_info = entity_get_info($entity_type);
    foreach ($bundles as $bundle_name => $bundle_label) {
      $row = array();
      $row[] = array('data' => check_plain($entity_info['label'] . ' - ' . $bundle_label));
      $row[] = array('data' => l(t('edit'), "admin/config/group/$type/$entity_type/$bundle_name"));

      $rows[] = $row;
    }
  }

  $build['roles_table'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('No group types available. Edit or <a href="@url">add a new content type</a> and set it to behave as a group.', array('@url' => url('admin/structure/types/add'))),
  );

  return $build;
}

/**
 * Menu callback: administer roles.
 *
 * @ingroup forms
 * @see og_user_admin_roles_validate()
 * @see og_user_admin_roles_submit()
 * @see theme_group_user_admin_new_role()
 */
function og_ui_admin_roles($form, $form_state, $group_type = '', $gid = 0, $bundle = '', $rid = 0) {
  if ($gid) {
    og_set_breadcrumb($group_type, $gid, array(l(t('Group'), "$group_type/$gid/group")), l(t('Roles'), "group/$group_type/$gid/admin/roles"));
  }

  if ($rid) {
    // Display the edit role form.
    $role = og_role_load($rid);

    // Get group type and bundle from role.
    $bundle = $role->group_bundle;
    $group_type = $role->group_type;

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Role name'),
      '#default_value' => $role->name,
      '#size' => 30,
      '#required' => TRUE,
      '#maxlength' => 64,
      '#description' => t('The name for this role. Example: "moderator", "editorial board", "site architect".'),
    );
    $form['rid'] = array(
      '#type' => 'value',
      '#value' => $rid,
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save role'),
    );
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete role'),
    );
  }
  else {
    if ($gid) {
      $group = entity_load_single($group_type, $gid);
      list(,, $bundle) = entity_extract_ids($group_type, $group);
    }

    // No role ID.
    if (!$gid || ($group && !og_is_group_default_access($group_type, $group))) {

      $form['name'] = array(
        '#type' => 'textfield',
        '#title' => t('Name'),
        '#title_display' => 'invisible',
        '#size' => 32,
        '#maxlength' => 64,
      );
      $form['add'] = array(
        '#type' => 'submit',
        '#value' => t('Add role'),
        '#validate' => array('og_ui_admin_roles_validate'),
        '#submit' => array('og_ui_admin_roles_submit'),
      );

      // Attach the CSS to the form.
      $form['#attached'] = array(
        'css' => array (
          'type' => 'file',
          'data' => drupal_get_path('module', 'og_ui') . '/css/og_ui.css',
        ),
      );
    }
  }

  $form['group_type'] = array('#type' => 'value', '#value' => $group_type);
  $form['bundle'] = array('#type' => 'value', '#value' => $bundle);
  $form['gid'] = array('#type' => 'value', '#value' => $gid);

  $role_names = _og_ui_get_role_names($group_type, $bundle, $gid, $rid);
  $form['role_names'] = array('#type' => 'value', '#value' => $role_names);

  return $form;
}

/**
 * Validate handler; OG roles.
 */
function og_ui_admin_roles_validate($form, &$form_state) {
  if (!empty($form_state['values']['name'])) {
    $group_type = $form['group_type']['#value'];
    $bundle = $form['bundle']['#value'];
    $gid = $form['gid']['#value'];

    $roles = og_roles($group_type, $bundle, $gid);
    $unique_name = TRUE;
    if (!empty($form_state['values']['rid'])) {
      $role = og_role_load($form_state['values']['rid']);
      $unique_name = $role->name != $form_state['values']['name'];
    }
    if ($unique_name && in_array($form_state['values']['name'], $roles)) {
      form_set_error('name', t('The role name %name already exists. Please choose another role name.', array('%name' => $form_state['values']['name'])));
    }
  }
  else {
    form_set_error('name', t('You must specify a valid role name.'));
  }
}

/**
 * Submit handler; OG roles.
 */
function og_ui_admin_roles_submit($form, &$form_state) {
  $group_type = $form['group_type']['#value'];
  $bundle = $form['bundle']['#value'];
  $gid = $form['gid']['#value'];


  if (!empty($form_state['values']['rid'])) {
    $role = og_role_load($form_state['values']['rid']);
    $role->name = $form_state['values']['name'];
  }
  else {
    $role = og_role_create($form_state['values']['name'], $group_type, $gid, $bundle);
  }
  if ($form_state['values']['op'] == t('Save role')) {
    og_role_save($role);
    drupal_set_message(t('The role has been renamed.'));
  }
  elseif ($form_state['values']['op'] == t('Add role')) {
    og_role_save($role);
    drupal_set_message(t('The role has been added.'));
  }
  elseif ($form_state['values']['op'] == t('Delete role')) {
    og_role_delete($form_state['values']['rid']);
    drupal_set_message(t('The role has been deleted.'));
  }

  if ($gid) {
    $form_state['redirect'] = "group/$group_type/$gid/admin/roles";
  }
  else {
    $form_state['redirect'] = 'admin/config/group/roles';
    if ($bundle) {
      $form_state['redirect'] .= "/$group_type/$bundle";
    }
  }
}

/**
 * Theme the new role form.
 *
 * @ingroup themeable
 */
function theme_og_ui_admin_new_role($variables) {
  $form = $variables['form'];
  $header = array(t('Name'), array('data' => t('Operations'), 'colspan' => 2));

  $group_type = $form['group_type']['#value'];
  $gid = $form['gid']['#value'];
  $bundle = $form['bundle']['#value'];
  $role_names = $form['role_names']['#value'];

  if ($gid) {
    $group = entity_load_single($group_type, $gid);
    $default_access = $group && og_is_group_default_access($group_type, $group);
  }
  else {
    $default_access = FALSE;
  }

  foreach ($role_names as $rid => $name) {
    $text = !$default_access ? t('edit permissions') : t('view permissions');
    $path = $gid ? "group/$group_type/$gid/admin" : 'admin/config/group';
    $permissions = l($text, "$path/permission/$rid/edit");

    if (!$default_access && !in_array($name, array(OG_ANONYMOUS_ROLE, OG_AUTHENTICATED_ROLE))) {
      $rows[] = array(check_plain($name), l(t('edit role'), "$path/role/$rid/edit"), $permissions);
    }
    else {
      $rows[] = array(check_plain($name), t('locked'), $permissions);
    }
  }

  $rows[] = array(array('data' => drupal_render($form['name']) . drupal_render($form['add']), 'colspan' => 4, 'class' => 'edit-name'));

  hide($form['actions']);
  $output = drupal_render_children($form);
  $output .= theme('table', array('header' => $header, 'rows' => $rows));
  $output .= render($form['actions']);

  return $output;
}

/**
 * Helper function to get role names.
 *
 * @param $group_type
 *   Group entity type. E.g. 'node'.
 * @param $bundle
 *   Group bundle.
 * @param $gid
 *   Group item ID.
 * @param $rid
 *   Role ID.
 *
 * @return array
 *   Role names according to parameters.
 */
function _og_ui_get_role_names($group_type, $bundle, $gid, $rid) {
  if ($gid) {
    $group = entity_load_single($group_type, $gid);

    list(,,$bundle) = entity_extract_ids($group_type, $group);
    $gid = og_is_group_default_access($group_type, $group) ? 0 : $gid;
  }

  $role_names = og_roles($group_type, $bundle, $gid);
  if ($rid && !empty($role_names[$rid])) {
    $role_names = array($rid => $role_names[$rid]);
  }

  return $role_names;
}

/**
 * Menu callback: administer permissions.
 *
 * @param $group_type
 *   Group entity type. E.g. 'node'.
 * @param $gid
 *   Group item ID.
 * @param $bundle
 *   Group bundle.
 * @param $rid.
 *   Role ID. When specified, edit only this role's permissions.
 *
 * @ingroup forms
 * @see og_ui_admin_permissions_submit()
 * @see theme_og_ui_admin_permissions()
 */
function og_ui_admin_permissions($form, $form_state, $group_type = '', $gid = 0, $bundle = '', $rid = 0) {
  if ($rid) {
    // Get group type and bundle from role.
    $role = og_role_load($rid);
    $bundle = $role->group_bundle;
    $group_type = $role->group_type;
  }

  if ($gid) {
    og_set_breadcrumb($group_type, $gid, array(l(t('Group'), "$group_type/$gid/group")));
  }

  $form['group_type'] = array('#type' => 'value', '#value' => $group_type);
  $form['bundle'] = array('#type' => 'value', '#value' => $bundle);
  $form['gid'] = array('#type' => 'value', '#value' => $gid);

  $role_names = _og_ui_get_role_names($group_type, $bundle, $gid, $rid);

  // Fetch permissions for all roles or the one selected role.
  $role_permissions = og_role_permissions($role_names);

  // Store $role_names for use when saving the data.
  $form['role_names'] = array(
    '#type' => 'value',
    '#value' => $role_names,
  );

  // Render role/permission overview:
  $options = array();
  $module_info = system_get_info('module');

  // Get a list of all the modules implementing a hook_permission() and sort by
  // display name.
  $permissions_by_module = array();
  foreach (og_get_permissions() as $perm => $value) {
    $module = $value['module'];
    $permissions_by_module[$module][$perm] = $value;
  }

  asort($permissions_by_module);

  foreach ($permissions_by_module as $module => $permissions) {
    $form['permission'][] = array(
      '#markup' => $module_info[$module]['name'],
      '#id' => $module,
    );

    foreach ($permissions as $perm => $perm_item) {
      // Fill in default values for the permission.
      $perm_item += array(
        'description' => '',
        'restrict access' => FALSE,
        'warning' => !empty($perm_item['restrict access']) ? t('Warning: Give to trusted roles only; this permission has security implications in the group context.') : '',
      );
      // If the user can manage permissions, but does not have administer
      // group permission, hide restricted permissions from them. This
      // prevents users from escalating their privileges.
      if ($gid && ($perm_item['restrict access'] && !og_user_access($group_type, $gid, 'administer group'))) {
        continue;
      }

      $options[$perm] = '';
      $form['permission'][$perm] = array(
        '#type' => 'item',
        '#markup' => $perm_item['title'],
        '#description' => theme('user_permission_description', array('permission_item' => $perm_item)),
      );
      foreach ($role_names as $rid => $name) {
        // Builds arrays for checked boxes for each role
        if (isset($role_permissions[$rid][$perm])) {
          $status[$rid][] = $perm;
        }
      }
    }
  }

  // Have to build checkboxes here after checkbox arrays are built
  foreach ($role_names as $rid => $name) {
    $form['checkboxes'][$rid] = array(
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => isset($status[$rid]) ? $status[$rid] : array(),
      '#attributes' => array('class' => array('rid-' . $rid)),
    );
    $form['role_names'][$rid] = array('#markup' => check_plain($name), '#tree' => TRUE);
  }


  if (!$gid || !og_is_group_default_access($group_type, $gid)) {
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save permissions'),
      '#submit' => array('og_ui_admin_permissions_submit'),
    );
  }
  $form['#after_build'][] = 'og_ui_admin_permissions_after_build';
  return $form;
}

/**
 * Helper function to disable the permissions checkboxes.
 *
 * Every permission can declare to which roles it applies, as some don't make
 * sense if applied to anonymous or authenticated user (e.g. subscribe to group
 * should appear only to anonymous members).
 * A second case for disabling is when using deafult roles and permissions, and
 * the user is browsing a group's permissions page.
 */
function og_ui_admin_permissions_after_build($form) {
  $group_type = $form['group_type']['#value'];
  $gid = $form['gid']['#value'];
  $bundle = $form['bundle']['#value'];

  $role_names = $form['role_names']['#value'];

  // Indicate if all checkboxes should be disabled.
  $disable = $gid && og_is_group_default_access($group_type, $gid);

  // Authenticated roles.
  $roles = og_roles($group_type, $bundle, $gid);
  $anon_rid = array_search(OG_ANONYMOUS_ROLE, $roles);

  // Remove the anonymous member from the authenticated roles.
  unset($roles[$anon_rid]);
  $auth_rids = array_keys($roles);

  foreach (og_get_permissions() as $key => $perm) {
    $perm['roles'] = drupal_map_assoc($perm['roles']);

    if ($disable || (empty($perm['roles']) || empty($perm['roles'][OG_ANONYMOUS_ROLE]))) {
      if (in_array($anon_rid, array_keys($role_names))) {
        $form['checkboxes'][$anon_rid][$key]['#attributes']['disabled'] = TRUE;
      }
    }
    if ($disable || (empty($perm['roles']) || empty($perm['roles'][OG_AUTHENTICATED_ROLE]))) {
      foreach ($auth_rids as $auth_rid) {
        if (in_array($auth_rid, array_keys($role_names))) {
          $form['checkboxes'][$auth_rid][$key]['#attributes']['disabled'] = TRUE;
        }
      }
    }
  }

  return $form;
}

/**
 * Save permissions selected on the administer permissions page.
 *
 * @see og_user_admin_permissions()
 */
function og_ui_admin_permissions_submit($form, &$form_state) {
  foreach ($form_state['values']['role_names'] as $rid => $name) {
    og_role_change_permissions($rid, $form_state['values'][$rid]);
  }

  drupal_set_message(t('The changes have been saved.'));
}

/**
 * Theme the administer permissions page.
 *
 * @ingroup themeable
 */
function theme_og_ui_admin_permissions($variables) {
  $form = $variables['form'];
  $role_names = $form['role_names']['#value'];

  foreach (element_children($form['permission']) as $key) {
    $row = array();
    // Module name.
    if (is_numeric($key)) {
      $row[] = array('data' => drupal_render($form['permission'][$key]), 'class' => array('module'), 'id' => 'module-' . $form['permission'][$key]['#id'], 'colspan' => count($form['role_names']['#value']) + 1);
    }
    else {
      // Permission row.
      $row[] = array(
        'data' => drupal_render($form['permission'][$key]),
        'class' => array('permission'),
      );

      foreach (element_children($form['checkboxes']) as $rid) {
        $form['checkboxes'][$rid][$key]['#title'] = $role_names[$rid] . ': ' . $form['permission'][$key]['#markup'];
        $form['checkboxes'][$rid][$key]['#title_display'] = 'invisible';
        $row[] = array('data' => drupal_render($form['checkboxes'][$rid][$key]), 'class' => array('checkbox'));
      }
    }
    $rows[] = $row;
  }
  $header[] = (t('Permission'));
  foreach (element_children($form['role_names']) as $rid) {
    $header[] = array('data' => drupal_render($form['role_names'][$rid]), 'class' => array('checkbox'));
  }
  $output = '';
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'permissions')));
  $output .= drupal_render_children($form);
  return $output;
}

/**
 * Allow site admin to add or remove group fields from fieldable entities.
 */
function og_ui_field_settings($form, &$form_state) {
  $form = array();
  $options = array();

  $options = array();
  foreach (entity_get_info() as $entity_type => $entity_info) {
    if (empty($entity_info['fieldable'])) {
      continue;
    }
    foreach($entity_info['bundles'] as $bundle_name => $bundle) {
      // Prefix the bundle name with the entity type.
      $entity_name = check_plain("$entity_info[label] ($entity_type)");
      $options[$entity_name][$entity_type . ':' . $bundle_name] = filter_xss($bundle['label']);
    }
  }

  $form['bundle'] = array(
    '#title' => t('Bundles'),
    '#type' => 'select',
    '#options' => $options,
  );

  $options = array();
  foreach (og_fields_info() as $field_name => $field) {
    foreach ($field['type'] as $type) {
      $type_name = $type == 'group' ? t('Group') : t('Group content');
      $options[$type_name][$field_name] = filter_xss($field['instance']['label']);
    }
  }

  $selected_field_name = !empty($form_state['values']['field_type']) ? $form_state['values']['field_type'] : OG_AUDIENCE_FIELD;
  $selected_og_info = og_fields_info($selected_field_name);

  $form['field_info_wrapper'] = array(
    '#prefix' => '<div id="field-info-wrapper">',
    '#suffix' => '</div>',
    '#parents' => array('field_info_wrapper'),
    '#type' => 'fieldset',
  );

  $form['field_info_wrapper']['field_type'] = array(
    '#title' => t('Fields'),
    '#type' => 'select',
    '#options' => $options,
    '#required' => TRUE,
    '#default_value' => $selected_field_name,
    '#ajax' => array(
      'callback' => 'og_ui_admin_fields_ajax_callback',
      'wrapper' => 'field-info-wrapper',
    ),
  );

  $form['field_info_wrapper']['description'] = array(
    '#markup' => $selected_og_info['description'],
  );

  if (!empty($selected_og_info['multiple'])) {
    $form['field_info_wrapper']['field_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Field name'),
      '#description' => t('This field type supports adding multiple instances on the same bundle (i.e. the field name is not hardcoded).'),
      '#required' => TRUE,
      '#maxlength' => 32,
      '#default_value' => $selected_field_name,
    );
  }
  else {
    // Pass the field name as a value.
    $form['field_name_wrapper']['field_name'] = array(
      '#type' => 'value',
      '#value' => $selected_field_name,
    );
  }

  $field_enabled = array();
  $og_fields = og_fields_info();

  $og_fields_name = array_keys($og_fields);

  $entity_info = entity_get_info();

  // Get the fields that exist in the bundle.
  foreach (field_info_fields() as $field_name => $field) {
    if (in_array($field_name, $og_fields_name) && !empty($field['bundles'])) {
      foreach ($field['bundles'] as $entity_type => $bundles) {
        foreach ($bundles as $bundle) {
          $field_enabled[$entity_type][$bundle][] = $field_name;
        }
      }
    }
  }

  if ($field_enabled) {
    $form['group_fields'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => 99,
    );

    // Show all the group fields of each bundle.
    foreach ($field_enabled as $entity_type => $bundles) {
      foreach ($bundles as $bundle => $fields) {
        $options = array();
        $bundles = field_info_bundles($entity_type);
        $form['group_fields_' . $entity_type . '_' . $bundle] = array(
          '#type' => 'fieldset',
          '#title' => t('@bundle - @entity entity', array('@bundle' => $bundles[$bundle]['label'], '@entity' => $entity_info[$entity_type]['label'])),
          '#collapsible' => TRUE,
          '#group' => 'group_fields',
        );
        foreach ($fields as $field_name) {
          $options[] = array(
            check_plain($og_fields[$field_name]['instance']['label']),
            filter_xss($og_fields[$field_name]['description']),
            l(t('Delete'), "admin/config/group/fields/$entity_type/$bundle/$field_name/delete"),
          );
        }

        $header = array(t('Field'), t('Description'), t('Operations'));
        $form['group_fields_' . $entity_type . '_' . $bundle]['fields'] = array(
          '#markup' => theme('table', array('header' => $header, 'rows' => $options)),
        );

      }
    }
  }
  else {
    $form['group_fields'] = array(
      '#markup' => t('There are no Group fields attached to any bundle yet.'),
    );
  }

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Add field'),
  );

  return $form;
}

/**
 * AJAX callback to to return the field name if the field supports "mulitple"
 * instances, on the same bundle (e.g. OG_AUDIENCE_FIELD).
 */
function og_ui_admin_fields_ajax_callback($form, &$form_state) {
  return $form['field_info_wrapper'];
}

/**
 * Validate handler; Check if field can be attached to bundle.
 */
function og_ui_field_settings_validate($form, &$form_state) {
  list($entity_type, $bundle) = explode(':', $form_state['values']['bundle']);
  $field_name = $form_state['values']['field_name'];

  $og_field = og_fields_info($form_state['values']['field_type']);
  $bundles = field_info_bundles($entity_type);
  $entity_info = entity_get_info($entity_type);

  $params = array(
    '%field' => $og_field['instance']['label'],
    '%bundle' => $bundles[$bundle]['label'],
    '%entity' => $entity_info['label'],
  );

  if (field_info_instance($entity_type, $field_name, $bundle)) {
    form_set_error('bundles', t('Field %field already exists in %bundle.', $params));
  }

  // Check field can be attached to entity type.
  if (!empty($og_field['entity']) && !in_array($entity_type, $og_field['entity'])) {
    $items = array();
    foreach ($og_field['entity'] as $entity_type) {
      $info = entity_get_info($entity_type);
      $items[] = $info['label'];
    }
    form_set_error('bundles', t('Field %field can only be attached to %entities entity bundles.', $params + array('%entities' => implode(', ', $items))));
  }

}

/**
 * Submit handler; Attach field can to bundle.
 */
function og_ui_field_settings_submit($form, &$form_state) {
  list($entity_type, $bundle) = explode(':', $form_state['values']['bundle']);
  $field_name = $form_state['values']['field_name'];
  $field_type = $form_state['values']['field_type'];

  $og_field = og_fields_info($field_type);

  og_create_field($field_name, $entity_type, $bundle, $og_field);

  $params = array(
    '@field-type' => $og_field['instance']['label'],
    '@field-name' => $field_name,
    '@bundle' => $bundle,
  );

  if ($field_name == $field_type) {
    drupal_set_message(t('Added field @field-type to @bundle.', $params));
  }
  else {
    drupal_set_message(t('Added field @field-type (@field-name) to @bundle.', $params));
  }
}


/**
 * Menu callback; present a form for removing a field instance from a bundle.
 */
function og_ui_field_delete_form($form, &$form_state, $instance) {
  module_load_include('inc', 'field_ui', 'field_ui.admin');
  $output = field_ui_field_delete_form($form, $form_state, $instance);

  $output['actions']['cancel'] = array(
    '#markup' => l(t('Cancel'), 'admin/config/group/fields'),
  );

  return $output;
}


function og_ui_field_delete_form_submit($form, &$form_state) {
  module_load_include('inc', 'field_ui', 'field_ui.admin');
  field_ui_field_delete_form_submit($form, $form_state);
  $form_state['redirect'] = 'admin/config/group/fields';
}
