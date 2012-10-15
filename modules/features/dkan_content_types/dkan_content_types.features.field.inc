<?php
/**
 * @file
 * dkan_content_types.features.field.inc
 */

/**
 * Implements hook_field_default_fields().
 */
function dkan_content_types_field_default_fields() {
  $fields = array();

  // Exported field: 'node-dataset-body'.
  $fields['node-dataset-body'] = array(
    'field_config' => array(
      'active' => '1',
      'cardinality' => '1',
      'deleted' => '0',
      'entity_types' => array(
        0 => 'node',
      ),
      'field_name' => 'body',
      'foreign keys' => array(
        'format' => array(
          'columns' => array(
            'format' => 'format',
          ),
          'table' => 'filter_format',
        ),
      ),
      'indexes' => array(
        'format' => array(
          0 => 'format',
        ),
      ),
      'locked' => '0',
      'module' => 'text',
      'settings' => array(),
      'translatable' => '0',
      'type' => 'text_with_summary',
    ),
    'field_instance' => array(
      'bundle' => 'dataset',
      'default_value' => NULL,
      'deleted' => '0',
      'description' => '',
      'display' => array(
        'default' => array(
          'label' => 'hidden',
          'module' => 'text',
          'settings' => array(),
          'type' => 'text_default',
          'weight' => 0,
        ),
        'teaser' => array(
          'label' => 'hidden',
          'module' => 'text',
          'settings' => array(
            'trim_length' => 600,
          ),
          'type' => 'text_summary_or_trimmed',
          'weight' => 0,
        ),
      ),
      'entity_type' => 'node',
      'field_name' => 'body',
      'label' => 'Description',
      'required' => 0,
      'settings' => array(
        'display_summary' => 0,
        'text_processing' => '1',
        'user_register_form' => FALSE,
      ),
      'widget' => array(
        'active' => 1,
        'module' => 'text',
        'settings' => array(
          'rows' => '4',
          'summary_rows' => 5,
        ),
        'type' => 'text_textarea_with_summary',
        'weight' => '1',
      ),
    ),
  );

  // Exported field: 'node-dataset-field_license'.
  $fields['node-dataset-field_license'] = array(
    'field_config' => array(
      'active' => '1',
      'cardinality' => '1',
      'deleted' => '0',
      'entity_types' => array(),
      'field_name' => 'field_license',
      'foreign keys' => array(
        'format' => array(
          'columns' => array(
            'format' => 'format',
          ),
          'table' => 'filter_format',
        ),
      ),
      'indexes' => array(
        'format' => array(
          0 => 'format',
        ),
      ),
      'locked' => '0',
      'module' => 'text',
      'settings' => array(
        'max_length' => '255',
      ),
      'translatable' => '0',
      'type' => 'text',
    ),
    'field_instance' => array(
      'bundle' => 'dataset',
      'default_value' => NULL,
      'deleted' => '0',
      'description' => 'License definitions and additional information can be found at <a href="http://opendefinition.org">opendefinition.org</a>',
      'display' => array(
        'default' => array(
          'label' => 'above',
          'module' => 'text',
          'settings' => array(),
          'type' => 'text_default',
          'weight' => 2,
        ),
        'teaser' => array(
          'label' => 'above',
          'settings' => array(),
          'type' => 'hidden',
          'weight' => 0,
        ),
      ),
      'entity_type' => 'node',
      'field_name' => 'field_license',
      'label' => 'License',
      'required' => 0,
      'settings' => array(
        'text_processing' => '0',
        'user_register_form' => FALSE,
      ),
      'widget' => array(
        'active' => 1,
        'module' => 'select_or_other',
        'settings' => array(
          'available_options' => 'cc-by|Creative Commons Attribution
cc-by-sa|Creative Commons Attribution Share-Alike
cc-zero|Creative Commons CCZero
cc-nc|Creative Commons Non-Commercial (Any)
gfdl|GNU Free Documentation License
notspecified" selected="selected|License Not Specified
odc-by|Open Data Commons Attribution License
odc-odbl|Open Data Commons Open Database License (ODbL)
odc-pddl|Open Data Commons Public Domain Dedication and Licence (PDDL)
other-at|Other (Attribution)
other-nc|Other (Non-Commercial)
other-closed|Other (Not Open)
other-open|Other (Open)
other-pd|Other (Public Domain)
uk-ogl|UK Open Government Licence (OGL)',
          'available_options_php' => '',
          'markup_available_options_php' => '&lt;none&gt;',
          'other' => 'Other',
          'other_title' => '',
          'other_unknown_defaults' => 'other',
          'sort_options' => 0,
        ),
        'type' => 'select_or_other',
        'weight' => '3',
      ),
    ),
  );

  // Exported field: 'node-dataset-field_tags'.
  $fields['node-dataset-field_tags'] = array(
    'field_config' => array(
      'active' => '1',
      'cardinality' => '-1',
      'deleted' => '0',
      'entity_types' => array(),
      'field_name' => 'field_tags',
      'foreign keys' => array(
        'tid' => array(
          'columns' => array(
            'tid' => 'tid',
          ),
          'table' => 'taxonomy_term_data',
        ),
      ),
      'indexes' => array(
        'tid' => array(
          0 => 'tid',
        ),
      ),
      'locked' => '0',
      'module' => 'taxonomy',
      'settings' => array(
        'allowed_values' => array(
          0 => array(
            'vocabulary' => 'tags',
            'parent' => '0',
          ),
        ),
      ),
      'translatable' => '0',
      'type' => 'taxonomy_term_reference',
    ),
    'field_instance' => array(
      'bundle' => 'dataset',
      'default_value' => NULL,
      'deleted' => '0',
      'description' => '',
      'display' => array(
        'default' => array(
          'label' => 'above',
          'module' => 'taxonomy',
          'settings' => array(),
          'type' => 'taxonomy_term_reference_link',
          'weight' => 3,
        ),
        'teaser' => array(
          'label' => 'above',
          'settings' => array(),
          'type' => 'hidden',
          'weight' => 0,
        ),
      ),
      'entity_type' => 'node',
      'field_name' => 'field_tags',
      'label' => 'Tags',
      'required' => 0,
      'settings' => array(
        'user_register_form' => FALSE,
      ),
      'widget' => array(
        'active' => 1,
        'module' => 'active_tags',
        'settings' => array(
          'autocomplete_path' => 'taxonomy/autocomplete',
          'mode' => 'single',
          'size' => 60,
        ),
        'type' => 'active_tags_taxonomy_autocomplete',
        'weight' => '2',
      ),
    ),
  );

  // Exported field: 'node-dataset-group_audience'.
  $fields['node-dataset-group_audience'] = array(
    'field_config' => array(
      'active' => '1',
      'cardinality' => '-1',
      'deleted' => '0',
      'entity_types' => array(),
      'field_name' => 'group_audience',
      'foreign keys' => array(
        'og' => array(
          'columns' => array(
            'gid' => 'gid',
          ),
          'table' => 'og',
        ),
      ),
      'indexes' => array(
        'gid' => array(
          0 => 'gid',
        ),
      ),
      'locked' => '0',
      'module' => 'og',
      'no_ui' => TRUE,
      'settings' => array(),
      'translatable' => '0',
      'type' => 'group',
    ),
    'field_instance' => array(
      'bundle' => 'dataset',
      'default_value' => NULL,
      'default_value_function' => 'og_field_audience_default_value',
      'deleted' => '0',
      'description' => '',
      'display' => array(
        'default' => array(
          'label' => 'above',
          'module' => 'og',
          'settings' => array(),
          'type' => 'og_list_default',
          'weight' => 1,
        ),
        'teaser' => array(
          'label' => 'above',
          'settings' => array(),
          'type' => 'hidden',
          'weight' => 0,
        ),
      ),
      'entity_type' => 'node',
      'field_name' => 'group_audience',
      'label' => 'Groups audience',
      'required' => FALSE,
      'settings' => array(
        'user_register_form' => FALSE,
      ),
      'view modes' => array(
        'full' => array(
          'custom settings' => FALSE,
          'label' => 'Full',
          'type' => 'og_list_default',
        ),
        'teaser' => array(
          'custom settings' => FALSE,
          'label' => 'Teaser',
          'type' => 'og_list_default',
        ),
      ),
      'widget' => array(
        'module' => 'og',
        'settings' => array(
          'autocomplete_match' => 'contains',
          'autocomplete_path' => 'group/autocomplete',
          'opt_group' => 'auto',
          'size' => 60,
        ),
        'type' => 'group_audience',
        'weight' => '4',
      ),
      'widget_type' => 'group_audience',
    ),
  );

  // Exported field: 'node-group-body'.
  $fields['node-group-body'] = array(
    'field_config' => array(
      'active' => '1',
      'cardinality' => '1',
      'deleted' => '0',
      'entity_types' => array(
        0 => 'node',
      ),
      'field_name' => 'body',
      'foreign keys' => array(
        'format' => array(
          'columns' => array(
            'format' => 'format',
          ),
          'table' => 'filter_format',
        ),
      ),
      'indexes' => array(
        'format' => array(
          0 => 'format',
        ),
      ),
      'locked' => '0',
      'module' => 'text',
      'settings' => array(),
      'translatable' => '0',
      'type' => 'text_with_summary',
    ),
    'field_instance' => array(
      'bundle' => 'group',
      'default_value' => NULL,
      'deleted' => '0',
      'description' => '',
      'display' => array(
        'default' => array(
          'label' => 'hidden',
          'module' => 'text',
          'settings' => array(),
          'type' => 'text_default',
          'weight' => 1,
        ),
        'teaser' => array(
          'label' => 'hidden',
          'module' => 'text',
          'settings' => array(
            'trim_length' => 600,
          ),
          'type' => 'text_summary_or_trimmed',
          'weight' => 1,
        ),
      ),
      'entity_type' => 'node',
      'field_name' => 'body',
      'label' => 'Body',
      'required' => FALSE,
      'settings' => array(
        'display_summary' => TRUE,
        'text_processing' => 1,
        'user_register_form' => FALSE,
      ),
      'widget' => array(
        'module' => 'text',
        'settings' => array(
          'rows' => 20,
          'summary_rows' => 5,
        ),
        'type' => 'text_textarea_with_summary',
        'weight' => 1,
      ),
    ),
  );

  // Exported field: 'node-group-group_group'.
  $fields['node-group-group_group'] = array(
    'field_config' => array(
      'active' => '1',
      'cardinality' => '1',
      'deleted' => '0',
      'entity_types' => array(),
      'field_name' => 'group_group',
      'foreign keys' => array(),
      'indexes' => array(
        'value' => array(
          0 => 'value',
        ),
      ),
      'locked' => '0',
      'module' => 'list',
      'no_ui' => TRUE,
      'settings' => array(
        'allowed_values' => array(
          0 => 'Not a group',
          1 => 'Group',
        ),
        'allowed_values_function' => '',
      ),
      'translatable' => '0',
      'type' => 'list_boolean',
    ),
    'field_instance' => array(
      'bundle' => 'group',
      'default_value' => array(
        0 => array(
          'value' => 1,
        ),
      ),
      'deleted' => '0',
      'description' => 'Determine if this is an OG group.',
      'display' => array(
        'default' => array(
          'label' => 'above',
          'module' => 'og_ui',
          'settings' => array(),
          'type' => 'og_group_subscribe',
          'weight' => 0,
        ),
        'teaser' => array(
          'label' => 'above',
          'settings' => array(),
          'type' => 'hidden',
          'weight' => 0,
        ),
      ),
      'display_label' => 1,
      'entity_type' => 'node',
      'field_name' => 'group_group',
      'label' => 'Group',
      'required' => FALSE,
      'settings' => array(
        'user_register_form' => FALSE,
      ),
      'view modes' => array(
        'full' => array(
          'custom settings' => FALSE,
          'label' => 'Full',
          'type' => 'og_group_subscribe',
        ),
        'teaser' => array(
          'custom settings' => FALSE,
          'label' => 'Teaser',
          'type' => 'og_group_subscribe',
        ),
      ),
      'widget' => array(
        'module' => 'options',
        'settings' => array(
          'display_label' => 0,
        ),
        'type' => 'options_onoff',
        'weight' => 0,
      ),
    ),
  );

  // Exported field: 'node-resource-body'.
  $fields['node-resource-body'] = array(
    'field_config' => array(
      'active' => '1',
      'cardinality' => '1',
      'deleted' => '0',
      'entity_types' => array(
        0 => 'node',
      ),
      'field_name' => 'body',
      'foreign keys' => array(
        'format' => array(
          'columns' => array(
            'format' => 'format',
          ),
          'table' => 'filter_format',
        ),
      ),
      'indexes' => array(
        'format' => array(
          0 => 'format',
        ),
      ),
      'locked' => '0',
      'module' => 'text',
      'settings' => array(),
      'translatable' => '0',
      'type' => 'text_with_summary',
    ),
    'field_instance' => array(
      'bundle' => 'resource',
      'default_value' => NULL,
      'deleted' => '0',
      'description' => '',
      'display' => array(
        'default' => array(
          'label' => 'hidden',
          'module' => 'text',
          'settings' => array(),
          'type' => 'text_default',
          'weight' => 1,
        ),
        'teaser' => array(
          'label' => 'hidden',
          'module' => 'text',
          'settings' => array(
            'trim_length' => 600,
          ),
          'type' => 'text_summary_or_trimmed',
          'weight' => 1,
        ),
      ),
      'entity_type' => 'node',
      'field_name' => 'body',
      'label' => 'Body',
      'required' => 0,
      'settings' => array(
        'display_summary' => 1,
        'text_processing' => '1',
        'user_register_form' => FALSE,
      ),
      'widget' => array(
        'active' => 1,
        'module' => 'text',
        'settings' => array(
          'rows' => '5',
          'summary_rows' => 5,
        ),
        'type' => 'text_textarea_with_summary',
        'weight' => 1,
      ),
    ),
  );

  // Exported field: 'node-resource-group_audience'.
  $fields['node-resource-group_audience'] = array(
    'field_config' => array(
      'active' => '1',
      'cardinality' => '-1',
      'deleted' => '0',
      'entity_types' => array(),
      'field_name' => 'group_audience',
      'foreign keys' => array(
        'og' => array(
          'columns' => array(
            'gid' => 'gid',
          ),
          'table' => 'og',
        ),
      ),
      'indexes' => array(
        'gid' => array(
          0 => 'gid',
        ),
      ),
      'locked' => '0',
      'module' => 'og',
      'no_ui' => TRUE,
      'settings' => array(),
      'translatable' => '0',
      'type' => 'group',
    ),
    'field_instance' => array(
      'bundle' => 'resource',
      'default_value' => NULL,
      'default_value_function' => 'og_field_audience_default_value',
      'deleted' => '0',
      'description' => '',
      'display' => array(
        'default' => array(
          'label' => 'above',
          'module' => 'og',
          'settings' => array(),
          'type' => 'og_list_default',
          'weight' => 0,
        ),
        'teaser' => array(
          'label' => 'above',
          'settings' => array(),
          'type' => 'hidden',
          'weight' => 0,
        ),
      ),
      'entity_type' => 'node',
      'field_name' => 'group_audience',
      'label' => 'Groups audience',
      'required' => FALSE,
      'settings' => array(
        'user_register_form' => FALSE,
      ),
      'view modes' => array(
        'full' => array(
          'custom settings' => FALSE,
          'label' => 'Full',
          'type' => 'og_list_default',
        ),
        'teaser' => array(
          'custom settings' => FALSE,
          'label' => 'Teaser',
          'type' => 'og_list_default',
        ),
      ),
      'widget' => array(
        'module' => 'og',
        'settings' => array(
          'opt_group' => 'auto',
        ),
        'type' => 'group_audience',
        'weight' => 0,
      ),
      'widget_type' => 'group_audience',
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Body');
  t('Description');
  t('Determine if this is an OG group.');
  t('Group');
  t('Groups audience');
  t('License');
  t('License definitions and additional information can be found at <a href="http://opendefinition.org">opendefinition.org</a>');
  t('Tags');

  return $fields;
}
