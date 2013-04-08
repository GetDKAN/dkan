<?php


/**
 * @file
 * OG example selection handler.
 */

class OgExampleSelectionHandler extends OgSelectionHandler {

  /**
   * Overrides OgSelectionHandler::getInstance().
   */
  public static function getInstance($field, $instance = NULL, $entity_type = NULL, $entity = NULL) {
    return new OgExampleSelectionHandler($field, $instance, $entity_type, $entity);
  }

  /**
   * Overrides OgSelectionHandler::buildEntityFieldQuery().
   *
   * This is an example of "subgroups" (but without getting into the logic of
   * sub-grouping).
   * The idea here is to show we can set "My groups" and "Other groups" to
   * reference different groups by different
   * logic. In this example, all group nodes below node ID 5, will appear under
   * "My groups", and the rest will appear under "Other groups",
   * for administrators.
   */
  public function buildEntityFieldQuery($match = NULL, $match_operator = 'CONTAINS') {
    $group_type = $this->field['settings']['target_type'];


    if (empty($this->instance['field_mode']) || $group_type != 'node') {
      return parent::buildEntityFieldQuery($match, $match_operator);
    }

    $field_mode = $this->instance['field_mode'];
    $handler = EntityReference_SelectionHandler_Generic::getInstance($this->field, $this->instance, $this->entity_type, $this->entity);
    $query = $handler->buildEntityFieldQuery($match, $match_operator);

    // Show only the entities that are active groups.
    $query->fieldCondition(OG_GROUP_FIELD, 'value', 1, '=');

    if ($field_mode == 'default') {
      $query->propertyCondition('nid', '5', '<=');
    }
    else {
      $query->propertyCondition('nid', '5', '>');
    }

    // FIXME: http://drupal.org/node/1325628
    unset($query->tags['node_access']);

    // FIXME: drupal.org/node/1413108
    unset($query->tags['entityreference']);

    $query->addTag('entity_field_access');
    $query->addTag('og');

    return $query;
  }
}
