<?php

/**
 * @file
 * Callbacks for datastore pages.
 */

/**
 * Callback for Data API instructions.
 */
function dkan_datastore_datastore_api($node) {
  $datastore = dkan_datastore_go($node->uuid);
  $output = $datastore->apiForm();
  return $output;
}

/**
 * Callback for download tab.
 */
function dkan_datastore_download($node) {
  $node_wrapper = entity_metadata_wrapper('node', $node);
  if ($node_wrapper->field_upload->value()) {
    $url = file_create_url($node_wrapper->field_upload->value()->uri);
    return drupal_goto($url);
  }
  else {
    drupal_set_message(t('No download available for this resource'));
    return '';
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
 * Render a datastore delete form.
 *
 * Used on both node pages and configuration pages.
 * Therefore $node may be missing.
 */
function dkan_datastore_delete_tab_form($form, &$form_state, $importer_id, $node = NULL) {
  $datastore = dkan_datastore_go($node->uuid);
  $form = $datastore->deleteForm($form_state);
  $form['#submit'] = array('feeds_delete_tab_form_submit');
  return $form;
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
  $form_state['#tables_to_drop'] = $form['#tables_to_drop'];
  $datastore->dropFormSubmit($form_state);
  $form_state['redirect'] = $form['#redirect'];
}
