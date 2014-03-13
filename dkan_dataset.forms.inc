<?php

/**
 * @file
 * Form elements for DKAN.
 */

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
    unset($element['path']);
  }
}

/**
 * Implements hook_field_group_pre_render().
 */
function dkan_dataset_field_group_pre_render(&$element, $group, &$form) {
  if (module_exists('context')) {
    $contexts = context_active_contexts();
  }
  $query = drupal_get_query_parameters();
  $arg2 = arg(2);
  $additional = isset($form['#additional_processed']) && $form['#additional_processed'] ? TRUE : FALSE;
  if ((isset($form['#groups'][$group->group_name]->disabled) && $form['#groups'][$group->group_name]->disabled) || isset($query['additional'])) {
    if ($group->group_name == 'group_primary') {
      $element += array(
        '#type' => 'markup',
        '#weight' => $group->weight,
        '#access' => FALSE,
      );
    }
  }
  elseif (isset($form['#action']) && $form['#action'] == '/node/add/dataset' && !$additional && $arg2 != 'edit') {
    if ($group->group_name == 'group_additional') {
      $element += array(
        '#type' => 'markup',
        '#weight' => $group->weight,
        '#access' => FALSE,
      );
    }
  }
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function dkan_dataset_form_alter(&$form, &$form_state, $form_id) {

  if ($form_id == 'dataset_node_form') {

    $form['#attached']['css'][] = drupal_get_path('module', 'dkan_dataset') . '/dkan_dataset.css';
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
      $form['path']['alias']['#title'] = 'URL';
    }
    // Adding descriptions.
    $form['title']['#attributes']['placeholder'] = t('eg. A descriptive title');

    // Get langcode for body.
    $body_langcode = dkan_dataset_form_field_language($form, 'body');

    $form['body'][$body_langcode][0]['#prefix'] = '<div id="prefix">';
    $form['body'][$body_langcode][0]['#suffix'] = '</div>';
    $form['path']['alias']['#description'] = '';
    $form['path']['alias']['#title'] = '';

    // Get langcode for field_tags.
    $field_tags_langcode = dkan_dataset_form_field_language($form, 'field_tags');

    $form['field_tags'][$field_tags_langcode]['#description'] = t('eg. economy, mental health, government');

    // Get langcode for field_license.
    $field_license_langcode = dkan_dataset_form_field_language($form, 'field_license');
    // Add icon to license field.
    $form['field_license'][$field_license_langcode]['#description'] = '<i class="icon-large icon-info-sign"></i>License definitions and additional information can be found at <a href="http://opendefinition.org">opendefinition.org</a>';

    // Get langcode for field_additional_info.
    $field_additional_info_langcode = dkan_dataset_form_field_language($form, 'field_additional_info');
    foreach ($form['field_additional_info'][$field_additional_info_langcode] as $key => $values) {
      if (is_int($key)) {
        $form['field_additional_info'][$field_additional_info_langcode][$key]['first']['#attributes']['placeholder'] = t('key');
        $form['field_additional_info'][$field_additional_info_langcode][$key]['second']['#attributes']['placeholder'] = t('value');
      }
    }

    if (isset($form['og_group_ref'])) {
      // Get langcode for og_group_ref.
      $og_group_ref_langcode = dkan_dataset_form_field_language($form, 'og_group_ref');

      // Don't show groups if user doesn't have any groups to add.
      $groups = $form['og_group_ref'][$og_group_ref_langcode][0]['default']['#options'];
      if (count($groups) < 2) {
        $form['og_group_ref']['#access'] = FALSE;
      }
      // Move groups to vertical tabs.
      $form['og_group_ref']['#group'] = 'additional_settings';
      $form['og_group_ref']['#title'] = t('Groups');
      $form['og_group_ref']['#type'] = 'fieldset';
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
      if (variable_get('dkan_dataset_form_ajax', 0)) {
        $form['actions']['next']['#ajax'] = array(
          'callback' => 'dkan_dataset_dataset_submit_js',
          'wrapper' => $form['#id'],
          'method' => 'replace',
          'effect' => 'fade',
        );
      }
      else {
        $form['actions']['next']['#submit'] = array('node_form_submit', 'dkan_dataset_dataset_form_submit');
      }
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
      }
    }
    else {
      $form['actions']['submit']['#value'] = t('Finish');
      $form['#action'] = '/node/add/dataset';
    }
  }
  if ($form_id == 'resource_node_form') {
    drupal_add_library('system', 'jquery.bbq');
    $form['#attached']['css'][] = drupal_get_path('module', 'dkan_dataset') . '/dkan_dataset.css';
    $form['#attached']['js'][] = drupal_get_path('module', 'dkan_dataset') . '/js/dkan_forms.js';

    if (isset($form['feeds'])) {
      $form['feeds']['#access'] = FALSE;
    }

    $form['#after_build']['dkan_dataset'] = 'dkan_dataset_resource_form_after_build';

    // Get langcode for field_dataset_ref.
    $field_dataset_ref_langcode = dkan_dataset_form_field_language($form, 'field_dataset_ref');
    $form['field_dataset_ref'][$field_dataset_ref_langcode]['#description'] = t('Dataset that this resource is attached to.');

    // Get langcode for field_upload.
    $field_upload_langcode = dkan_dataset_form_field_language($form, 'field_upload');

    $form['field_upload'][$field_upload_langcode][0]['#title'] = '';
    $form['field_upload'][$field_upload_langcode][0]['view']['#collapsible'] = TRUE;
    $form['field_upload'][$field_upload_langcode][0]['view']['#collasped'] = TRUE;

    // Get langcode for field_link_file.
    $field_link_file_langcode = dkan_dataset_form_field_language($form, 'field_link_file');
    $field_link_remote_file_langcode = dkan_dataset_form_field_language($form, 'field_link_remote_file');
    $form['field_link_file'][$field_link_file_langcode][0]['#description'] = 'Link to a CSV file that will be imported into the DKAN datastore.';
    $form['field_link_remote_file'][$field_link_file_langcode][0]['#description'] = 'Link to a CSV file that will be imported into the DKAN datastore.';

    // Get langcode for field_link_api.
    $field_link_api_langcode = dkan_dataset_form_field_language($form, 'field_link_api');
    $form['field_link_api'][$field_link_api_langcode][0]['#description'] = 'Link to a public API or information source.';

    $form['title']['#attributes']['placeholder'] = t('eg. January Gold Prices');
    $form['title']['#required'] = TRUE;

    $form['field_link_api'][$field_link_api_langcode][0]['#title'] = '';

    $form['field_link_file'][$field_link_file_langcode][0]['#title'] = '';
    $form['field_link_remote_file'][$field_link_file_langcode][0]['#title'] = '';

    // Move groups to vertical tabs.
    $form['og_group_ref']['#group'] = 'additional_settings';
    $form['og_group_ref']['#title'] = t('Groups');
    $form['og_group_ref']['#type'] = 'fieldset';

    $action_query = '';
    if ($query = drupal_get_query_parameters()) {
      if (isset($query['dataset'])) {
        $form['field_dataset_ref'][$field_dataset_ref_langcode]['#default_value'] = $query['dataset'];
        $action_query = '?dataset=' . $query['dataset'];
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
    $form['#action'] = '/node/add/resource' . $action_query;
    if (isset($query['dataset']) || (isset($form_state['input']['field_dataset_ref']['und'][0]))) {
      $form['actions']['additional'] = array(
        '#type' => 'submit',
        '#value' => t('Next: Additional Info'),
        '#weight' => -5,
        '#submit' => array('node_form_submit'),
      );
      if (variable_get('dkan_resource_form_ajax', 0)) {
        $form['actions']['additional']['#ajax'] = array(
          'callback' => 'dkan_dataset_additional_submit_js',
          'wrapper' => $form['#id'],
          'method' => 'replace',
          'effect' => 'fade',
        );
      }
      else {
        $form['actions']['additional']['#submit'] = array('node_form_submit', 'dkan_dataset_resource_additional_form_submit');
      }
    }
    $form['actions']['another'] = array(
      '#type' => 'submit',
      '#value' => t('Save and add another'),
      '#weight' => 10,
      '#submit' => array('node_form_submit'),
    );
    if (variable_get('dkan_resource_form_ajax', 0)) {
      $form['actions']['another']['#ajax'] = array(
        'callback' => 'dkan_dataset_resource_another_submit_js',
        'wrapper' => $form['#id'],
        'method' => 'replace',
        'effect' => 'fade',
      );
    }
    else {
      $form['actions']['another']['#submit'] = array('node_form_submit', 'dkan_dataset_resource_form_submit');
    }
  }
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
 * Dataset node form submit handler.
 */
function dkan_dataset_dataset_form_submit($form, &$form_state) {
  if (isset($form_state['node']->nid)) {
    drupal_goto('node/add/resource', array('query' => array('dataset' => $form_state['node']->nid)));
  }
}

/**
 * Validation function for resource node form.
 */
function dkan_dataset_resource_node_form_validate($form, &$form_state) {
  // Get langcode for field_link_file.
  $field_link_file_langcode = dkan_dataset_form_field_language($form, 'field_link_file');
  $field_link_remote_file_langcode = dkan_dataset_form_field_language($form, 'field_link_remote_file');
  // Get langcode for field_link_api.
  $field_link_api_langcode = dkan_dataset_form_field_language($form, 'field_link_api');
  // Get langcode for field_upload.
  $field_upload_langcode = dkan_dataset_form_field_language($form, 'field_upload');
  // Get langcode for field_format.
  $field_format_langcode = dkan_dataset_form_field_language($form, 'field_format');

  $link = isset($form_state['values']['field_link_file'][$field_link_file_langcode]) ? $form_state['values']['field_link_file'][$field_link_file_langcode][0]['url'] : NULL;
  $api = $form_state['values']['field_link_api'][$field_link_api_langcode][0]['url'];
  $upload_fid = $form_state['values']['field_upload'][$field_upload_langcode][0]['fid'];
  $link_fid = isset($form_state['values']['field_link_remote_file'][$field_upload_langcode]) ? $form_state['values']['field_link_remote_file'][$field_upload_langcode][0]['fid'] : NULL;
  // Ensure that only one type of resource is uploaded or linked to.
  if ($link && ($api || $upload_fid || $link_fid) || $api && ($link || $upload_fid || $link_fid) || ($upload_fid && $api || $link) || ($link_fid && $api || $link)) {
    form_set_error('field_link_file', 'Only one resource type can be added.');
  }
  // Autodetect file type.
  if ($upload_fid || $link_fid || $api) {
    if ($upload_fid) {
      $file = file_load($upload_fid);
      $type = substr($file->filename, strrpos($file->filename, '.') + 1);
    }
    elseif ($link_fid) {
      $file = file_load($link_fid);
      $type = substr($file->filename, strrpos($file->filename, '.') + 1);
    }
    elseif ($api) {
      $request = drupal_http_request($api);
      include_once DRUPAL_ROOT . '/includes/file.mimetypes.inc';
      if (!isset($request->error)) {
        $mapping = file_mimetype_mapping();
        $offset = array_search($request->headers['content-type'], $mapping['mimetypes']);
        if ($offset) {
          $type = array_search($offset, $mapping['extensions']);
        }
      }
      $type = 'data';
    }
    else {
      $type = substr($link, strrpos($link, '.') + 1);
    }
    $vocabs = taxonomy_vocabulary_get_names();
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
}

/**
 * After build function for resource and dataset nodes.
 */
function dkan_dataset_resource_form_after_build($form, &$form_state) {
  if (isset($form['field_link_file'])) {
    // Get langcode for field_link_file.
    $field_link_file_langcode = dkan_dataset_form_field_language($form, 'field_link_file');
    $field_link_remote_file_langcode = dkan_dataset_form_field_language($form, 'field_link_file');
    // Get langcode for field_link_api.
    $field_link_api_langcode = dkan_dataset_form_field_language($form, 'field_link_api');

    $form['field_link_file'][$field_link_file_langcode][0]['url']['#attributes']['placeholder'] = 'eg. http://example.com/gold-prices-jan-2011.csv';
    $form['field_link_remote_file'][$field_link_file_langcode][0]['filefield_remotefile']['url']['#attributes']['placeholder'] = 'eg. http://example.com/gold-prices-jan-2011.csv';
    $form['field_link_remote_file'][$field_link_file_langcode][0]['filefield_remotefile']['select']['#suffix'] = '';
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
 * Builds the additional data for the dateset form.
 */
function dkan_dataset_additional_submit_js($form, &$form_state) {
  form_load_include($form_state, 'inc', 'node', 'node.pages');
  module_load_include('inc', 'node', 'node.pages');
  $type = 'dataset';
  global $user;
  // Return the actual form if it contains errors.
  if (form_get_errors()) {
    return $form;
  }
  // Get langcode for field_dataset_ref.
  $field_dataset_ref_langcode = dkan_dataset_form_field_language($form, 'field_dataset_ref');

  $nid = $form_state['values']['field_dataset_ref'][$field_dataset_ref_langcode][0]['target_id'];
  $node = node_load($nid);
  $dataset_form = drupal_get_form($type . '_node_form', $node);

  $dataset_form['#groups']['group_primary']->disabled = TRUE;
  $dataset_form['#additional_processed'] = TRUE;

  $stage = dkan_form_create_stages('dataset-additional', $nid);

  $commands[] = ajax_command_replace('.node-resource-form', drupal_render($dataset_form));
  $commands[] = ajax_command_prepend('.node-dataset-form', $stage);
  $commands[] = ajax_command_settings(array('dkanAdditional' => array('nid' => $nid)), TRUE);
  $output = array('#type' => 'ajax', '#commands' => $commands);
  return $output;
}

/**
 * Builds the resource form after resource form submit.
 */
function dkan_dataset_resource_another_submit_js($form, &$form_state) {
  // Return the actual form if it contains errors.
  if (form_get_errors()) {
    return $form;
  }
  // Get langcode for field_dataset_ref.
  $field_dataset_ref_langcode = dkan_dataset_form_field_language($form, 'field_dataset_ref');

  $nid = isset($form_state['build_info']['args'][0]->field_dataset_ref['nid']) ? $form_state['build_info']['args'][0]->field_dataset_ref['nid'] : $form_state['values']['field_dataset_ref'][$field_dataset_ref_langcode][0]['target_id'];
  $resource_form = dkan_dataset_prepare_resource_form($nid);
  $stage = dkan_form_create_stages('resource-edit', $nid);

  $commands[] = ajax_command_replace('ol.stages', $stage);
  $message = '<div id="messages"><div class="messages status"> Your resource <em class="placeholder">' . $form_state['values']['title'] . '</em> has been created. View it here: ' . l($form_state['values']['title'], 'node/' . $form_state['nid']) . ' or add another.</div></div>';

  $commands[] = ajax_command_replace('.node-resource-form', drupal_render($resource_form));
  $commands[] = ajax_command_prepend('ul.nav-simple', '<li>' . l($form_state['values']['title'], 'node/' . $form_state['nid'] . '/edit') . '</li>');
  $commands[] = ajax_command_prepend('form.node-form', $message);
  // Needed to prepend language after ajax redirect. Ugly.
  global $language_url;
  if (isset($language_url->provider) && $language_url->provider == 'locale-url') {
    $push = array('dkanPush' => array('nid' => $form_state['node']->nid, 'lang' => $language_url->prefix));
  }
  else {
    $push = array('dkanPush' => array('nid' => $form_state['node']->nid));
  }
  $commands[] = ajax_command_settings($push, TRUE);
  $output = array('#type' => 'ajax', '#commands' => $commands);
  return $output;
}

/**
 * Builds the resource form after the dataset submit.
 */
function dkan_dataset_dataset_submit_js($form, &$form_state) {
  // Return the actual form if it contains errors.
  if (form_get_errors()) {
    return $form;
  }
  $resource_form = dkan_dataset_prepare_resource_form($form_state['node']->nid);

  // Replacement elements.
  $stage = dkan_form_create_stages('resource-edit', $form_state['node']->nid);
  $breadcrumbs = '<ul class="breadcrumb"><li><a href="/">Home</a></li><li><a href="/dataset">Datasets</a></li><li>' . l($form_state['values']['title'], 'node/' . $form_state['nid']) . '</li><li><a href="/node/add/resource?dataset=' . $form_state['nid'] . '" class="active">Add Resource</a></li></ul>';
  $resources_block = '<section class="block block-dkan-forms block-dkan-forms-resource-nodes block-dkan-forms-dkan-forms-resource-nodes even" id="block-dkan-forms-dkan-forms-resource-nodes"><div class="block-inner clearfix"><h2 class="block-title">Resources</h2><div class="content clearfix"><div class="item-list"><ul class="nav-simple"><li class="last"></li></ul></div><a id="context-block-dkan_dataset-dkan_forms_resource_nodes" class="context-block editable edit-resource-create"></a></div></div></section>';

  $commands[] = ajax_command_append('#region-sidebar-first .region-inner', $resources_block);
  $commands[] = ajax_command_replace('ul.breadcrumb', $breadcrumbs);
  $commands[] = ajax_command_replace('#block-dkan-forms-dkan-forms-dataset-help h2', '<h2 class="block-title">What is data?</h2>');
  $commands[] = ajax_command_replace('#block-dkan-forms-dkan-forms-dataset-help .content', '<div class="content">' . dkan_dataset_resource_form_help() . '</div>');
  $commands[] = ajax_command_replace('ol.stages', $stage);
  $commands[] = ajax_command_replace('#page-title', '<h1 class="title" id="page-title">Add data</h1>');
  // Needed to prepend language after ajax redirect. Ugly.
  global $language_url;
  if (isset($language_url->provider) && $language_url->provider == 'locale-url') {
    $push = array('dkanPush' => array('nid' => $form_state['node']->nid, 'lang' => $language_url->prefix));
  }
  else {
    $push = array('dkanPush' => array('nid' => $form_state['node']->nid));
  }
  $commands[] = ajax_command_settings($push, TRUE);

  $commands[] = ajax_command_replace('#' . $form['#id'], drupal_render($resource_form));
  $output = array('#type' => 'ajax', '#commands' => $commands);
  return $output;
}

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
  $resource_form['#action'] = '/node/add/resource?dataset=' . $nid;
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

  if (isset($current_node->type) && $current_node->type == 'resource' && isset($current_node->field_dataset_ref[0])) {
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
      $links[] = l('<span>' . $node->title . '</span>', 'node/' . $node->nid, array('html' => TRUE));
    }
    if (!$current_node) {
      $links[] = '';
    }
    $output = theme('item_list', array('items' => $links, 'attributes' => array('class' => array('nav-simple'))));
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
