<?php

/**
 * @file
 * Form elements for DKAN.
 */

use dkanDataset\getRemoteFileInfo;

include_once 'includes/getRemoteFileInfo.php';

/**
 * Implements hook_field_group_build_pre_render_alter().
 */
function dkan_dataset_field_group_build_pre_render_alter(&$element) {
  // Moving path into primary group here since we can't do it on the content
  // type form. Rearanging weights so path is under title.
  if (isset($element['#form_id']) && $element['#form_id'] == 'dataset_node_form') {
    $element['group_primary']['path'] = $element['path'];
    $element['group_primary']['title']['#weight'] = '-10';
    $element['group_primary']['path']['#weight'] = "1";
    $element['group_primary']['body']['#weight'] = "2";
    $element['group_primary']['field_tags']['#weight'] = "3";
    $element['group_primary']['og_group_ref']['#weight'] = "4";
    unset($element['path']);
  }
}

/**
 * Helper function to get the node title without loading the whole node object.
 */
function dkan_dataset_get_node_title($nid) {
  return db_query('SELECT title FROM {node} WHERE nid = :nid', array(':nid' => $nid))->fetchField();
}

/**
 * Returns an array with the available group options for the current user.
 */
function dkan_dataset_get_group_options() {

  $available_groups = array();

  if (user_access('administer group')) {
    // If the user is a group administrator then show all groups as options.
    $groups = og_get_all_group();
  }
  else {
    // If the user is not an administrator then only display the groups that the
    // user is a member of. If a user is not specified for
    // 'og_get_groups_by_user' then it defaults to the current user.
    $groups = og_get_groups_by_user(NULL, 'node');
  }

  if ($groups) {
    foreach ($groups as $group_id) {
      $available_groups[$group_id] = dkan_dataset_get_node_title($group_id);
    }
  }

  return $available_groups;
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function dkan_dataset_form_alter(&$form, &$form_state, $form_id) {
  if ($form_id == 'eck__properties__form') {
    $form['#after_build'][] = 'dkan_dataset_eck_properties_after_build_form';
  }
  if ($form_id == 'entity_table_select_visualization_ve_chart') {
    $form['#after_build'][] = 'dkan_dataset_visualization_after_build_form';
  }

  if ($form_id == 'dataset_node_form') {

    $form['#attached']['css'][] = drupal_get_path('module', 'dkan_dataset') . '/css/dkan_dataset.css';
    $form['#attached']['js'][] = drupal_get_path('module', 'dkan_dataset') . '/js/dkan_forms.js';
    beautytips_add_beautytips();

    $form['#after_build']['dkan_dataset'] = 'dkan_dataset_resource_form_after_build';

    // Slugify pathauto in a jazzy but brittle manner.
    if (variable_get('dkan_dataset_form_pathauto_alter', 1)) {
      $form['#attached']['js'][] = libraries_get_path('slugify') . '/jquery.slugify.js';
      $form['#attached']['js'][] = drupal_get_path('module', 'dkan_dataset') . '/js/dkan_forms.slugify.js';
      // Removing path from vertiable tabs.
      unset($form['path']['#group']);
      unset($form['path']['#title']);
      $form['path']['#weight'] = "-1";
      // Always use pathauto.
      $form['path']['pathauto']['#default_value'] = TRUE;
      $form['path']['pathauto']['#access'] = FALSE;
      $form['path']['alias']['#default_value'] = preg_replace('/dataset\//', '', $form['path']['alias']['#default_value']);
      $form['path']['#collapsible'] = FALSE;
      $form['path']['#collapsed'] = FALSE;
      $alias = $form['path']['alias']['#default_value'] ? $form['path']['alias']['#default_value'] : t('[dataset]');
      $form['path']['#prefix'] = '<div id="field-tags-wrapper">
                                    <div id="url-edit-preview">
                                      <div id="url-edit-label"><strong>URL</strong></div>
                                      <div id="url-edit-prefix">' . check_plain($_SERVER['HTTP_HOST']) . '/dataset/</div>' .
        '<span id="url-slug">' . $alias . '</span>' . $form['path']['alias']['#default_value'] . '<button class="btn btn-small">Edit</button>
                                    </div>';
      $form['path']['#suffix'] = '</div>';
      $form['path']['alias']['#prefix'] = '<span class="add-on">/dataset/</span>';
    }
    // Adding descriptions.
    $form['title']['#attributes']['placeholder'] = t('eg. A descriptive title');

    // Get langcode for body.
    $body_langcode = dkan_dataset_form_field_language($form, 'body');

    $form['body'][$body_langcode][0]['#prefix'] = '<div id="prefix">';
    $form['body'][$body_langcode][0]['#suffix'] = '</div>';
    $form['path']['alias']['#description'] = '';
    $form['path']['alias']['#title'] = t('URL');

    // Get langcode for field_tags.
    $field_tags_langcode = dkan_dataset_form_field_language($form, 'field_tags');

    $form['field_tags'][$field_tags_langcode]['#description'] = t('eg. economy, mental health, government');

    // Get langcode for field_license.
    $field_license_langcode = dkan_dataset_form_field_language($form, 'field_license');
    // Add icon to license field.
    $form['field_license'][$field_license_langcode]['#description'] =
      t('<i class="icon-large icon-info-sign"></i>
        License definitions and additional information can be found at
        <a href="http://opendefinition.org">opendefinition.org</a>');

    // Get langcode for field_additional_info.
    if (isset($form['field_additional_info'])) {
      $field_additional_info_langcode = dkan_dataset_form_field_language($form, 'field_additional_info');
      foreach ($form['field_additional_info'][$field_additional_info_langcode] as $key => $values) {
        if (is_int($key)) {
          $form['field_additional_info'][$field_additional_info_langcode][$key]['first']['#attributes']['placeholder'] = t('key');
          $form['field_additional_info'][$field_additional_info_langcode][$key]['second']['#attributes']['placeholder'] = t('value');
        }
      }
    }

    if (isset($form['og_group_ref'])) {
      // Get langcode for og_group_ref.
      $og_group_ref_langcode = dkan_dataset_form_field_language($form, 'og_group_ref');

      // Modify title of Publisher field.
      $form['og_group_ref'][$og_group_ref_langcode]['#title'] = t('Groups');

      $groups = dkan_dataset_get_group_options();
      $form['og_group_ref'][$og_group_ref_langcode]['#options'] = $groups;
      // Don't show groups if user doesn't have any groups to add.
      if (empty($groups)) {
        $form['og_group_ref']['#access'] = FALSE;
      }
    }

    if (arg(1) == 'add') {
      // We don't need to see revision form when creating a new node.
      $form['revision_information']['#access'] = FALSE;

      $form['prefix']['#type'] = 'item';
      $form['prefix']['#weight'] = '-20';
      $form['prefix']['#markup'] = dkan_form_create_stages('dataset-create');

      $form['actions']['next'] = array(
        '#type' => 'submit',
        '#value' => t('Next: Add data'),
        '#weight' => -5,
        '#submit' => array('node_form_submit'),
      );
      $form['actions']['next']['#submit'] = array('node_form_submit', 'dkan_dataset_dataset_form_submit');
      $form['actions']['terms'] = array(
        '#type' => 'item',
        '#markup' => '<div id="action-info">' . t('Important: By submitting content, you agree to release your contributions under the Open Database License.') . '</div>',
      );
      unset($form['actions']['submit']);
    }
    elseif (arg(2) == 'edit' && $query = drupal_get_query_parameters()) {
      if (isset($query['additional'])) {
        $form['prefix']['#type'] = 'item';
        $form['prefix']['#weight'] = '-20';
        $form['prefix']['#markup'] = dkan_form_create_stages('dataset-additional', arg(1));
        if (isset($form['actions']['submit'])) {
          $form['actions']['submit']['#submit'] = array('dkan_dataset_dataset_body_submit', 'node_form_submit');
        }
      }
    }
    else {
      $form['actions']['submit']['#value'] = t('Finish');
      // This is here for AJAX compatibililty but causing other problems;
      // commenting out for now
      // $form['#action'] = url('node/add/dataset');.
    }

    // Mark all POD related required fields as required except for 'Publisher',
    // handled separately if POD validation is enabled on DKAN Dataset Forms.
    if (variable_get('dkan_dataset_form_pod_validation')) {
      // Body.
      $body_langcode = dkan_dataset_form_field_language($form, 'body');
      $form['body'][$body_langcode][0]['#required'] = TRUE;
      // Tags.
      $field_tags_langcode = dkan_dataset_form_field_language($form, 'field_tags');
      $form['field_tags'][$field_tags_langcode]['#required'] = TRUE;
      // Contact email.
      $field_contact_email_langcode = dkan_dataset_form_field_language($form, 'field_contact_email');
      $form['field_contact_email'][$field_contact_email_langcode][0]['value']['#required'] = TRUE;
      // Contact name.
      $field_contact_name_langcode = dkan_dataset_form_field_language($form, 'field_contact_name');
      $form['field_contact_name'][$field_contact_name_langcode][0]['value']['#required'] = TRUE;
      // Public access level.
      $field_public_access_level_langcode = dkan_dataset_form_field_language($form, 'field_public_access_level');
      $form['field_public_access_level'][$field_public_access_level_langcode]['#required'] = TRUE;
      // License.
      $field_license_langcode = dkan_dataset_form_field_language($form, 'field_license');
      // Set 'Open Data Commons Open Database License (ODbL)' as default value.
      $valid_pod_license = TRUE;
      $selected_license_code = $form['field_license'][$field_license_langcode]['#default_value'];
      // If the license is not specified then it's not a POD valid license.
      if ($selected_license_code === 'notspecified') {
        $valid_pod_license = FALSE;
      }
      else {
        $available_licenses = dkan_dataset_content_types_license_subscribed_values();
        // If the license is not available then is not a POD valid license.
        if (!isset($available_licenses[$selected_license_code])) {
          $valid_pod_license = FALSE;
          drupal_set_message(
            t('The dataset had a license value that is not valid on Project Open
              Data, so it was changed automatically. Please check the new value
              before saving any changes.')
          );
        }
      }

      if (!$valid_pod_license) {
        $form['field_license'][$field_license_langcode]['#default_value'] = 'odc-odbl';
      }
    }

    // Make 'Rights' field visible and required if 'Public Access Level' is set
    // to 'restricted' or 'private'.
    $access_level_values = array(
      array('value' => t('restricted')),
      array('value' => t('private')),
    );
    $field_public_access_level_langcode = dkan_dataset_form_field_language($form, 'field_public_access_level');
    $form['field_rights']['#states'] = array(
      'visible' => array(
        '[name="field_public_access_level[' . $field_public_access_level_langcode . ']"]' => array($access_level_values),
      ),
      'required' => array(
        '[name="field_public_access_level[' . $field_public_access_level_langcode . ']"]' => array($access_level_values),
      ),
    );

    // Mark 'Groups' field as required if the group validation is enabled on
    // DKAN Dataset forms.
    if (variable_get('dkan_dataset_form_group_validation') && module_exists('dkan_dataset_groups')) {
      // Groups.
      $og_group_ref_langcode = dkan_dataset_form_field_language($form, 'og_group_ref');
      $form['og_group_ref'][$og_group_ref_langcode]['#required'] = TRUE;
      // If there are no groups then display a warning letting the user know
      // they won't be able to create the dataset since the 'Publisher' field is
      // a POD-required field.
      $available_groups = $form['og_group_ref'][$og_group_ref_langcode]['#options'];
      if (empty($available_groups)) {
        // Show a warning message.
        dkan_dataset_display_groups_warning();
      }
    }

    // If 'Validate dataset form according to Project Open Data' is enabled on
    // dataset form settings then move all POD required fields to the first step
    // if the form is a multistep form.
    $multistep_form_enabled = variable_get('dkan_dataset_form_additional_info', 1);
    $pod_validation_enabled = variable_get('dkan_dataset_form_pod_validation');
    if ($multistep_form_enabled && $pod_validation_enabled) {
      $form['#group_children']['field_contact_name'] = 'group_primary';
      $form['#group_children']['field_contact_email'] = 'group_primary';
      $form['#group_children']['field_public_access_level'] = 'group_primary';
      $form['#group_children']['field_rights'] = 'group_primary';
    }

    // Hide field groups if multi step form is enabled and the dataset API is
    // not being used.
    if (variable_get('dkan_dataset_form_additional_info', 1) && (current_path() != 'api/dataset/node')) {
      $query = drupal_get_query_parameters();
      $arg2 = arg(2);
      $additional = isset($form['#additional_processed']) && $form['#additional_processed'] ? TRUE : FALSE;
      if ((isset($form['#groups']['group_primary']->disabled) && $form['#groups']['group_primary']->disabled) || isset($query['additional'])) {
        field_group_hide_field_groups($form, array('group_primary'));
        if (module_exists('open_data_federal_extras')) {
          field_group_hide_field_groups($form, array('group_odfie_pod'));
        }
      }
      elseif (isset($form['#action']) && $form['#action'] == url('node/add/dataset') && !$additional && $arg2 != 'edit') {
        field_group_hide_field_groups($form, array('group_additional'));
      }
    }

    $form['#validate'][] = 'dkan_dataset_dataset_node_form_validate';
  }
  if ($form_id == 'resource_node_form') {
    drupal_add_library('system', 'jquery.bbq');
    $form['#attached']['css'][] = drupal_get_path('module', 'dkan_dataset') . '/css/dkan_dataset.css';
    $form['#attached']['js'][] = drupal_get_path('module', 'dkan_dataset') . '/js/dkan_forms.js';

    if (isset($form['feeds'])) {
      $form['feeds']['#access'] = FALSE;
    }

    $form['#after_build']['dkan_dataset'] = 'dkan_dataset_resource_form_after_build';

    // Get langcode for field_dataset_ref.
    $field_dataset_ref_langcode = dkan_dataset_form_field_language($form, 'field_dataset_ref');
    $form['field_dataset_ref'][$field_dataset_ref_langcode]['#description'] = t('Dataset that this resource is attached to.');
    $dataset_options = $form['field_dataset_ref'][$field_dataset_ref_langcode]['#options'];
    $fix_apostrophes = function ($value) {
      return html_entity_decode($value, ENT_QUOTES, "UTF-8");
    };
    $form['field_dataset_ref'][$field_dataset_ref_langcode]['#options'] = array_map($fix_apostrophes, $dataset_options);

    // Get langcode for field_upload.
    $field_upload_langcode = dkan_dataset_form_field_language($form, 'field_upload');

    $form['field_upload'][$field_upload_langcode][0]['#title'] = '';
    $form['field_upload'][$field_upload_langcode][0]['view']['#collapsible'] = TRUE;
    $form['field_upload'][$field_upload_langcode][0]['view']['#collasped'] = TRUE;
    $file_upload_validators = $form['field_upload'][$field_upload_langcode][0]['#file_resup_upload_validators'];
    $form['field_upload'][$field_upload_langcode][0]['#description'] = theme('file_upload_help', array('upload_validators' => $file_upload_validators));

    // Get langcode for field_link_remote_file.
    $field_link_remote_file_langcode = dkan_dataset_form_field_language($form, 'field_link_remote_file');
    $form['field_link_remote_file'][$field_link_remote_file_langcode][0]['#description'] = t('Link to a file hosted on a remote server. CSV files can be imported into the DKAN datastore.');

    // Get langcode for field_link_api.
    $field_link_api_langcode = dkan_dataset_form_field_language($form, 'field_link_api');
    $form['field_link_api'][$field_link_api_langcode][0]['#description'] = t('Full url to a public API or information source (http://example.com).');

    // Get langcode for field_format.
    $field_link_api_langcode = dkan_dataset_form_field_language($form, 'field_format');
    $form['field_format'][$field_link_api_langcode]['#description'] = 'Leave blank to auto-detect resource format';

    $form['title']['#attributes']['placeholder'] = t('eg. January Gold Prices');
    $form['title']['#required'] = TRUE;

    $form['field_link_api'][$field_link_api_langcode][0]['#title'] = '';
    $form['field_link_remote_file'][$field_link_remote_file_langcode][0]['#title'] = '';

    // Use state to show or hide field group.
    $form['og_group_ref']['#states'] = array(
      'visible' => array(
        array(':input[name="field_dataset_ref[und][]"]' => array('value' => array('_none'))),
        '[OR/XOR]',
        array(':input[name="field_dataset_ref[und][]"]' => array('value' => array())),
      ),
    );

    if ($query = drupal_get_query_parameters()) {
      if (isset($query['dataset'])) {
        $form['field_dataset_ref'][$field_dataset_ref_langcode]['#default_value'] = $query['dataset'];
        $form['prefix']['#type'] = 'item';
        $form['prefix']['#weight'] = '-20';
        $form['prefix']['#markup'] = dkan_form_create_stages('resource-create', $query['dataset']);
      }
    }
    elseif (isset($form['#node']->field_dataset_ref['nid'])) {
      $form['field_dataset_ref'][$field_dataset_ref_langcode]['#default_value'] = $form['#node']->field_dataset_ref['nid'];
      $form['prefix']['#type'] = 'item';
      $form['prefix']['#weight'] = '-20';
      $form['prefix']['#markup'] = dkan_form_create_stages('resource-create', $form['#node']->field_dataset_ref['nid']);
    }
    $form['#validate'][] = 'dkan_dataset_resource_node_form_validate';
    // This saves us on the ajax reloading.
    if (isset($query['dataset'])) {
      $form['#action'] = url('node/add/resource', array('query' => array('dataset' => $query['dataset'])));
    }
    else {
      $form['#action'] = url('node/add/resource');
      if (drupal_match_path(current_path(), 'node/*/edit')) {
        $form['#action'] = url('node/' . $form['#node']->nid . '/edit');
      }
    }
    if (variable_get('dkan_dataset_form_additional_info', 1)) {
      if (isset($query['dataset']) || (isset($form_state['input']['field_dataset_ref']['und'][0]))) {
        $form['actions']['additional'] = array(
          '#type' => 'submit',
          '#value' => t('Next: Additional Info'),
          '#weight' => -5,
          '#submit' => array('node_form_submit'),
        );
        $form['actions']['additional']['#submit'] = array('node_form_submit', 'dkan_dataset_resource_additional_form_submit');
      }
    }
    $form['actions']['another'] = array(
      '#type' => 'submit',
      '#value' => t('Save and add another'),
      '#weight' => 10,
      '#submit' => array('node_form_submit'),
    );
    $form['actions']['another']['#submit'] = array('node_form_submit', 'dkan_dataset_resource_form_submit');
  }
}

/**
 * Process tableselect to pass 508 compliance.
 */
function dkan_dataset_eck_properties_after_build_form($form, &$form_state) {
  $table = $form['new_properties_table'];
  $children = element_children($table);

  foreach ($children as $key) {
    $form['new_properties_table'][$key]['#title'] = t('A checkbox for tableselect');
  }

  return $form;
}

/**
 * Process visualization entity table to pass 508 compliance.
 */
function dkan_dataset_visualization_after_build_form($form, &$form_state) {
  $table = $form['entity_table'];
  $children = element_children($table);

  foreach ($children as $key) {
    $form['entity_table'][$key]['#title'] = t('A checkbox for tableselect');
  }

  return $form;
}

/**
 * Resource node form submit handler.
 */
function dkan_dataset_resource_form_submit($form, &$form_state) {
  if ($query = drupal_get_query_parameters()) {
    if (isset($query['dataset'])) {
      drupal_goto('node/add/resource', array('query' => array('dataset' => $query['dataset'])));
    }
  }
}

/**
 * Resource next form submit handler.
 */
function dkan_dataset_resource_additional_form_submit($form, &$form_state) {
  if ($query = drupal_get_query_parameters()) {
    if (isset($query['dataset'])) {
      drupal_goto('node/' . $query['dataset'] . '/edit', array('query' => array('additional' => 1)));
    }
  }
}

/**
 * Resource node form submit handler.
 */
function dkan_dataset_dataset_body_submit($form, &$form_state) {
  // Fixes body getting dropped in "Additional info" form.
  if (isset($form_state['build_info']['args'][0]->body['und']['0']['value'])) {
    if ($form_state['build_info']['args'][0]->body['und']['0']['value']) {
      $form_state['values']['body']['und'][0]['value'] = $form_state['build_info']['args'][0]->body['und']['0']['value'];
      $form_state['values']['body']['und'][0]['summary'] = $form_state['build_info']['args'][0]->body['und']['0']['summary'];
      $form_state['values']['body']['und'][0]['format'] = $form_state['build_info']['args'][0]->body['und']['0']['format'];
    }
  }
}

/**
 * Dataset node form submit handler.
 */
function dkan_dataset_dataset_form_submit($form, &$form_state) {
  if (isset($form_state['node']->nid)) {
    drupal_goto('node/add/resource', array('query' => array('dataset' => $form_state['node']->nid)));
  }
}

/**
 * Validation function for dataset node form.
 */
function dkan_dataset_dataset_node_form_validate($form, &$form_state) {
  // Throw an error if the user has no groups available but the strict POD
  // validation is enabled in DKAN Dataset Forms. The check about the field
  // being required and empty won't work because the 'Groups'
  // field is hidden when the user is not associated with any groups.
  if (variable_get('dkan_dataset_form_group_validation') && module_exists('dkan_dataset_groups')) {
    // Get available groups from the field options.
    $field_og_group_ref_langcode = dkan_dataset_form_field_language($form, 'og_group_ref');
    $available_groups = $form['og_group_ref'][$field_og_group_ref_langcode]['#options'];
    // If there are no options then display an error.
    if (empty($available_groups)) {
      dkan_dataset_display_groups_warning();
      form_set_error('', 'Error: You tried to submit a dataset with no groups assigned.');
    }
  }

  // Display an error if the 'Public Access Level' field value is not 'public'
  // and the 'Rights' field is empty.
  $field_public_access_level_langcode = dkan_dataset_form_field_language($form, 'field_public_access_level');
  $public_access_level = $form_state['values']['field_public_access_level'][$field_public_access_level_langcode][0]['value'];
  $field_rights_langcode = dkan_dataset_form_field_language($form, 'field_rights');
  $rights = $form_state['values']['field_rights'][$field_rights_langcode][0]['value'];
  if (isset($public_access_level) && $public_access_level != 'public' && empty($rights)) {
    form_set_error('field_rights', 'Rights field is required.');
  }
}

/**
 * Display groups warning on dataset form.
 */
function dkan_dataset_display_groups_warning() {
  if (!user_access('administer group')) {
    drupal_set_message(t("A dataset must be added to a specific group, but you don’t belong to any groups.
          Ask a supervisor or site administrator to add you to a group or
          promote you to site manager to add datasets."), 'warning');
  }
}

/**
 * Validation function for resource node form.
 */
function dkan_dataset_resource_node_form_validate($form, &$form_state) {
  // Get langcode for field_link_remote_file.
  $field_link_remote_file_langcode = dkan_dataset_form_field_language($form, 'field_link_remote_file');
  // Get langcode for field_link_api.
  $field_link_api_langcode = dkan_dataset_form_field_language($form, 'field_link_api');
  // Get langcode for field_upload.
  $field_upload_langcode = dkan_dataset_form_field_language($form, 'field_upload');
  // Get langcode for field_format.
  $field_format_langcode = dkan_dataset_form_field_language($form, 'field_format');

  // See if file link, api link, or file upload.
  $link = isset($form_state['values']['field_link_remote_file'][$field_link_remote_file_langcode]) ? $form_state['values']['field_link_remote_file'][$field_link_remote_file_langcode][0]['filefield_dkan_remotefile']['url'] : NULL;
  $link_fid = isset($form_state['values']['field_link_remote_file'][$field_link_remote_file_langcode]) ? $form_state['values']['field_link_remote_file'][$field_link_remote_file_langcode][0]['fid'] : NULL;
  $api = isset($form_state['values']['field_link_api']) ? $form_state['values']['field_link_api'][$field_link_api_langcode][0]['url'] : NULL;
  $upload_fid = isset($form_state['values']['field_upload'][$field_upload_langcode][0]['fid']) ? $form_state['values']['field_upload'][$field_upload_langcode][0]['fid'] : NULL;

  $format_name = isset($form_state['values']['field_format'][$field_upload_langcode][0]) ? $form_state['values']['field_format'][$field_upload_langcode][0]['name'] : NULL;
  $format_tid = isset($form_state['values']['field_format'][$field_upload_langcode][0]) ? $form_state['values']['field_format'][$field_upload_langcode][0]['tid'] : NULL;
  $old_format_tid = isset($form['#node']->field_format[$field_upload_langcode][0]) ? $form['#node']->field_format[$field_upload_langcode][0]['tid'] : NULL;

  if ($format_tid && !$format_name) {
    $new_tax = taxonomy_term_load($format_tid);
    $format_name = $new_tax->name;
  }
  $type = NULL;
  // A format has been entered.
  if ($format_name) {
    // Previous format is the same, make no change.
    if ($old_format_tid == $format_tid) {
      $type = $format_name;
    }
    // No previous format, so user entered the format.
    elseif (!$old_format_tid) {
      $type = $format_name;
    }
    else {
      // User entered new taxonomy term that is different from previous.
      if ($old_tax = taxonomy_term_load($old_format_tid)) {
        if ($old_tax->name != $format_name) {
          $type = $format_name;
        }
      }
    }
  }
  // Ensure that only one type of resource is uploaded or linked to.
  if (count(array_filter(array($link_fid, $api, $upload_fid))) != 1) {
    if ($link_fid) {
      $remote_error = t('<strong>Remote file</strong> is populated - only one resource type can be used at a time.');
      form_set_error('field_link_remote_file', $remote_error);
    }
    if ($api) {
      $api_error = t('<strong>API or Website URL</strong> is populated - only one resource type can be used at a time.');
      form_set_error('field_link_api', $api_error);
    }
    if ($upload_fid) {
      $upload_error = t('<strong>Upload</strong> is populated - only one resource type can be used at a time.');
      form_set_error('field_upload', $upload_error);
    }
  }
  if (!empty($form_state['values']['field_link_api'][$field_link_api_langcode][0]['url']) &&
    !valid_url($form_state['values']['field_link_api'][$field_link_api_langcode][0]['url'], TRUE)) {
    // Require full url if using the API or Website URL option.
    $api_url_error = t('Please enter a full url (include http:// or https://)');
    form_set_error('field_link_api', $api_url_error);
  }
  // Autodetect file type if a new one is not provided.
  if (!$type) {
    if ($upload_fid || $link_fid || $api) {
      if ($upload_fid) {
        $file = file_load($upload_fid);
        $type = isset($file->filemime) ? dkan_dataset_discovery_extension($file->filemime) : '';
      }
      elseif ($link_fid) {
        $file = file_load($link_fid);
        // If you have a file that provides a content-type header or filemime of
        // octet-stream we can look if the file has a filename with an extension
        // we can use.
        if (isset($file->filemime) && $file->filemime == 'application/octet-stream') {
          $name = explode(".", $file->filename);
          if (count($name) > 1) {
            $type = $name[count($name) - 1];
          }
        }
        else {
          $type = isset($file->filemime) ? dkan_dataset_discovery_extension($file->filemime) : '';
        }

      }
      // @todo Probably remove all this
      elseif ($api) {
        $file = new GetRemoteFileInfo($api, t('!site_name crawler', array('!site_name' => variable_get('site_name'))), TRUE, file_directory_temp());
        $filemime = $file->getType();
        $type = dkan_dataset_discovery_extension($filemime);
      }
      // Check for geojson.
      if ($type == 'json' && isset($file)) {
        $response = drupal_http_request($file->uri, array('timeout' => 5));
        // dpm($response);
        if ($response->code == '200') {
          $data = $response->data;
          $decoded = drupal_json_decode($data);
          $geojson_types = array(
            'FeatureCollection',
            'GeometryCollection',
            'Feature',
            'Point',
            'MultiPoint',
            'LineString',
            'MultiLineString',
            'Polygon',
            'MultiPolygon',
          );
          foreach ($geojson_types as $geojson_type) {
            foreach ($decoded as $key => $value) {
              if ($geojson_type === $value) {
                $type = 'geojson';
              }
            }
          }
        }
      }
    }
  }
  $vocabs = taxonomy_vocabulary_get_names();

  $type = isset($type) ? $type : 'data';
  if (isset($vocabs['format'])) {
    $form_state['values']['field_format'][$field_format_langcode][0]['vid'] = $vocabs['format']->vid;
    if ($tax = taxonomy_get_term_by_name($type, 'format')) {
      $tax = array_values($tax);
      $form_state['values']['field_format'][$field_format_langcode][0]['tid'] = $tax[0]->tid;
      $form_state['values']['field_format'][$field_format_langcode][0]['name'] = $tax[0]->name;
    }
    else {
      $form_state['values']['field_format'][$field_format_langcode][0]['tid'] = 'autocreate';
      $form_state['values']['field_format'][$field_format_langcode][0]['name'] = $type;
    }
  }
}

/**
 * Implements hook_recline_format_alter().
 */
function dkan_dataset_recline_format_alter(&$format, $entity) {
  if (isset($entity->field_format['und'][0]['taxonomy_term']->name)) {
    $format = $entity->field_format['und'][0]['taxonomy_term']->name;
  }
}

/**
 * After build function for resource and dataset nodes.
 */
function dkan_dataset_resource_form_after_build($form, &$form_state) {
  // Hide 'Other' options if strict POD validation is enabled.
  if (isset($form['field_license']) && variable_get('dkan_dataset_form_pod_validation')) {
    unset($form['field_license']['und']['select']['#options']['select_or_other']);
    unset($form['field_license']['und']['select']['#options']['_none']);
  }
  if (isset($form['field_link_remote_file'])) {
    // Get langcode for field_link_remote_file.
    $field_link_remote_file_langcode = dkan_dataset_form_field_language($form, 'field_link_remote_file');
    $form['field_link_remote_file'][$field_link_remote_file_langcode][0]['filefield_dkan_remotefile']['url']['#attributes']['placeholder'] = 'eg. http://example.com/gold-prices-jan-2011.csv';
    $form['field_link_remote_file'][$field_link_remote_file_langcode][0]['filefield_dkan_remotefile']['select']['#suffix'] = '';
  }
  if (isset($form['field_link_api'])) {
    // Get langcode for field_link_api.
    $field_link_api_langcode = dkan_dataset_form_field_language($form, 'field_link_api');
    $form['field_link_api'][$field_link_api_langcode][0]['url']['#attributes']['placeholder'] = 'eg. http://example.com/gold-prices-jan-2011';
  }
  if (isset($form['field_format'])) {
    // Get langcode for field_format.
    $field_format_langcode = dkan_dataset_form_field_language($form, 'field_format');

    $form['field_format'][$field_format_langcode]['textfield']['#attributes']['placeholder'] = 'eg. csv, json';
    $form['field_format'][$field_format_langcode]['textfield']['#description'] = t('File format will be autodetected');
  }
  // Get langcode for body.
  $body_langcode = dkan_dataset_form_field_language($form, 'body');
  $form['body'][$body_langcode][0]['value']['#attributes']['placeholder'] = t('eg. Some useful notes about the data');
  return $form;
}

module_load_include('inc', 'node', 'node.pages');

/**
 * Creates initial resource node form.
 */
function dkan_dataset_prepare_resource_form($nid) {
  $type = 'resource';
  global $user;
  $node = (object) array(
    'uid' => $user->uid,
    'name' => (isset($user->name) ? $user->name : ''),
    'type' => $type,
  );
  $node->field_dataset_ref['nid'] = $nid;
  $new_form_state = array();
  $new_form_state['build_info']['args'][] = $node;
  // Don't pull from cache.
  $new_form_state['input'] = array();
  $resource_form = drupal_build_form($type . '_node_form', $new_form_state);
  $resource_form['#action'] = url('node/add/resource', array('query' => array('dataset' => $nid)));
  return $resource_form;
}

/**
 * Implements hook_block_info().
 */
function dkan_dataset_block_info() {
  $blocks['dkan_dataset_dataset_help'] = array(
    'info' => t('What are datasets?'),
  );
  $blocks['dkan_dataset_resource_help'] = array(
    'info' => t('What is data?'),
  );
  $blocks['dkan_dataset_resource_nodes'] = array(
    'info' => t('Resources'),
  );
  $blocks['dkan_dataset_visualizations_list'] = array(
    'info' => t('Visualizations'),
  );
  return $blocks;
}

/**
 * Implements hook_block_view().
 */
function dkan_dataset_block_view($delta = '') {
  $block = array();
  switch ($delta) {
    case 'dkan_dataset_dataset_help':
      $block['subject'] = t('What are datasets?');
      $block['content'] = dkan_dataset_dataset_form_help();
      break;

    case 'dkan_dataset_resource_help':
      $block['subject'] = t('What is data?');
      $block['content'] = dkan_dataset_resource_form_help();
      break;

    case 'dkan_dataset_resource_nodes':
      $block['subject'] = t('Resources');
      $block['content'] = dkan_dataset_resource_nodes();
      break;

    case 'dkan_dataset_visualizations_list':
      $block['subject'] = t('Visualizations');
      $block['content'] = dkan_dataset_visualizations_list();
      break;

  }

  return $block;
}

/**
 * Adds block for dataset edit form.
 */
function dkan_dataset_dataset_form_help() {
  return t('Datasets are simply used to group related pieces of data. These can then be found under a single url with a description and licensing information.');
}

/**
 * Adds block for resource edit form.
 */
function dkan_dataset_resource_form_help() {
  return t('Data can be any file or link to a file containing useful data.');
}

/**
 * Returns resources within a dataset.
 */
function dkan_dataset_resource_nodes() {
  $output = '';
  $nodes = array();
  $current_node = menu_get_object();

  if (isset($current_node->type) && $current_node->type == 'resource' && isset($current_node->field_dataset_ref[LANGUAGE_NONE][0])) {
    $wrapper = entity_metadata_wrapper('node', $current_node);
    $target = $wrapper->field_dataset_ref->value();
    $target_id = $target[0]->nid;
    if ($target_id) {
      $nodes = dkan_dataset_get_resource_nodes($target_id);
    }
  }
  else {
    if ($query = drupal_get_query_parameters()) {
      if (isset($query['dataset'])) {
        $nodes = dkan_dataset_get_resource_nodes($query['dataset']);
      }
    }
  }
  if ($nodes) {
    foreach ($nodes as $node) {
      $attributes = array('attributes' => array('class' => array('list-group-item')), 'html' => TRUE);
      $links[] = l('<span>' . $node->title . '</span>', 'node/' . $node->nid, $attributes);
    }
    if (!$current_node) {
      $links[] = '';
    }
    $output = theme('item_list', array('items' => $links, 'attributes' => array('class' => array('list-group'))));
  }
  return $output;
}

/**
 * Returns list of visualizations based on a resource.
 */
function dkan_dataset_visualizations_list() {
  $output = '';
  $current_node = menu_get_object();
  if (isset($current_node->type) && $current_node->type == 'resource') {
    if ($current_node->uuid) {
      // Find all visualizations created from this resource.
      $nodes = dkan_dataset_get_visualization_entities($current_node->uuid);
    }
    if ($nodes) {
      foreach ($nodes as $node) {
        $attributes = array('attributes' => array('class' => array('list-group-item')), 'html' => TRUE);
        // Fetch title and type.
        $result = db_query("SELECT type, title FROM {eck_visualization} WHERE id = :id", array(':id' => $node->nid));
        foreach ($result as $row) {
          // Add visualization to the list.
          $links[] = l($row->title, 'visualization/' . $row->type . '/' . $node->nid, $attributes);
        }
      }
      if (!$current_node) {
        $links[] = '';
      }
      $output = theme('item_list', array('items' => $links, 'attributes' => array('class' => array('list-group'))));
    }
  }
  return $output;
}

/**
 * Returns the display language for the fields attached to the given form.
 */
function dkan_dataset_form_field_language($form, $field_name = NULL) {
  $langcode = 'und';
  if ($field_name && isset($form[$field_name]) && !empty($form[$field_name]['#language'])) {
    $langcode = $form[$field_name]['#language'];
  }
  $langcode = field_valid_language($langcode, FALSE);
  return $langcode;
}
