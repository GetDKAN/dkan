<?php

/**
 * @file
 * open_data_federal_extras.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function open_data_federal_extras_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'node-dataset-field_odfe_bureau_code'.
  $field_instances['node-dataset-field_odfe_bureau_code'] = array(
    'bundle' => 'dataset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Adds Project Open Data\'s <a href="https://project-open-data.cio.gov/v1.1/schema/#bureauCode">Bureau Code</a>.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'list',
        'settings' => array(),
        'type' => 'list_default',
        'weight' => 6,
      ),
      'search_result' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
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
    'field_name' => 'field_odfe_bureau_code',
    'label' => 'Bureau Code',
    'required' => 1,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(
        'apply_chosen' => 1,
      ),
      'type' => 'options_select',
      'weight' => 25,
    ),
  );

  // Exported field_instance: 'node-dataset-field_odfe_data_quality'.
  $field_instances['node-dataset-field_odfe_data_quality'] = array(
    'bundle' => 'dataset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'list',
        'settings' => array(),
        'type' => 'list_default',
        'weight' => 11,
      ),
      'search_result' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
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
    'field_name' => 'field_odfe_data_quality',
    'label' => 'Data Quality',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(
        'apply_chosen' => 1,
      ),
      'type' => 'options_buttons',
      'weight' => 30,
    ),
  );

  // Exported field_instance: 'node-dataset-field_odfe_investment_uii'.
  $field_instances['node-dataset-field_odfe_investment_uii'] = array(
    'bundle' => 'dataset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Use to link a given dataset with its related IT Unique Investment Identifier, which can often be found in Exhibit 53 documents.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 14,
      ),
      'search_result' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
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
    'field_name' => 'field_odfe_investment_uii',
    'label' => 'Primary IT Investment UII',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 33,
    ),
  );

  // Exported field_instance: 'node-dataset-field_odfe_program_code'.
  $field_instances['node-dataset-field_odfe_program_code'] = array(
    'bundle' => 'dataset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Adds Project Open Data\'s <a href="https://project-open-data.cio.gov/v1.1/schema/#programCode">Program Code</a>.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'list',
        'settings' => array(),
        'type' => 'list_default',
        'weight' => 7,
      ),
      'search_result' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
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
    'field_name' => 'field_odfe_program_code',
    'label' => 'Program Code',
    'required' => 1,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(
        'apply_chosen' => 1,
      ),
      'type' => 'options_select',
      'weight' => 26,
    ),
  );

  // Exported field_instance: 'node-dataset-field_odfe_system_of_records'.
  $field_instances['node-dataset-field_odfe_system_of_records'] = array(
    'bundle' => 'dataset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'This field should be a URL to the System of Records Notice (SORN) that relates to the dataset, specifically from FederalRegister.gov.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 16,
      ),
      'search_result' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
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
    'field_name' => 'field_odfe_system_of_records',
    'label' => 'System of Records',
    'required' => 0,
    'settings' => array(
      'absolute_url' => 1,
      'attributes' => array(
        'class' => '',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'none',
      'title_label_use_field_label' => FALSE,
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 34,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Adds Project Open Data\'s <a href="https://project-open-data.cio.gov/v1.1/schema/#bureauCode">Bureau Code</a>.');
  t('Adds Project Open Data\'s <a href="https://project-open-data.cio.gov/v1.1/schema/#programCode">Program Code</a>.');
  t('Bureau Code');
  t('Data Quality');
  t('Primary IT Investment UII');
  t('Program Code');
  t('System of Records');
  t('This field should be a URL to the System of Records Notice (SORN) that relates to the dataset, specifically from FederalRegister.gov.');
  t('Use to link a given dataset with its related IT Unique Investment Identifier, which can often be found in Exhibit 53 documents.');

  return $field_instances;
}
