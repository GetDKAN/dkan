<?php

/**
 * @file
 * Rules integration for the Organic groups module.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implementation of hook_rules_event_info().
 *
 * Adds some additional event that make reacting on user subscriptions easier.
 */
function og_rules_event_info() {
  $defaults = array(
    'variables' => array(
      'og_membership' => array(
        'type' => 'og_membership',
        'label' => t("The user's OG membership"),
      ),
      'account' => array(
        'type' => 'user',
        'label' => t('User'),
      ),
    ),
    'group' => t('OG membership'),
  );
  return array(
    'og_user_insert' => $defaults + array(
      'label' => t('User has become a group member'),
      'help' => t("A user has become a group member, but might have been not yet approved."),
    ),
    'og_user_approved' => $defaults + array(
      'label' => t('User membership has been approved'),
    ),
    'og_user_blocked' => $defaults + array(
      'label' => t('User membership has been blocked'),
    ),
    'og_user_delete' => $defaults + array(
      'label' => t('User has been removed from group'),
      'help' => t("A user has been removed from group and is no longer a group member."),
    ),
    'og_role_grant' => array_merge_recursive($defaults, array(
      'label' => t('OG role granted to user'),
      'help' => t("An OG role has been granted to this user."),
      'variables' => array(
        'og_rid' => array(
          'type' => 'integer',
          'label' => t('OG role ID'),
          'description' => t('The id of the OG user role.'),
        ),
      ),
    )),
    'og_role_revoke' => array_merge_recursive($defaults, array(
      'label' => t('OG role revoked from user'),
      'help' => t("An OG role has been revoked from this user."),
      'variables' => array(
        'og_rid' => array(
          'type' => 'integer',
          'label' => t('OG role ID'),
          'description' => t('The id of the OG user role.'),
        ),
      ),
    )),
  );
}

/**
 * Implements hook_rules_action_info().
 */
function og_rules_action_info() {
  $items = array();
  $items['og_get_members'] = array(
    'label' => t('Get group members from group audience'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'group_content' => array(
        'type' => 'entity',
        'label' => t('Group content'),
        'description' => t('The group content determining the group audience.'),
      ),
      // @todo: Add option to filter by member-state?
    ),
    'provides' => array(
      'group_members' => array('type' => 'list<user>', 'label' => t('List of group members')),
    ),
    'base' => 'og_rules_get_members',
    'access callback' => 'og_rules_integration_access',
  );

  $items['og_get_managers'] = array(
    'label' => t('Get group managers from group audience'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'group_content' => array(
        'type' => 'entity',
        'label' => t('Group content'),
        'description' => t('The group content determining the group audience.'),
      ),
    ),
    'provides' => array(
      'group_managers' => array('type' => 'list<user>', 'label' => t('List of group managers')),
    ),
    'base' => 'og_rules_get_managers',
    'access callback' => 'og_rules_integration_access',
  );

  $items['og_get_group_content'] = array(
    'label' => t('Get group content from a group'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'group' => array(
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Group'),
        'description' => t('The group for which to fetch content.'),
        'wrapped' => TRUE,
      ),
      'entity_type' => array(
        'type' => 'token',
        'label' => t('Entity type'),
        'description' => t('The entity type of the content which is to be fetched.'),
        'options list' => 'og_get_fieldable_entity_list',
      ),
    ),
    'provides' => array(
      'group_content' => array('type' => 'list<entity>', 'label' => t('Group content')),
    ),
    'base' => 'og_rules_get_group_content',
    'access callback' => 'og_rules_integration_access',
  );

  $items['og_group_content_add'] = array(
    'label' => t('Add entity to group'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'entity' => array(
        // Do not use type 'entity' but restrict the type to group content type.
        'type' => array_keys(og_get_all_group_content_entity()),
        'label' => t('Entity'),
        'description' => t('The group content which is to be added to a group.'),
        'wrapped' => TRUE,
      ),
      'group' => array(
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Group'),
        'wrapped' => TRUE,
      ),
    ),
    'base' => 'og_rules_add_entity_to_group',
    'access callback' => 'og_rules_integration_access',
  );

  $items['og_group_content_remove'] = array(
    'label' => t('Remove entity from group'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'entity' => array(
        // Do not use type 'entity' but restrict the type to group content type.
        'type' => array_keys(og_get_all_group_content_entity()),
        'label' => t('Entity'),
        'description' => t('The entity which is to be removed from a group.'),
        'wrapped' => TRUE,
      ),
      'group' => array(
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Group'),
        'wrapped' => TRUE,
      ),
    ),
    'base' => 'og_rules_remove_entity_from_group',
    'access callback' => 'og_rules_integration_access',
  );

  // For UX also provide separate actions for user subcriptions although it is
  // technically the same as adding entities to groups.

  $items['og_subcribe_user'] = array(
    'label' => t('Subscribe user to group'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'user' => array(
        'type' => 'user',
        'label' => t('User'),
        'description' => t('The user who will be subscribed.'),
        'wrapped' => TRUE,
      ),
      'group' => array(
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Group'),
        'wrapped' => TRUE,
      ),
      // @todo: Add membership-type setting + add in the membership-entity
      // fields via the info_alter callback + reload the form once the
      // membership-type has been chosen.
      // Then, we probably also want to provide the newly created membership
      // entity.
    ),
    'base' => 'og_rules_add_entity_to_group',
    'access callback' => 'og_rules_integration_access',
  );

  $items['og_unsubscribe_user'] = array(
    'label' => t('Unsubscribe user from group'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'user' => array(
        'type' => 'user',
        'label' => t('User'),
        'description' => t('The user who will be unsubscribed.'),
        'wrapped' => TRUE,
      ),
      'group' => array(
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Group'),
        'wrapped' => TRUE,
      ),
    ),
    'base' => 'og_rules_remove_entity_from_group',
    'access callback' => 'og_rules_integration_access',
  );

  return $items;
}

/**
 * Action: Get group members from a group content.
 */
function og_rules_get_members($group_content) {
  if (!isset($group_content->og_membership)) {
    // Not a group content.
    return;
  }

  $members = array();
  foreach ($group_content->og_membership->value() as $og_membership) {
    // Get the group members the group content belongs to.
    $current_members = db_select('og_membership', 'om')
      ->fields('om', array('etid'))
      ->condition('om.gid', $og_membership->gid)
      ->condition('om.group_type', $og_membership->group_type)
      ->condition('om.entity_type', 'user')
      ->execute()
      ->fetchCol();

    $members = array_merge($members, $current_members);
  }
  // Remove duplicate items.
  $members = array_keys(array_flip($members));
  return array('group_members' => $members);
}

/**
 * Action: Get group managers from a group content.
 */
function og_rules_get_managers($group_content) {
  if (!isset($group_content->og_membership)) {
    // Not a group content.
    return;
  }

  $return = array();
  foreach ($group_content->og_membership->value() as $og_membership) {
    $group = entity_load_single($og_membership->group_type, $og_membership->gid);
    if(!empty($group->uid)){
      $return[] = $group->uid;
    }
  }

  // Remove duplicate items.
  $return = array_keys(array_flip($return));
  return array('group_managers' => $return);
}

/**
 * Action: Fetch group content of a certain entity type.
 */
function og_rules_get_group_content(EntityDrupalWrapper $group, $entity_type) {
  $memberships = og_membership_load_multiple(FALSE, array('gid' => $group->getIdentifier(), 'entity_type' => $entity_type));
  $entities = array();
  foreach ($memberships as $membership) {
    $entities[] = $membership->etid;
  }
  $entities = entity_load($entity_type, $entities);
  return array('group_content' => $entities);
}

/**
 * "Fetch group content" action info alter callback.
 */
function og_rules_get_group_content_info_alter(&$element_info, $element) {
  if (isset($element->settings['entity_type']) && $entity_type = $element->settings['entity_type']) {
    $element_info['provides']['group_content']['type']  = "list<$entity_type>";
  }
}

/**
 * Action: Add entity to group.
 */
function og_rules_add_entity_to_group(EntityDrupalWrapper $entity, EntityDrupalWrapper $group) {
  // TODO: Add field-name.
  $values = array(
    'entity_type' => $entity->type(),
    'entity' => $entity->value(),
  );
  og_group($group->type(), $group->getIdentifier(), $values);
}

/**
 * Action: Remove entity from group.
 */
function og_rules_remove_entity_from_group(EntityDrupalWrapper $entity, EntityDrupalWrapper $group) {
  og_ungroup($group->type(), $group->getIdentifier(), $entity->type(), $entity->value());
}

/**
 * OG Rules integration access callback.
 */
function og_rules_integration_access($type, $name) {
  // Grant everyone access to conditions.
  return $type == 'condition' || user_access('administer group');
}

/**
 * Implements hook_rules_condition_info().
 */
function og_rules_condition_info() {
  $items = array();
  $items['og_user_has_permission'] = array(
    'label' => t('User has group permission'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'permission' => array(
        'type' => 'text',
        'label' => t('Permission'),
        'description' => t('The permission to check for.'),
        'options list' => 'og_rules_user_has_permission_options_list',
        'restriction' => 'input',
      ),
      'group' => array(
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Group'),
        'description' => t('The group for which permission should be checked.'),
        'wrapped' => TRUE,
      ),
      'account' => array(
        'type' => 'user',
        'label' => t('User'),
      ),
    ),
    'base' => 'og_rules_user_has_permission',
    'access callback' => 'og_rules_integration_access',
  );
  $items['og_user_in_group'] = array(
    'label' => t('User is group member'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'account' => array(
        'type' => 'user',
        'label' => t('User'),
        'wrapped' => TRUE,
      ),
      'group' => array(
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Group'),
        'wrapped' => TRUE,
      ),
      'states' => array(
        'type' => 'list<integer>',
        'label' => t('Membership states'),
        'restriction' => 'input',
        'options list' => 'og_group_content_states',
        'optional' => TRUE,
        'default value' => array(OG_STATE_ACTIVE),
      ),
    ),
    'base' => 'og_rules_condition_user_in_group',
    'access callback' => 'og_rules_integration_access',
  );
  $items['og_entity_in_group'] = array(
    'label' => t('Entity is in group'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'entity' => array(
        // Do not use type 'entity' but restrict the type to group content type.
        'type' => array_keys(og_get_all_group_content_entity()),
        'label' => t('Group content entity'),
        'description' => t('The group content which will be checked.'),
        'wrapped' => TRUE,
      ),
      'group' => array(
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Group'),
        'wrapped' => TRUE,
      ),
    ),
    'base' => 'og_rules_condition_entity_in_group',
    'access callback' => 'og_rules_integration_access',
  );
  $items['og_entity_is_group'] = array(
    'label' => t('Entity is group'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'entity' => array(
        // Do not use type 'entity' but restrict the type to group type.
        'type' => array_keys(og_get_all_group_entity()),
        'label' => t('Entity'),
        'wrapped' => TRUE,
      ),
    ),
    'base' => 'og_rules_entity_is_group',
    'access callback' => 'og_rules_integration_access',
  );
  $items['og_entity_is_group_content'] = array(
    'label' => t('Entity is group content'),
    'group' => t('Organic groups'),
    'parameter' => array(
      'entity' => array(
        // Do not use type 'entity' but restrict the type to group content type.
        'type' => array_keys(og_get_all_group_content_entity()),
        'label' => t('Entity'),
        'wrapped' => TRUE,
      ),
    ),
    'base' => 'og_rules_entity_is_group_content',
    'access callback' => 'og_rules_integration_access',
  );
  return $items;
}

/**
 * Condition: User has group permisison.
 */
function og_rules_user_has_permission($permission, EntityDrupalWrapper $group, $account) {
  return og_user_access($group->type(), $group->getIdentifier(), $permission, $account);
}

/**
 * Condition user has group permission options list callback for permissions.
 */
function og_rules_user_has_permission_options_list() {
  $perms = array();
  foreach (og_get_permissions() as $perm => $value) {
    // By keeping them keyed by module we can use optgroups with the
    // 'select' type.
    $perms[$value['module']][$perm] = strip_tags($value['title']);
  }
  return $perms;
}

/**
 * Condition: Entity is in group.
 */
function og_rules_condition_entity_in_group(EntityDrupalWrapper $entity, EntityDrupalWrapper $group) {
  return og_is_member($group->type(), $group->getIdentifier(), $entity->type(), $entity->value());
}

/**
 * Condition: User is in group.
 */
function og_rules_condition_user_in_group(EntityDrupalWrapper $entity, EntityDrupalWrapper $group, $states = array(OG_STATE_ACTIVE)) {
  return og_is_member($group->type(), $group->getIdentifier(), $entity->type(), $entity->value(), $states);
}

/**
 * Condition "User is in group" help.
 */
function og_rules_condition_user_in_group_help() {
  return t('Evaluates to TRUE if the user is a member of the group and the membership meets the configured valid membership state.');
}

/**
 * Condition: Entity is group.
 */
function og_rules_entity_is_group(EntityDrupalWrapper $entity) {
  return (bool) og_is_group($entity->type(), $entity->getIdentifier());
}

/**
 * Condition: "Entity is group" help.
 */
function og_rules_entity_is_group_help() {
  return t('Determines whether the entity is an active group.');
}

/**
 * Condition: Entity is group content.
 */
function og_rules_entity_is_group_content(EntityDrupalWrapper $entity) {
  return (bool) og_is_group_content_type($entity->type(), $entity->getBundle());
}

/**
 * Condition: "Entity is group content" help.
 */
function og_rules_entity_is_group_content_help() {
  return t('Determines whether the entity is group content, i.e. it can be or is used as group content.');
}

/**
 * @}
 */
