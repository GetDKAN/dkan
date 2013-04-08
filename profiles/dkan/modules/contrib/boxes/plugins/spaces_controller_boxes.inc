<?php

/**
 * Spaces controller for boxes module.
 */
class spaces_controller_boxes extends spaces_controller {

  // Override of load_original_values
  protected function load_original_values($id = NULL) {
    if (empty($this->loaded_all['original'])) {
      // TODO can we always rely on having export.inc loaded?
      if (!isset($id)) {
        $boxes = array();
        foreach (ctools_export_load_object('box') as $box) {
          $boxes[$box->delta] = $box;
        }
        $this->values['original'] = $boxes;
        $this->loaded_all['original'] = TRUE;
      }
      else if (!isset($this->loaded['original'][$id])) {
        if ($box = array_pop(ctools_export_load_object('box', 'names', array($id)))) {
          $this->values['original'][$id] = $box;
          $this->loaded['original'][$id] = TRUE;
        }
      }
    }
  }

  /**
   * Override of the set() method.
   *
   * Remove flags used by to detect state and transflorm to stdClass so that the
   * box configuration can be loaded regardless of whether the plugin is
   * available.
   */
  function set($id, $value) {
    unset($value->new);
    unset($value->export_type);
    $box = new stdClass();
    foreach ($value as $k => $v) {
      $box->$k = $v;
    }
    parent::set($id, $box);
  }
}
