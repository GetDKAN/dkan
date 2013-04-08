<?php
/**
 * @file
 *
 * Is an alter plugin for defaultcontent
 *
 * Handles the exporting and importing of node_reference fields
 */

$plugin = array();

/**
 * Handles the change to the node need for exporting node references
 *
 * Change the node_ref field to refer to the machine_name of the
 * references nodes
 */
function node_reference_export_alter(&$node) {
  $fields = get_ref_fields();
  foreach ($fields as $field_name => $field) {
    if (isset($node->{$field_name})) {
      foreach ($node->{$field_name} as $lang => $items) {
        foreach ($items as $key => $item) {
          if (($nid = $item['nid']) && ($machine_name = defaultcontent_get_default($nid))) {
            $node->{$field_name}[$lang][$key]['machine_name'] = $machine_name;
            unset($node->{$field_name}[$lang][$key]['nid']);
          }
          elseif (isset($item['machine_name'])) {
            //TODO remove
          }
          else {
            unset($node->{$field_name}[$lang][$key]);
          }
        }
      }
    }
  }
}

/**
 * Handles the importing of node_reference fields
 *
 * Change the machine_name of ref nodes to there nid
 */
function node_reference_import_alter(&$node) {
  $fields = get_ref_fields();
  foreach ($fields as $field_name => $field) {
    if (isset($node->{$field_name})) {
      foreach ($node->{$field_name} as $lang => $items) {
        foreach ($items as $key => $item) {
          if (($machine_name = $item['machine_name']) && ($nid = defaultcontent_get_default($machine_name))) {
            $node->{$field_name}[$lang][$key]['nid'] = $nid;
            unset($node->{$field_name}[$lang][$key]['machine_name']);
          }
          elseif (isset($item['nid'])) {
            //TODO remove
          }
          else {
            unset($node->{$field_name}[$lang][$key]);
          }
        }
      }
    }
  }
}

/**
 * Handles a sort to insure that ref nodes are import first
 */
function node_reference_import_sort($a, $b) {
  $fields = get_ref_fields();
  $a_children = array();
  $b_children = array();
  foreach ($fields as $field_name => $field) {
    if (isset($a->{$field_name})) {
      foreach ($a->{$field_name} as $lang => $items) {
        foreach ($items as $key => $item) {
          $a_children[] = $item['machine_name'];
        }
      }
    }
    if (isset($b->{$field_name})) {
      foreach ($b->{$field_name} as $lang => $items) {
        foreach ($items as $key => $item) {
          $b_children[] = $item['machine_name'];
        }
      }
    }
  }
  if (in_array($a->machine_name, $b_children) || empty($a_children)) {
    return -1;
  }
  elseif (in_array($b->machine_name, $a_children) || empty($b_children)) {
    return 1;
  }
  else {
    return 0;
  }
}

/**
 * Helper function to get all node reference fields
 */
function get_ref_fields() {
  $ref_fields = array();
  foreach (field_info_fields() as $id => $field) {
    if ($field['type'] == 'node_reference') {
      $ref_fields[$id] = $field;
    }
  }
  return $ref_fields;
}
