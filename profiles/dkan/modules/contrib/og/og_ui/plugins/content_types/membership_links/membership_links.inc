<?php

/**
 * Plugin definition.
 */
$plugin = array(
  'title' => t('Membership links'),
  'description' => t('Show membership unsubscribe and subscribe link(s) to a given group.'),
  'required context' => new ctools_context_required(t('OG Group'), 'entity:group'),
  'defaults' => array(
    'membership_types' => array(),
    'include_name' => FALSE,
  ),
  'category' => t('Organic groups'),
);

/**
 * Render callback.
 */
function og_ui_membership_links_content_type_render($subtype, $conf, $args, $context) {
  global $user;
  if (empty($context->data)) {
    return FALSE;
  }

  $group = clone $context->data;
  $entity = $group->getEntity();
  if (!og_get_membership($group->gid, 'user', $user->uid)) {
    if (!og_user_access($group->gid, 'subscribe') && !og_user_access($group->gid, 'subscribe without approval')) {
      // User doesn't have access.
      return;
    }

    $subscribe_access = og_user_access($group->gid, 'subscribe');

    $types = array();
    foreach (og_ui_subscribe_get_types($conf) as $type => $description) {
      $types[] = array(
        'type' => $type,
        'description' => $description,
        'approval needed' => !$subscribe_access,
      );
    }

    $items = array();
    $options = array();
    $count = count($types);
    foreach ($types as $type) {
      $subscribe_path = 'group/' . $group->entity_type . '/' . $group->etid . '/subscribe/' . $type['type'];
      if ($user->uid) {
        $path = $subscribe_path;
      }
      else {
        // User is anonymous, so redirect to subscribe after login.
        $path = 'user/login';
        $options['query']['destination'] = $subscribe_path;
      }

      if ($count > 1 || $conf['include_name']) {
        $params = array('@type' => $type['description']);

        if ($type['approval needed']) {
          $items[] = array('data' => l(t('Request "@type" membership to group', $params), $path, $options));
        }
        else {
          $items[] = array('data' => l(t('Subscribe to group using "@type" membership', $params), $path, $options));
        }
      }
      else {
        if ($type['approval needed']) {
          $items[] = array('data' => l(t('Request group membership'), $path, $options));
        }
        else {
          $items[] = array('data' => l(t('Subscribe to group'), $path, $options));
        }
      }
    }
    $title = format_plural($count, 'Membership link', 'Membership links');
  }
  else {
    // User already has membership.
    if (!og_user_access($group->gid, 'unsubscribe')) {
      // User can't unsubscribe.
      return;
    }

    if (!empty($entity->uid) && $entity->uid == $user->uid) {
      // User is the group manager.
      return;
    }

    $items[] = array('data' => l(t('Unsubscribe from group'), 'group/' . $group->entity_type . '/' . $group->etid . '/unsubscribe'));
    $title = t('Unsubscribe link');
  }

  $block = new stdClass();
  $block->module = 'og_ui';
  $block->title = $title;
  $block->content = array('#theme' => 'item_list', '#items' => $items);
  return $block;
}

/**
 * Edit form.
 */
function og_ui_membership_links_content_type_edit_form($form, &$form_state) {
  $conf = $form_state['conf'];
  $options = array();
  foreach (og_membership_type_load() as $type) {
    $options[$type->name] = $type->description;
  }

  $form['membership_types'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Membership types'),
    '#description' => t('Select the membership types. If none are selected, all the accessible will be presented.'),
    '#options' => $options,
    '#default_value' => $conf['membership_types'],
  );

  $form['include_name'] = array(
    '#type' => 'checkbox',
    '#title' => t('Include membership type name on single membership type'),
    '#description' => t('When disabled and there is only a single membership type enabled, the membership type name will not be appended to the subscribe link.'),
    '#default_value' => $conf['include_name'],
  );
  return $form;
}

/**
 * Submit form.
 */
function og_ui_membership_links_content_type_edit_form_submit($form, &$form_state) {
  $form_state['values']['membership_types'] = array_filter($form_state['values']['membership_types']);
  // Copy everything from our defaults.
  foreach (array_keys($form_state['plugin']['defaults']) as $key) {
    $form_state['conf'][$key] = $form_state['values'][$key];
  }
}


function og_ui_membership_links_content_type_admin_title($subtype, $conf, $context) {
  if (empty($conf['membership_types'])) {
    return t('Membership links for all accessible membership types');
  }
  else {
    $types = og_ui_subscribe_get_types($conf);
    return t('Membership links for @types membership types', array('@types' => implode(', ', $types)));
  }
}

/**
 * Get the membership types defined in the pane's $conf.
 */
function og_ui_subscribe_get_types($conf) {
  $options = array();
  foreach (og_membership_type_load() as $type) {
    if (empty($conf['membership_types']) || in_array($type->name, $conf['membership_types'])) {
      $options[$type->name] = $type->description;
    }
  }
  return $options;
}

