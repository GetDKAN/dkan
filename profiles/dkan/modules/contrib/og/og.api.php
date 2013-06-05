<?php


/**
 * @file
 * Hooks provided by the Organic groups module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Add group permissions.
 */
function hook_og_permission() {
  return array(
    'subscribe' => array(
      'title' => t('Subscribe user to group'),
      'description' => t("Allow user to be a member of a group (approval required)."),
      // Determine to which role to limit the permission. For example the
      // "subscribe" can't be assigned only to a non-member, as a member doesn't
      // need it.
      'roles' => array(OG_ANONYMOUS_ROLE),
      // Determine to which roles the permissions will be enabled by default.
      'default role' => array(OG_ANONYMOUS_ROLE),
    ),
  );
}

/**
 * Alter the organic groups permissions.
 *
 * @param $perms
 *   The permissions passed by reference.
 */
function hook_og_permission_alter(&$perms) {

}


/**
 * Set a default role that will be used as a global role.
 *
 * A global role, is a role that is assigned by default to all new groups.
 */
function hook_og_default_roles() {
  return array('super admin');
}

/**
 * Alter the default roles.
 *
 * The anonymous and authenticated member roles are not alterable.
 *
 * @param $roles
 *   Array with the default roles name.
 */
function hook_og_default_roles_alter(&$roles) {
  // Remove a default role.
  unset($roles['super admin']);
}

/**
 * Allow modules to act upon new group role.
 *
 * @param $role
 *   The group role object.
 */
function hook_og_role_insert($role) {
}

/**
 * Allow modules to act upon existing group role update.
 *
 * @param $role
 *   The group role object.
 */
function hook_og_role_update($role) {

}

/**
 * Allow modules to act upon existing group role deletion.
 *
 * @param $role
 *   The deleted group role object. The object is actually a dummy, as the data
 *   is already deleted from the database. However, we pass the object to allow
 *   implementing modules to properly identify the deleted role.
 */
function hook_og_role_delete($role) {
}

/**
 * Allow modules to react upon a role being granted.
 *
 * @param $entity_type
 *   The entity type of the group in which a role has been granted.
 * @param $gid
 *   The group id of the group in which a role has been granted.
 * @param $uid
 *   The user id of the user to whom a role has been granted.
 * @param $rid
 *   The OG role id being granted to the user.
 */
function hook_og_role_grant($entity_type, $gid, $uid, $rid) {
}

/**
 * Allow modules to react upon a role being revoked.
 *
 * @param $entity_type
 *   The entity type of the group in which a role has been revoked.
 * @param $gid
 *   The group id of the group in which a role has been revoked.
 * @param $uid
 *   The user id of the user to whom a role has been revoked.
 * @param $rid
 *   The OG role id being revoked from the user.
 */
function hook_og_role_revoke($entity_type, $gid, $uid, $rid) {
}

/**
 * Give a notification about OG role permissions change.
 *
 * @param $role
 *   The role object of the changed role.
 * @param $grant
 *   A list of granted permission names.
 * @param $revoke
 *   A list of revoked permission names.
 */
function hook_og_role_change_permissions($role, $grant, $revoke) {
  if (!$role->gid) {
    drupal_set_message(t('Global group permissions granted for @role users: @permissions', array('@role' => $role->name, '@permissions' => implode(', ', $grant))));
  }
}

/**
 * Provide information about fields that are related to Organic groups.
 *
 * Using this info, Organic groups is aware of the fields, and allows adding
 * them to the correct bundle.
 *
 * - type: Array with the values "group" and/ or "group content". To define to
 *   which bundles the field may be attached.
 * - Description: The description of the field.
 * - field: The field info array as will be passed to field_create_field().
 * - instance: The field instance array as will be passed to
 *   field_info_instance().
 * - entity type: Optional; Array of the entity types this field can be attached
 *   to. The field will not be attachable to other entity types. Defaults to
 *   empty array.
 */
function hook_og_fields_info() {
  $items = array();
  $items[OG_GROUP_FIELD] = array(
    'type' => array('group'),
    'description' => t('Determine if this should be a group.'),
    'field' => array(
      'field_name' => OG_GROUP_FIELD,
      'no_ui' => TRUE,
      'type' => 'list_boolean',
      'cardinality' => 1,
      'settings' => array(
        'allowed_values' => array(0 => 'Not a group type', 1 => 'Group type'),
        'allowed_values_function' => '',
      ),
    ),
    'instance' => array(
      'label' => t('Group type'),
      'widget_type' => 'options_select',
      'required' => TRUE,
      // Make the group type default.
      'default_value' => array(0 => array('value' => 1)),
      'view modes' => array(
        'full' => array(
          'label' => t('Full'),
          'type' => 'og_group_subscribe',
          'custom settings' => FALSE,
        ),
        'teaser' => array(
          'label' => t('Teaser'),
          'type' => 'og_group_subscribe',
          'custom settings' => FALSE,
        ),
      ),
    ),
  );
  return $items;
}

/**
 * TODO
 */
function hook_og_fields_info_alter(&$fields_info) {

}

/**
 * Act upon organic groups cache clearing.
 *
 * This can be used by implementing modules, that need to clear the cache
 * as-well.
 */
function hook_og_invalidate_cache($gids = array()) {
  $caches = array(
    'og_foo',
    'og_bar',
  );

  foreach ($caches as $cache) {
    drupal_static_reset($cache);
  }
}

/**
 * Alter the permissions of a user in a group.
 *
 * @param $perm
 *   The permissions of a user, passed by reference.
 * @param $context
 *   Array with:
 *   - string: The permission asked for the user.
 *   - group_type: The entity type of the group.
 *   - group: The group object.
 *   - account: The user account.
 */
function hook_og_user_access_alter(&$perm, $context) {
  // If user ID 2 doesn't already have a permission then enable it.
  if (empty($perm['foo']) && $context['account']->uid = 2) {
    $perm['foo'] = TRUE;
  }
}



/**
* Acts on OG membership types being loaded from the database.
*
* This hook is invoked during OG membership type loading, which is handled by
* entity_load(), via the EntityCRUDController.
*
* @param array $og_membership_types
*   An array of OG membership type entities being loaded, keyed by id.
*
* @see hook_entity_load()
*/
function hook_og_membership_type_load(array $og_membership_types) {
  $result = db_query('SELECT pid, foo FROM {mytable} WHERE pid IN(:ids)', array(':ids' => array_keys($entities)));
  foreach ($result as $record) {
    $entities[$record->pid]->foo = $record->foo;
  }
}

/**
* Responds when a OG membership type is inserted.
*
* This hook is invoked after the OG membership type is inserted into the database.
*
* @param OgMembershipType $og_membership
*   The OG membership type that is being inserted.
*
* @see hook_entity_insert()
*/
function hook_og_membership_type_insert(OgMembershipType $og_membership) {
  db_insert('mytable')
    ->fields(array(
      'id' => entity_id('og_membership_type', $og_membership),
      'extra' => print_r($og_membership, TRUE),
    ))
    ->execute();
}

/**
* Acts on a OG membership type being inserted or updated.
*
* This hook is invoked before the OG membership type is saved to the database.
*
* @param OgMembershipType $og_membership
*   The OG membership type that is being inserted or updated.
*
* @see hook_entity_presave()
*/
function hook_og_membership_type_presave(OgMembershipType $og_membership) {
  $og_membership->name = 'foo';
}

/**
* Responds to a OG membership type being updated.
*
* This hook is invoked after the OG membership type has been updated in the database.
*
* @param OgMembershipType $og_membership
*   The OG membership type that is being updated.
*
* @see hook_entity_update()
*/
function hook_og_membership_type_update(OgMembershipType $og_membership) {
  db_update('mytable')
    ->fields(array('extra' => print_r($og_membership, TRUE)))
    ->condition('id', entity_id('og_membership_type', $og_membership))
    ->execute();
}

/**
* Responds to OG membership type deletion.
*
* This hook is invoked after the OG membership type has been removed from the database.
*
* @param OgMembershipType $og_membership
*   The OG membership type that is being deleted.
*
* @see hook_entity_delete()
*/
function hook_og_membership_type_delete(OgMembershipType $og_membership) {
  db_delete('mytable')
    ->condition('pid', entity_id('og_membership_type', $og_membership))
    ->execute();
}

/**
* Define default OG membership type configurations.
*
* @return
*   An array of default OG membership types, keyed by machine names.
*
* @see hook_default_og_membership_type_alter()
*/
function hook_default_og_membership_type() {
  $defaults['main'] = entity_create('og_membership_type', array(
    // �
  ));
  return $defaults;
}

/**
* Alter default OG membership type configurations.
*
* @param array $defaults
*   An array of default OG membership types, keyed by machine names.
*
* @see hook_default_og_membership_type()
*/
function hook_default_og_membership_type_alter(array &$defaults) {
  $defaults['main']->name = 'custom name';
}


/**
* Acts on OG memberships being loaded from the database.
*
* This hook is invoked during OG membership loading, which is handled by
* entity_load(), via the EntityCRUDController.
*
* @param array $og_memberships
*   An array of OG membership entities being loaded, keyed by id.
*
* @see hook_entity_load()
*/
function hook_og_membership_load(array $og_memberships) {
  $result = db_query('SELECT pid, foo FROM {mytable} WHERE pid IN(:ids)', array(':ids' => array_keys($entities)));
  foreach ($result as $record) {
    $entities[$record->pid]->foo = $record->foo;
  }
}

/**
* Responds when a OG membership is inserted.
*
* This hook is invoked after the OG membership is inserted into the database.
*
* @param OgMembership $og_membership
*   The OG membership that is being inserted.
*
* @see hook_entity_insert()
*/
function hook_og_membership_insert(OgMembership $og_membership) {
  db_insert('mytable')
    ->fields(array(
      'id' => entity_id('og_membership', $og_membership),
      'extra' => print_r($og_membership, TRUE),
    ))
    ->execute();
}

/**
* Acts on a OG membership being inserted or updated.
*
* This hook is invoked before the OG membership is saved to the database.
*
* @param OgMembership $og_membership
*   The OG membership that is being inserted or updated.
*
* @see hook_entity_presave()
*/
function hook_og_membership_presave(OgMembership $og_membership) {
  $og_membership->name = 'foo';
}

/**
* Responds to a OG membership being updated.
*
* This hook is invoked after the OG membership has been updated in the database.
*
* @param OgMembership $og_membership
*   The OG membership that is being updated.
*
* @see hook_entity_update()
*/
function hook_og_membership_update(OgMembership $og_membership) {
  db_update('mytable')
    ->fields(array('extra' => print_r($og_membership, TRUE)))
    ->condition('id', entity_id('og_membership', $og_membership))
    ->execute();
}

/**
* Responds to OG membership deletion.
*
* This hook is invoked after the OG membership has been removed from the database.
*
* @param OgMembership $og_membership
*   The OG membership that is being deleted.
*
* @see hook_entity_delete()
*/
function hook_og_membership_delete(OgMembership $og_membership) {
  db_delete('mytable')
    ->condition('pid', entity_id('og_membership', $og_membership))
    ->execute();
}


/**
 * @} End of "addtogroup hooks".
 */
