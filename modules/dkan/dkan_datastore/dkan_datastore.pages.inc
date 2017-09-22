<?php

/**
 * @file
 * Callbacks for datastore pages.
 */

// Default to 50MB.
define('MAX_FILE_REMOTE_PROXY_DEFAULT', 1024 * 1024 * 50);

/**
 * Callback for Data API instructions.
 */
function dkan_datastore_datastore_api($node) {
  $status = dkan_datastore_status($node);
  switch ($status) {
    case DKAN_DATASTORE_EXISTS:
      $datastore = dkan_datastore_go($node->uuid);
      $output = $datastore->apiForm();
      return $output;

    case DKAN_DATASTORE_FILE_EXISTS:
      drupal_set_message(t('You need to add your file to the datastore in order to use the DATA API'));
      $redirect_path = 'node/' . $node->nid . '/datastore';
      break;

    default:
      drupal_set_message(t('This resource can not be added to the datastore'));
      $redirect_path = 'node/' . $node->nid;
      break;
  }
  drupal_goto($redirect_path);
}

/**
 * Callback for download tab.
 */
function dkan_datastore_download($node) {
  $node_wrapper = entity_metadata_wrapper('node', $node);
  $link_type = dkan_datastore_resource_link_type($node);
  $url = $link_type['url'];
  $type = $link_type['type'];

  if ($type == 'upload') {
    return drupal_goto($url);
  }
  elseif ($type == 'remote') {
    $size = isset($node_wrapper->field_link_remote_file->value()['filesize']) ? (int) $node_wrapper->field_link_remote_file->value()['filesize'] : 0;

    // dkan_datastore_proxy() loads the file locally,
    // which has a big memory footprint.
    // If the file is too large we don't want to proxy.
    $max = (int) variable_get('dkan_datastore_max_file_remote_proxy_size', MAX_FILE_REMOTE_PROXY_DEFAULT);
    if ($size > $max) {
      drupal_goto($url);
    }
    else {
      dkan_datastore_proxy($node);
    }
  }
  elseif ($type == 'link') {
    return drupal_goto($url);
  }
  else {
    drupal_set_message(t('No download available for this resource'));
    return '';
  }
}

/**
 * Proxy remote resources.
 */
function dkan_datastore_proxy($node) {
  $allowed_types = array('csv');
  $node_wrapper = entity_metadata_wrapper('node', $node);
  $remote_file = $node_wrapper->field_link_remote_file->value();
  $uri = $remote_file['uri'];
  $filename = $remote_file['filename'];
  $mime = $remote_file['filemime'];
  $type = recline_get_data_type($remote_file['filemime']);

  if (in_array($type, $allowed_types)) {
    drupal_add_http_header('Content-Type', $mime);
    drupal_add_http_header('Content-Disposition', 'attachment; filename=' . $filename);
    print file_get_contents($uri);
  }
  else {
    // Not in allowed types, treat the file URI as a normal url.
    return drupal_goto($uri);
  }
}

/**
 * Callback for back link.
 */
function dkan_datastore_back($node) {
  $node_wrapper = entity_metadata_wrapper('node', $node);
  $dataset = $node_wrapper->field_dataset_ref->value();
  if ($dataset) {
    drupal_goto('node/' . $dataset->nid);
  }
  return '';
}

/**
 * Callback for 'Add Resouce' tab.
 */
function dkan_datastore_add_resource($node) {
  drupal_goto('node/add/resource', array('query' => array('dataset' => $node->nid)));
}

/**
 * Render a datastore import form on node/id/import pages.
 */
function dkan_datastore_import_tab_form($form, &$form_state, $node) {
  $datastore = dkan_datastore_go($node->uuid);
  $form = $datastore->manageForm($form_state);
  $form['#datastore'] = $datastore;
  return $form;
}

/**
 * Submit function for import tab.
 */
function dkan_datastore_import_tab_form_submit($form, &$form_state) {
  $datastore = $form['#datastore'];
  $datastore->manageFormSubmit($form_state);
}

/**
 * Render a datastore unlock form.
 *
 * Used on both node pages and configuration pages.
 * Therefore $node may be missing.
 */
function dkan_datastore_unlock_tab_form($form, &$form_state, $importer_id, $node = NULL) {
  module_load_include('inc', 'feeds', 'feeds.pages');
  if (empty($node)) {
    $source = feeds_source($importer_id);
    $form['#redirect'] = 'import/' . $source->id;
  }
  else {
    $importer_id = feeds_get_importer_id($node->type);
    $source = feeds_source($importer_id, $node->nid);
    $form['#redirect'] = 'node/' . $source->feed_nid;
  }
  // Form cannot pass on source object.
  $form['#importer_id'] = $source->id;
  $form['#feed_nid'] = $source->feed_nid;
  $form['source_status'] = array(
    '#type' => 'fieldset',
    '#title' => t('Status'),
    '#tree' => TRUE,
    '#value' => feeds_source_status($source),
  );
  $form = confirm_form($form, t('Unlock this importer?'), $form['#redirect'], '', t('Delete'), t('Cancel'), 'confirm feeds update');
  if ($source->progressImporting() == FEEDS_BATCH_COMPLETE && $source->progressClearing() == FEEDS_BATCH_COMPLETE) {
    $form['source_locked'] = array(
      '#type' => 'markup',
      '#title' => t('Not Locked'),
      '#tree' => TRUE,
      '#markup' => t('This importer is not locked, therefore it cannot be unlocked.'),
    );
    $form['actions']['submit']['#disabled'] = TRUE;
    $form['actions']['submit']['#value'] = t('Unlock (disabled)');
  }
  else {
    $form['actions']['submit']['#value'] = t('Unlock');
  }
  $form['#submit'] = array('feeds_unlock_tab_form_submit');
  return $form;
}

/**
 * Render a datastore drop form on node/id/drop pages.
 */
function dkan_datastore_drop_tab_form($form, &$form_state, $node) {
  $datastore = dkan_datastore_go($node->uuid);
  $form = $datastore->dropForm($form_state);
  $form['#datastore'] = $datastore;
  return $form;
}

/**
 * Submit function for drop tab.
 */
function dkan_datastore_drop_tab_form_submit($form, &$form_state) {
  $datastore = $form['#datastore'];
  $datastore->dropFormSubmit($form_state);
  $form_state['redirect'] = $form['#redirect'];
}
