<?php

/**
 * @file
 * Callbacks for datastore pages.
 */

use Dkan\Datastore\Form\Drop;
use Dkan\Datastore\Form\Import;
use Dkan\Datastore\Form\Delete;

// Default to 50MB.
define('MAX_FILE_REMOTE_PROXY_DEFAULT', 1024 * 1024 * 50);

/**
 * Callback for Data API instructions.
 */
function dkan_datastore_datastore_api($node) {
  $output = "";
  $redirect_path = NULL;

  $status = dkan_datastore_status($node);

  switch ($status) {
    case DKAN_DATASTORE_EXISTS:
      /* @var $datastore DkanDatastore */
      $datastore = dkan_datastore_go($node->uuid);

      $output .= '<h2>' . t('DKAN Datastore API') . '</h2>';

      if ($datastore_id = $datastore->getId()) {
        if (!$datastore->exists()) {
          $output .= t('This resources has a file that has not been added to the datastore.');
        }
        else {
          $progress = $datastore->getImportProgress();
          if ($progress != 1.0) {
            $output .= t('This data source has not yet completed importing. Import is at @progress%. The API for this data source will be available upon the completion of the import process.', array('@ progress' => number_format(100 * $progress, 0)));
          }
          else {
            $output .= t('Access resource data via a web API with powerful query support.');
            $output .= '<h3>' . t('Resource ID') . '</h3>';
            $output .= t("The Resource ID for this resource is %id", array('%id' => $node->uuid));
            $output .= '<h3>' . t('Example Query') . '</h3>';
            $url = url("api/action/datastore/search.json", array(
              'query' => array(
                'resource_id' => $node->uuid,
                'limit' => 5,
              ),
              'absolute' => TRUE,
            ));;
            $output .= '<p>' . l($url, $url) . '</p>';
            $output .= '<p>' . t('Query this datastore and return first five results') . '</p>';
            $output .= '<h3>' . t('Documentation') . '</h3>';
            $output .= '<p>' . t('See DKAN API documentation for more details: <a href="http://docs.getdkan.com/docs/dkan-documentation/dkan-api/datastore-api">DKAN Datastore API</a>') . '</p>';
          }
        }
      }
      else {
        $output .= t('No files have been added to the datastore.');
      }
      break;

    case DKAN_DATASTORE_FILE_EXISTS:
      drupal_set_message(t('You need to add your file to the datastore in order to use the DATA API'));
      $redirect_path = 'node/' . $node->nid . '/datastore';
      break;

    default:
      drupal_set_message(t('This resource can not be added to the datastore'));
      $redirect_path = 'node/' . $node->nid;
      break;
  }

  if ($redirect_path) {
    drupal_goto($redirect_path);
  }
  else {
    return $output;
  }
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

/**
 * Render a datastore import form on node/id/import pages.
 */
function dkan_datastore_import_tab_form($form, &$form_state, $node) {
  module_load_include("php", "dkan_datastore", "src/Form/Import");

  /* @var $datastore DkanDatastore */
  $datastore = dkan_datastore_go($node->uuid);
  $dkan_form = new Import($datastore);
  $form = $dkan_form->toArray($form_state);
  $form['#datastore'] = $datastore;
  return $form;
}

/**
 * Submit function for import tab.
 */
function dkan_datastore_import_tab_form_submit($form, &$form_state) {
  module_load_include("php", "dkan_datastore", "src/Form/Import");

  $datastore = $form['#datastore'];
  $dkan_form = new Import($datastore);
  $dkan_form->submitHandler($form_state);
}

/**
 * Render a datastore delete form.
 *
 * Used on both node pages and configuration pages.
 * Therefore $node may be missing.
 */
function dkan_datastore_delete_tab_form($form, &$form_state, $importer_id, $node = NULL) {
  module_load_include("php", "dkan_datastore", "src/Form/Delete");

  $datastore = dkan_datastore_go($node->uuid);
  $dkan_form = new Delete($datastore);
  $form = $dkan_form->toArray($form_state);
  $form['#datastore'] = $datastore;
  return $form;
}

/**
 * Submit function for import tab.
 */
function dkan_datastore_delete_tab_form_submit($form, &$form_state) {
  module_load_include("php", "dkan_datastore", "src/Form/Import");

  $datastore = $form['#datastore'];
  $dkan_form = new Delete($datastore);
  $form_state['redirect'] = $form['#redirect'];
  $dkan_form->submitHandler($form_state);
}

/**
 * Render a datastore drop form on node/id/drop pages.
 */
function dkan_datastore_drop_tab_form($form, &$form_state, $node) {
  module_load_include("php", "dkan_datastore", "src/Form/Drop");

  /* @var $datastore DkanDatastore */
  $datastore = dkan_datastore_go($node->uuid);
  $dkan_form = new Drop($datastore);
  $form = $dkan_form->toArray($form_state);
  $form['#datastore'] = $datastore;
  return $form;
}

/**
 * Submit function for drop tab.
 */
function dkan_datastore_drop_tab_form_submit($form, &$form_state) {
  module_load_include("php", "dkan_datastore", "src/Form/Drop");

  /* @var $datastore DkanDatastore */
  $datastore = $form['#datastore'];
  $dkan_form = new Drop($datastore);
  $dkan_form->submitHandler($form_state);
  $form_state['redirect'] = $form['#redirect'];
}
