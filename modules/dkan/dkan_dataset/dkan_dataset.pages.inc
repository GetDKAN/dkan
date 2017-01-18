<?php

/**
 * @file
 * Pages for the datastore.
 */

/**
 * Callback for back link.
 */
function dkan_dataset_back($node) {
  $node_wrapper = entity_metadata_wrapper('node', $node);
  $ref = $node_wrapper->field_dataset_ref->value();

  if (isset($ref[0])) {
    drupal_goto('node/' . $ref[0]->nid);
  }
  else {
    drupal_set_message(t('There is no dataset associated with this resource.'));
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
 * Callback for 'Download Dataset'.
 */
function dkan_dataset_download($node) {
  $node = entity_metadata_wrapper('node', $node);
  $resources = $node->field_resources->value();
  if (isset($resources)) {
    $files = array();
    foreach ($resources as $resource) {
      // Node could be empty if it has been deleted.
      if (!$resource || !node_access('view', $resource)) {
        continue;
      }
      $resource_wrapper = entity_metadata_wrapper('node', $resource);
      if (isset($resource->field_upload[LANGUAGE_NONE])) {
        $files[] = drupal_realpath($resource->field_upload[LANGUAGE_NONE][0]['uri']);
      }
    }
    _dkan_dataset_zip($files, $node);
  }
}

/**
 * Zip files.
 */
function _dkan_dataset_zip($files, $node) {
  $zip = new ZipArchive();
  $file_path = tempnam(file_directory_temp(), 'zip');
  $zip_open = $zip->open($file_path, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
  if ($zip_open === TRUE) {
    foreach ($files as $file) {
      if (file_exists($file)) {
        if (!$zip->addFile(realpath($file), basename($file))) {
          drupal_set_message(t('!file could not be added to Zip.', array('!file' => $file)), 'error');
        }
      }
      else {
        drupal_set_message('!file not found.', array('!file' => $file), 'error');
      }
    }
    $zip->close();
    $filename = _dkan_dataset_zip_filename($node->title->value());
    $headers = array(
      'Content-type' => 'application/zip',
      'Content-Disposition' => 'attachment; filename="' . $filename . '"',
      'Content-Length' => filesize($file_path),
    );
    file_transfer('temporary://' . basename($file_path), $headers);
  }
  else {
    drupal_set_message(t('Unable to create file !filename.', array('!filename' => check_plain($filename))), 'error');
    drupal_access_denied();
    return FALSE;
  }
}

/**
 * Generate a filename for the zip file.
 */
function _dkan_dataset_zip_filename($title) {
  $filename = strtr(drupal_strtolower($title), array(
    ' ' => '-',
    '_' => '-',
    '[' => '-',
    ']' => '',
  ));
  $filename = preg_replace('/[^A-Za-z0-9\-_]/', '', $filename);
  $filename = preg_replace('/\-+/', '-', $filename);
  return $filename . '-All-' . date('Y-m-d_Hi') . '.zip';
}
