<?php

/**
 * @file
 * Pages for the datastore.
 */

/**
 * Callback for Data API instructions.
 */
function dkan_dataset_datastore_api($node) {
  return 'coming soon';
}

/**
 * Callback for download tab.
 */
function dkan_dataset_download($node) {
  $uri = isset($node->field_upload[$node->language][0]['uri']) ? $node->field_upload[$node->language][0]['uri'] : $node->field_upload[0]['uri'];
  $url = file_create_url($uri);
  return drupal_goto($url);
}

/**
 * Callback for back link.
 */
function dkan_dataset_back($node) {
  if (isset($node->field_dataset_ref[$node->language][0]['target_id'])) {
    drupal_goto('node/' . $node->field_dataset_ref[$node->language][0]['target_id']);
  }
  return '';
}

/**
 * Callback for 'Add Resouce' tab.
 */
function dkan_dataset_add_resource($node) {
  drupal_goto('node/add/resource', array('query' => array('dataset' => $node->nid)));
}

/**
 * Render a feeds import form on node/id/import pages.
 */
function dkan_dataset_import_tab_form($form, &$form_state, $node) {
  module_load_include('inc', 'feeds', 'feeds.pages');
  $importer_id = feeds_get_importer_id($node->type);
  $source = feeds_source($importer_id, $node->nid);

  $form = array();
  $form['#feed_nid'] = $node->nid;
  $form['#importer_id'] = $importer_id;
  $form['#redirect'] = 'node/' . $node->nid;
  $form['source_status'] = array(
    '#type' => 'fieldset',
    '#title' => t('Datastore Status'),
    '#tree' => TRUE,
    '#description' => t('Click "Import" below to import or re-import the data from your file into the datastore'),
    '#value' => feeds_source_status($source),
  );
  $form = confirm_form($form, t('Import all content from source?'), 'node/' . $node->nid, '', t('Import'), t('Cancel'), 'confirm feeds update');
  drupal_set_title('Manage Datastore');
  $progress = $source->progressImporting();
  if ($progress !== FEEDS_BATCH_COMPLETE) {
    $form['actions']['submit']['#disabled'] = TRUE;
    $form['actions']['submit']['#value'] =
      t('Importing (@progress %)', array('@progress' => number_format(100 * $progress, 0)));
  }
  $form['#submit'] = array('feeds_import_tab_form_submit');
  return $form;
}

/**
 * Render a feeds delete form.
 *
 * Used on both node pages and configuration pages.
 * Therefore $node may be missing.
 */
function dkan_dataset_delete_tab_form($form, &$form_state, $importer_id, $node = NULL) {
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
    '#title' => t('Delete Datatsore Records'),
    '#tree' => TRUE,
    '#value' => feeds_source_status($source),
  );
  $form = confirm_form($form, t('Delete all items from source?'), $form['#redirect'], '', t('Delete'), t('Cancel'), 'confirm feeds update');
  drupal_set_title('Manage Datastore');
  $progress = $source->progressClearing();
  if ($progress !== FEEDS_BATCH_COMPLETE) {
    $form['actions']['submit']['#disabled'] = TRUE;
    $form['actions']['submit']['#value'] =
      t('Deleting (@progress %)', array('@progress' => number_format(100 * $progress, 0)));
  }
  $form['#submit'] = array('feeds_delete_tab_form_submit');
  return $form;
}

/**
 * Render a feeds unlock form.
 *
 * Used on both node pages and configuration pages.
 * Therefore $node may be missing.
 */
function dkan_dataset_unlock_tab_form($form, &$form_state, $importer_id, $node = NULL) {
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

