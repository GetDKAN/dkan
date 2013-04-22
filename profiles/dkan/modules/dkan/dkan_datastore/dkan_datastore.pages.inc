<?php

/**
 * @file
 * Callbacks for datastore pages.
 */

/**
 * Callback for Data API instructions.
 */
function dkan_datastore_datastore_api($node) {
  $importer_id = feeds_get_importer_id($node->type);
  $source_config = feeds_source($importer_id, $node->nid);
  $fetcher = get_class($source_config->importer->fetcher);
  $source = isset($source_config->config[$fetcher]['source']) ? $source_config->config[$fetcher]['source'] : '';
  $output = '<h2>' . t('DKAN Datastore API') . '</h2>';
  if ($source) {
    $progress = $source_config->progressImporting();
    if ($progress != FEEDS_BATCH_COMPLETE) {
      $output .= t('This data source has not yet completed importing. Import is at @progress%. The API for this data source will be available upon the completion of the import process.', array('@progress' => number_format(100 * $progress, 0)));
    }
    else {
      $output .= t('Access resource data via a web API with powerful query support.');
      $output .= '<h3>' . t('Resource ID') . '</h3>';
      $output .= t('The Resource ID for this resource is ') . $node->uuid;
      $output .= '<h3>' . t('Example Query') . '</h3>';
      global $base_url;
      $url = $base_url . '/api/action/datastore/search.json?resource_id=' . $node->uuid . '&limit=5';
      $output .= '<p>' . l($url, $url) . '</p>';
      $output .= '<p>' . t('Query this datastore and return first five results') . '</p>';
      $output .= '<h3>' . t('Documentation') . '</h3>';
      $output .= '<p>' . t('See DKAN API documentation for more details: ') . l(t('DKAN Datastore API'), 'http://docs.getdkan.com/docs/dkan-documentation/dkan-api/datastore-api') . '</p>';
    }
  }
  else {
    $output .= t('No files have been added to the datastore.');
  }
  return $output;
}

/**
 * Callback for download tab.
 */
function dkan_datastore_download($node) {
  $uri = isset($node->field_upload[$node->language][0]['uri']) ? $node->field_upload[$node->language][0]['uri'] : $node->field_upload[0]['uri'];
  $url = file_create_url($uri);
  return drupal_goto($url);
}

/**
 * Callback for back link.
 */
function dkan_datastore_back($node) {
  if (isset($node->field_dataset_ref[$node->language][0]['target_id'])) {
    drupal_goto('node/' . $node->field_dataset_ref[$node->language][0]['target_id']);
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
 * Render a feeds import form on node/id/import pages.
 */
function dkan_datastore_import_tab_form($form, &$form_state, $node) {
  module_load_include('inc', 'feeds', 'feeds.pages');
  $importer_id = feeds_get_importer_id($node->type);
  $source_config = feeds_source($importer_id, $node->nid);
  $fetcher = get_class($source_config->importer->fetcher);
  $source = isset($source_config->config[$fetcher]['source']) ? $source_config->config[$fetcher]['source'] : '';
  $form = array();
  if ($source) {
    $form['#feed_nid'] = $node->nid;
    $form['#importer_id'] = $importer_id;
    $form['#redirect'] = 'node/' . $node->nid;
    $form['source_status'] = array(
      '#type' => 'fieldset',
      '#title' => t('Datastore Status'),
      '#tree' => TRUE,
      '#description' => t('Click "Import" below to import or re-import the data from your file into the datastore'),
      '#value' => feeds_source_status($source_config),
    );
    $form = confirm_form($form, t('Import all content from source?'), 'node/' . $node->nid, '', t('Import'), t('Cancel'), 'confirm feeds update');
    drupal_set_title('Manage Datastore');
    $progress = $source_config->progressImporting();
    $form['#count'] = $source_config->itemCount();
    if ($progress !== FEEDS_BATCH_COMPLETE) {
      $form['actions']['submit']['#disabled'] = TRUE;
      $form['actions']['submit']['#value'] = t('Importing (@progress %)', array('@progress' => number_format(100 * $progress, 0)));
    }
  }
  else {
    $form['no_source'] = array(
      '#type' => 'item',
      '#markup' => t('There is nothing to manage! You need to upload or link to a file in order to use the datastore.'),
    );
  }
  return $form;
}

/**
 * Submit function for import tab.
 */
function dkan_datastore_import_tab_form_submit($form, &$form_state) {
  $form_state['redirect'] = $form['#redirect'];
  $feed_nid = $form['#feed_nid'];
  if ($form['#count']) {
    $table_name = 'dkan_datastore_' . $feed_nid;
    $result = db_truncate($table_name)->execute();
  }
  feeds_source($form['#importer_id'], $feed_nid)->startImport();
}

/**
 * Render a feeds delete form.
 *
 * Used on both node pages and configuration pages.
 * Therefore $node may be missing.
 */
function dkan_datastore_delete_tab_form($form, &$form_state, $importer_id, $node = NULL) {
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
    $form['actions']['submit']['#value'] = t('Deleting (@progress %)', array('@progress' => number_format(100 * $progress, 0)));
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
