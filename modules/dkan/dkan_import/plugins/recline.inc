<?php

/**
 * @file
 * Custom elements for importing content.
 */

$plugin = array();

/**
 * Copy of file_export_alter().
 * Replaces get_file_fields() with get_recline_fields().
 */
function recline_export_alter(&$node) {
  $fields = get_recline_fields();
  foreach ($fields as $field_name => $field) {
    if (isset($node->{$field_name})) {
      foreach ($node->{$field_name} as $lang => $items) {
        foreach ($items as $key => $item) {
          $file = drupal_realpath($item['uri']);
          if ($file && ($handle = fopen($file, 'rb')) && (filesize($file) > 0)) {
            $binary = fread($handle, filesize($file));
            $node->{$field_name}[$lang][$key]['file'] = base64_encode($binary);
          }
          $filename = $node->{$field_name}[$lang][$key]['filename'];
          $node->{$field_name}[$lang][$key]['uri'] = 'public://' . $filename;
          unset($node->{$field_name}[$lang][$key]['fid']);
          unset($node->{$field_name}[$lang][$key]['uid']);
          unset($node->{$field_name}[$lang][$key]['timestamp']);
        }
      }
    }
  }
}

/**
 * Copy of file_import_alter().
 * Replaces get_file_fields() with get_recline_fields().
 */
function recline_import_alter(&$node) {
  $fields = get_recline_fields();
  foreach ($fields as $field_name => $field) {
    if (isset($node->{$field_name})) {
      foreach ($node->{$field_name} as $lang => $items) {
        foreach ($items as $key => $item) {
          $file = (object) $item;
          $query = new EntityFieldQuery();
          $fids = db_select('file_managed', 'f')
            ->condition('uri', $file->uri)
            ->fields('f', array('fid'))
            ->execute()
            ->fetchCol();
          if (!empty($fids) && isset($fids[0]) && is_object($fids[0])) {
            file_delete($fid[0]);
          }
          $file = file_save_data($item['file'], $item['uri']);
          $data = base64_decode($item['file']);
          if (!$data) {
            $data = $item['file'];
          }
          $file = file_save_data($data, $item['uri']);
          if ($field['type'] == 'file') {
            $file->display = $item['display'];
            $file->description = $item['description'];
          }
          $file->map = $item['map'];
          $file->grid = $item['grid'];
          $file->graph = $item['graph'];
          $file->timeline = $item['timeline'];
          $file->transform = $item['transform'];
          file_save($file);
          $node->{$field_name}[$lang][$key] = (array) $file;
        }
      }
    }
  }
}

/**
 * Helper function to get all recline fields.
 */
function get_recline_fields() {
  $ref_fields = array();
  foreach (field_info_fields() as $id => $field) {
    if (in_array($field['type'], array('recline_field'))) {
      $ref_fields[$id] = $field;
    }
  }
  return $ref_fields;
}
