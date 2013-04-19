<?php


/**
 * OG selection handler.
 */
class OgSelectionHandler extends EntityReference_SelectionHandler_Generic {

  /**
   * Implements EntityReferenceHandler::getInstance().
   */
  public static function getInstance($field, $instance = NULL, $entity_type = NULL, $entity = NULL) {
    return new OgSelectionHandler($field, $instance, $entity_type, $entity);
  }

  /**
   * Override EntityReferenceHandler::settingsForm().
   */
  public static function settingsForm($field, $instance) {
    $form = parent::settingsForm($field, $instance);
    $entity_type = $field['settings']['target_type'];
    $entity_info = entity_get_info($entity_type);

    $bundles = array();
    foreach ($entity_info['bundles'] as $bundle_name => $bundle_info) {
      if (og_is_group_type($entity_type, $bundle_name)) {
        $bundles[$bundle_name] = $bundle_info['label'];
      }
    }

    if (!$bundles) {
      $form['target_bundles'] = array(
        '#type' => 'item',
        '#title' => t('Target bundles'),
        '#markup' => t('Error: The selected "Target type" %entity does not have bundles that are a group type', array('%entity' => $entity_info['label'])),
      );
    }
    else {
      $settings = $field['settings']['handler_settings'];
      $settings += array(
        'target_bundles' => array(),
        'membership_type' => OG_MEMBERSHIP_TYPE_DEFAULT,
      );

      $form['target_bundles'] = array(
        '#type' => 'select',
        '#title' => t('Target bundles'),
        '#options' => $bundles,
        '#default_value' => $settings['target_bundles'],
        '#size' => 6,
        '#multiple' => TRUE,
        '#description' => t('The bundles of the entity type acting as group, that can be referenced. Optional, leave empty for all bundles.')
      );

      $options = array();
      foreach (og_membership_type_load() as $og_membership) {
        $options[$og_membership->name] = $og_membership->description;
      }
      $form['membership_type'] = array(
        '#type' => 'select',
        '#title' => t('OG membership type'),
        '#description' => t('Select the membership type that will be used for a subscribing user.'),
        '#options' => $options,
        '#default_value' => $settings['membership_type'],
        '#required' => TRUE,
      );
    }

    return $form;
  }

  /**
   * Build an EntityFieldQuery to get referencable entities.
   */
  public function buildEntityFieldQuery($match = NULL, $match_operator = 'CONTAINS') {
    global $user;

    $handler = EntityReference_SelectionHandler_Generic::getInstance($this->field, $this->instance, $this->entity_type, $this->entity);
    $query = $handler->buildEntityFieldQuery($match, $match_operator);

    // FIXME: http://drupal.org/node/1325628
    unset($query->tags['node_access']);

    // FIXME: drupal.org/node/1413108
    unset($query->tags['entityreference']);

    $query->addTag('entity_field_access');
    $query->addTag('og');

    $group_type = $this->field['settings']['target_type'];
    $entity_info = entity_get_info($group_type);

    if (!field_info_field(OG_GROUP_FIELD)) {
      // There are no groups, so falsify query.
      $query->propertyCondition($entity_info['entity keys']['id'], -1, '=');
      return $query;
    }

    // Show only the entities that are active groups.
    $query->fieldCondition(OG_GROUP_FIELD, 'value', 1, '=');

    if (empty($this->instance['field_mode'])) {
      return $query;
    }

    $field_mode = $this->instance['field_mode'];
    $user_groups = og_get_groups_by_user(NULL, $group_type);
    $user_groups = $user_groups ? $user_groups : array();
    $user_groups = array_merge($user_groups, $this->getGidsForCreate());


    // Show the user only the groups they belong to.
    if ($field_mode == 'default') {
      if ($user_groups && !empty($this->instance) && $this->instance['entity_type'] == 'node') {
        // Determine which groups should be selectable.
        $node = $this->entity;
        $node_type = $this->instance['bundle'];
        $ids = array();
        foreach ($user_groups as $gid) {
          // Check if user has "create" permissions on those groups.
          // If the user doesn't have create permission, check if perhaps the
          // content already exists and the user has edit permission.
          if (og_user_access($group_type, $gid, "create $node_type content")) {
            $ids[] = $gid;
          }
          elseif (!empty($node->nid) && (og_user_access($group_type, $gid, "update any $node_type content") || ($user->uid == $node->uid && og_user_access($group_type, $gid, "update own $node_type content")))) {
            $node_groups = isset($node_groups) ? $node_groups : og_get_entity_groups('node', $node->nid);
            if (in_array($gid, $node_groups['node'])) {
              $ids[] = $gid;
            }
          }
        }
      }
      else {
        $ids = $user_groups;
      }

      if ($ids) {
        $query->propertyCondition($entity_info['entity keys']['id'], $ids, 'IN');
      }
      else {
        // User doesn't have permission to select any group so falsify this
        // query.
        $query->propertyCondition($entity_info['entity keys']['id'], -1, '=');
      }
    }
    elseif ($field_mode == 'admin' && $user_groups) {
      // Show only groups the user doesn't belong to.
      if (!empty($this->instance) && $this->instance['entity_type'] == 'node') {
        // Don't include the groups, the user doesn't have create
        // permission.
        $node_type = $this->instance['bundle'];
        foreach ($user_groups as $delta => $gid) {
          if (!og_user_access($group_type, $gid, "create $node_type content")) {
            unset($user_groups[$delta]);
          }
        }
      }
      if ($user_groups) {
        $query->propertyCondition($entity_info['entity keys']['id'], $user_groups, 'NOT IN');
      }
    }

    return $query;
  }

  public function entityFieldQueryAlter(SelectQueryInterface $query) {
    $handler = EntityReference_SelectionHandler_Generic::getInstance($this->field, $this->instance);
    // FIXME: Allow altering, after fixing http://drupal.org/node/1413108
    // $handler->entityFieldQueryAlter($query);
  }

  /**
   * Get group IDs from URL or OG-context, with access to create group-content.
   *
   * @return
   *   Array with group IDs a user (member or non-member) is allowed to
   * create, or empty array.
   */
  private function getGidsForCreate() {
    if ($this->instance['entity_type'] != 'node') {
      return array();
    }

    if (!module_exists('entityreference_prepopulate') || empty($this->instance['settings']['behaviors']['prepopulate'])) {
      return array();
    }

    // Don't try to validate the IDs.
    if (!$ids = entityreference_prepopulate_get_values($this->field, $this->instance, FALSE)) {
      return array();
    }
    $node_type = $this->instance['bundle'];
    foreach ($ids as $delta => $id) {
      if (!is_numeric($id) || !$id || !og_user_access('node', $id, "create $node_type content")) {
        unset($ids[$delta]);
      }
    }
    return $ids;
  }
}
