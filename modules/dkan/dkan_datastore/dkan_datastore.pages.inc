<?php

/**
 * @file
 * Callbacks for datastore pages.
 */

// Default to 50MB.
define('MAX_FILE_REMOTE_PROXY_DEFAULT', 1024 * 1024 * 50);

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

    // dkan_datastore_proxy() loads the file locally which has a big memory footprint.
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

function dkan_datastore_pages($form, &$form_state, $node, $method) {
  $form['#node'] = $node;
  $form['#pages_method'] = $method;

  $pages = new \Dkan\Datastore\Pages($node);
  return $pages->{$method}($form,$form_state);
}

function dkan_datastore_pages_submit($form, &$form_state) {
  $node = $form['#node'];

  $method = $form['#pages_method'];
  $submit_method = "{$method}Submit";

  $pages = new \Dkan\Datastore\Pages($node);
  $pages->{$submit_method}($form, $form_state);
}



