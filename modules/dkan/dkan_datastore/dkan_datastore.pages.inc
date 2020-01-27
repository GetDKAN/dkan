<?php

/**
 * @file
 * Callbacks for datastore pages.
 */

use Dkan\Datastore\Resource;
use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Page\Page;

// Default to 50MB.
define('MAX_FILE_REMOTE_PROXY_DEFAULT', 1024 * 1024 * 50);

/**
 * Proxy remote resources.
 */
function dkan_datastore_proxy($node) {
  $node_wrapper = entity_metadata_wrapper('node', $node);
  $remote_file = $node_wrapper->field_link_remote_file->value();
  $uri = $remote_file['uri'];

  return drupal_goto($uri);
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
    dkan_datastore_proxy($node);
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
 * Callback for 'Add Resouce' tab.
 */
function dkan_datastore_add_resource($node) {
  drupal_goto('node/add/resource', array('query' => array('dataset' => $node->nid)));
}

/**
 * Datastore forms menu callback.
 */
function dkan_datastore_pages($form, &$form_state, $node) {
  $form['#node'] = $node;
  $page = new Page($node, $form, $form_state);
  return $page->get();
}

/**
 * Submit handler.
 */
function dkan_datastore_pages_submit($form, &$form_state) {
  $page = new Page($form['#node'], $form, $form_state);
  $page->submit();
}

/**
 * Submit handler.
 */
function dkan_datastore_drop_submit($form, &$form_state) {
  $form_state['storage']['drop'] = TRUE;
  $form_state['storage']['original_form'] = $form_state['values'];
  $form_state['rebuild'] = TRUE;
}

/**
 * Submit handler.
 */
function dkan_datastore_stop_submit($form, &$form_state) {
  drupal_set_message(t("Importing will be stopped shortly."));
  variable_set('dkan_datastore_interrupt', 1);
}

/**
 * Submit handler.
 */
function dkan_datastore_go_to_paused_state_submit($form, &$form_state) {
  $node = $form['#node'];
  try {
    /* @var $manager \Dkan\Datastore\Manager\ManagerInterface */
    $manager = (new Factory(Resource::createFromDrupalNode($node)))->get();
    $manager->goToPausedState();
  }
  catch (\Exception $e) {
    drupal_set_message($e->getMessage());
  }
}
