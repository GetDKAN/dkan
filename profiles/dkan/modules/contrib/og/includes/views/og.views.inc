<?php


/**
 * @file
 * Provides support for the Views module.
 */

/**
 * "OG membership" entity Views definitions.
 */
class OgMembershipViewsController extends EntityDefaultViewsController {

  /**
   * Override views_data().
   */
  public function views_data() {
    $data = parent::views_data();

    $data['og_membership']['state']['filter']['handler'] = 'og_handler_filter_group_audience_state';
    $data['og_membership']['state']['field']['handler'] = 'og_handler_field_group_audience_state';

    $data['og_membership']['etid'] = array(
      'title' => t('Entity id'),
      'help' => t('Og membership "etid" property.'),
      'field' => array(
        'handler' => 'views_handler_field_numeric',
        'click sortable' => TRUE,
       ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_numeric',
      ),
      'argument' => array(
        'handler' => 'views_handler_argument_numeric',
      ),
    );

    // Improve help text.
    $data['og_membership']['gid'] = $data['og_membership']['etid'];
    $data['og_membership']['gid']['title'] = t('Group ID');
    $data['og_membership']['gid']['help'] = t('Og membership "gid" property.');

    $data['og_membership']['og_roles'] = array(
      'title' => t('OG user roles in group'),
      'help' => t('Show all the roles a user belongs to in a group. Requires a relationship to users to be present.'),
      // This is a dummy field, so point it to a real field that we need - the
      // group ID
      'real field' => 'gid',
      'field' => array(
        'handler' => 'og_handler_field_user_roles',
      ),
    );

    $data['og_membership']['og_users_roles'] = array(
      'title' => t('OG Roles from membership'),
      'help' => t('The OG Roles associated with the OG membership'),
      // Provide a possible relationship to roles through uid.
      'relationship' => array(
        'label' => t('OG Roles from OG membership'),
        'base' => 'og_users_roles',
        'base field' => 'uid',
        'relationship field' => 'etid',
      ),
    );

    $data['og_role'] = array(
      'table' => array(
        'group' => t('OG user roles'),
        'join' => array(
          // Attach automatically to og_users_roles relations.
          'og_users_roles' => array(
            'left_field' => 'rid',
            'field' => 'rid',
          ),
        )
      )
    );
    $data['og_role']['name'] = array(
      'title' => t('Role Name'),
      'help' => t('The OG role name.'),
      'filter' => array(
        'handler' => 'og_handler_filter_user_roles',
      ),
    );

    // Link to edit membership
    $data['og_membership']['edit_membership'] = array(
      'field' => array(
        'title' => t('Edit link'),
        'help' => t('Provide a simple link to edit the membership.'),
        'handler' => 'og_handler_field_og_membership_link_edit',
      ),
    );

    return $data;
  }
}

/**
 * Implements hook_views_data_alter().
 */
function og_views_data_alter(&$data) {
  $group_content_entities = og_get_all_group_content_entity();
  $group_entities = og_get_all_group_entity();

  foreach (entity_get_info() as $entity_type => $info) {
    if (empty($group_content_entities[$entity_type]) && empty($group_entities[$entity_type])) {
      continue;
    }

    // OG membership relationship.
    $data[$info['base table']]['og_membership_rel'] = array(
      'group' => t('OG membership'),
      'title' => t('OG membership from @entity', array('@entity' => ucfirst($info['label']))),
      'help' => t('The OG membership associated with the @entity entity.', array('@entity' => ucfirst($info['label']))),
      'relationship' => array(
        // Pass the entity to the handler.
        'entity' => $entity_type,
        'handler' => 'og_handler_relationship',
        'label' => t('OG membership from @entity', array('@entity' => $entity_type)),
        'base' => 'og_membership',
        'base field' => 'etid',
        'relationship field' => $info['entity keys']['id'],
      ),
    );

    // The OG membership group.
    $data[$info['base table']]['og_membership_rel_group'] = array(
      'group' => t('OG membership'),
      'title' => t('OG membership from @entity group', array('@entity' => ucfirst($info['label']))),
      'help' => t('The OG membership associated with the @entity group', array('@entity' => ucfirst($info['label']))),
      'relationship' => array(
        'group_type' => $entity_type,
        'handler' => 'og_handler_relationship',
        'label' => t('OG membership from @entity group', array('@entity' => $entity_type)),
        'base' => 'og_membership',
        'base field' => 'gid',
        'relationship field' => $info['entity keys']['id'],
      ),
    );

    // OG membership's related entity.
    $data['og_membership']['og_membership_related_' . $entity_type] = array(
      'group' => t('OG membership'),
      'title' => t('@entity from OG membership', array('@entity' => ucfirst($info['label']))),
      'help' => t('The @entity entity that is associated with the OG membership.', array('@entity' => $info['label'])),
      'relationship' => array(
        'entity' => $entity_type,
        'handler' => 'og_handler_relationship',
        'label' => t('@entity from OG membership', array('@entity' => $entity_type)),
        'base' => $info['base table'],
        'base field' => $info['entity keys']['id'],
        'relationship field' => 'etid',
      ),
    );

    // OG membership's related group.
    $data['og_membership']['og_membership_related_' . $entity_type . '_group'] = array(
      'group' => t('OG membership'),
      'title' => t('Group @entity from OG membership', array('@entity' => ucfirst($info['label']))),
      'help' => t('The @entity group that is associated with the OG membership.', array('@entity' => $info['label'])),
      'relationship' => array(
        'group_type' => $entity_type,
        'handler' => 'og_handler_relationship',
        'label' => t('Group @entity from OG membership', array('@entity' => $entity_type)),
        'base' => $info['base table'],
        'base field' => $info['entity keys']['id'],
        'relationship field' => 'gid',
      ),
    );
  }

  $data['field_data_group_audience']['user_roles'] = array(
    'group' => t('Group'),
    'title' => t('Group user roles'),
    'help' => t('Show all the roles a user belongs to in a group.'),
    // This is a dummy field, so point it to a real field that we need - the
    // group ID
    'real field' => 'group_audience_gid',
    'field' => array(
      'handler' => 'og_handler_field_user_roles',
    ),
  );

  $data['field_data_group_audience']['og_permissions'] = array(
    'group' => t('Group'),
    'title' => t('Group permissions'),
    'help' => t('Filter by group permissions.'),
    // This is a dummy field, so point it to a real field that we need - the
    // group ID
    'real field' => 'group_audience_gid',
    'field' => array(
      'handler' => 'og_handler_field_group_permissions',
    ),
  );
}

/**
 * Implements hook_views_plugins().
 */
function og_views_plugins() {
  return array(
    'argument validator' => array(
      'og' => array(
        'title' => t('OG group'),
        'handler' => 'og_plugin_argument_validate_group',
        'path' => drupal_get_path('module', 'og') . '/includes/views/handlers',
      ),
    ),
    'argument default' => array(
      'og_user_groups' => array(
        'title' => t('The OG groups of the logged in user'),
        'handler' => 'og_plugin_argument_default_user_groups',
      ),
    ),
  );
}
