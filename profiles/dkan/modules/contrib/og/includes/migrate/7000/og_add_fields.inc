<?php

/**
 * @file
 * Add OG related fields to group and group-content node-types.
 */

class OgMigrateAddFields extends MigrationBase {

  public function __construct() {
    parent::__construct();
    $this->description = t('Add OG related fields to group and group-content node-types.');
    $this->enabled = !$this->isComplete();
  }

  public function isComplete() {
    return !variable_get('og_7000_add_field', FALSE);
  }

  /**
   * Create group, group-audience and group description fields.
   */
  public function import() {
    og_create_field(OG_AUDIENCE_FIELD, 'user', 'user');
    foreach (node_type_get_types() as $type) {
      // Check if the variable exists.
      if ($type_usage = variable_get('og_content_type_usage_' . $type->type)) {
        switch ($type_usage) {
          case 'group':
            $content_type['group'][] = $type->type;
            break;

          case 'group_post_standard':
          case 'group_post_wiki':
            // The type of the group content is now determined via the
            // group permissions. We only care about adding the group content
            // fields to the node.
            $content_type['group content'][] = $type->type;
            break;

        }
      }
    }

    if ($content_type) {
      // Add group and group content fields to content types.
      $field_names = array(
        'group' => OG_GROUP_FIELD,
        'group content' => OG_AUDIENCE_FIELD,
      );
      foreach ($field_names as $key => $field_name) {
        $content_type += array($key => array());
        foreach ($content_type[$key] as $type) {
          og_create_field($field_name, 'node', $type);

          if ($key == 'group') {

            if (!field_info_field('og_description')) {
              $field = array(
                'field_name' => 'og_description',
                'type' => 'text',
                'entity_types' => array('node'),
                'cardinality' => 1,
              );
              $field = field_create_field($field);
            }

            if (!field_info_instance('node', 'og_description', $type)) {
              // Create the "description" field for the bundle.
              $instance = array(
                'field_name' => 'og_description',
                'bundle' => $type,
                'entity_type' => 'node',
                'label' => t('Group description'),
                'description' => t('This is description of the group.'),
              );
              field_create_instance($instance);

              // Delete the OG6 variable.
              variable_del('og_content_type_usage_' . $type);
            }
          }
        }
      }
    }

    // Delete the field that indicates we still need to add fields.
    variable_del('og_7000_add_field');

    return MigrationBase::RESULT_COMPLETED;
  }
}
