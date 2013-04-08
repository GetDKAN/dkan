<?php

/**
 * @file
 * Re-populate group ID and group entity, deprecating the OG group entity.
 *
 * Class should be included only if this is an upgrade from branch 7.x-1.x
 * to branch 7.x-2.x
 */

class OgMigrateMembership extends OgEntityMigration {

  /**
   * The name of the table migrate will query.
   */
  public $tableName = 'og_membership';

  /**
   * Indicate we are updating existing data.
   */
  protected $systemOfRecord = Migration::DESTINATION;

  public function __construct() {
    $this->description = t('Re-populate group ID and group entity, deprecating the OG group entity.');

    $query = db_select('og_membership', 'ogm');
    $query->innerJoin('og', 'og', 'ogm.gid = og.gid OR (ogm.group_type = og.entity_type AND ogm.gid = og.etid)');
    $query
      ->fields('ogm', array('id', 'etid', 'entity_type', 'field_name'));

    $query->addField('og', 'etid', 'gid');
    $query->addField('og', 'entity_type', 'group_type');

    $this->query = $query;
    parent::__construct();

    $fields = array(
      'id',
      'group_type',
      'gid',
      'entity_type',
      'etid',
      'field_name',
    );

    foreach ($fields as $field) {
      $this->addFieldMapping($field, $field);
    }
  }

  /**
   * Reject the source row if the group or group content are missing.
   */
  public function prepareRow($row) {
    $entity_type = $row->entity_type;
    $etid = $row->etid;
    $group_type = $row->group_type;
    $gid = $row->gid;

    if (!$group_content = entity_load_single($entity_type, $etid)) {
      // The OG membership was somehow not deleted when the entity
      // was deleted.
      return FALSE;
    }

    if (!$group = entity_load_single($group_type, $gid)) {
      return FALSE;
    }
    return parent::prepareRow($row);
  }

  public function prepare($entity, $row) {
    $entity_type = $row->entity_type;
    $etid = $row->etid;
    $group_type = $row->group_type;
    $gid = $row->gid;

    $group_content = entity_load_single($entity_type, $etid);
    $group = entity_load_single($group_type, $gid);

    list(,, $group_bundle) = entity_extract_ids($group_type, $group);
    if (!$field_name = og_get_best_group_audience_field($entity_type, $group_content, $group_type, $group_bundle)) {
      // Create a new field. Pick an unused name, if the settings don't match.
      // To maintain some backwards compatibility, if the group type is a node,
      // we try to set its name to OG_AUDIENCE FIELD.
      $field_name = $group_type == 'node' ? OG_AUDIENCE_FIELD : substr("og_$group_type", 0, 32);
      $i = 1;

      $og_field = og_fields_info(OG_AUDIENCE_FIELD);
      $og_field['field']['settings']['target_type'] = $group_type;
      list(,, $bundle) = entity_extract_ids($entity_type, $group_content);

      while ($field = field_info_field($field_name)) {
        if ($field['settings']['target_type'] == $group_type && empty($field['settings']['handler_settings']['target_bundles']) || in_array($bundle, $field['settings']['handler_settings']['target_bundles'])) {
          // An existing field.
          $field_name = $field['field_name'];
          break;
        }
        $field_name = substr("og_$group_type", 0, 32 - strlen($i)) . $i;
        ++$i;
      }

      og_create_field($field_name, $entity_type, $bundle, $og_field);
    }
    $entity->field_name = $field_name;
  }

  /**
   * Override Migration::postImport().
   *
   * Remove OG-memberships that should have been deleted.
   */
  protected function postImport() {
    if (!$this->isComplete()) {
      return;
    }
    db_delete('og_membership')
      ->condition('group_type', '')
      ->execute();
  }
}
