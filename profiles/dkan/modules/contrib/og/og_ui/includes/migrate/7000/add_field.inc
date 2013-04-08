<?php

/**
 * @file
 * Add OG UI related fields to group node-types.
 */

class OgUiMigrateAddField extends MigrationBase {

  public function __construct() {
    parent::__construct();
    $this->description = t('Add OG UI related fields to group node-types.');
    $this->enabled = !$this->isComplete();

    foreach (node_type_get_names() as $bundle => $value) {
      // Dependent on a dynamic migration.
      $machine_name = 'OgMigrateGroup' . ucfirst($bundle);
      if (MigrationBase::getInstance($machine_name, 'OgMigrateGroup', array('bundle' => $bundle))) {
        $this->dependencies[] = $machine_name;
      }
    }
  }

  public function isComplete() {
    return !variable_get('og_ui_7000_add_field', FALSE);
  }

  /**
   * Add OG_DEFAULT_ACCESS_FIELD to group.
   */
  public function import() {
    $bundles = og_get_all_group_bundle();
    if (!empty($bundles['node'])) {
      foreach (array_keys($bundles['node']) as $bundle) {
        // Add the "Group roles and permissions" field to the bundle.
        og_create_field(OG_DEFAULT_ACCESS_FIELD, 'node', $bundle);
      }
    }

    // Delete the field that indicates we still need to add fields.
    variable_del('og_ui_7000_add_field');

    return MigrationBase::RESULT_COMPLETED;
  }
}
