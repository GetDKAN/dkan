<?php
/**
 * @file
 * Class defition for data_node_views_handler_field_item_count.
 */

class data_node_views_handler_field_item_count extends views_handler_field {
  function query() {
    return;
  }

  function pre_render($result) {
    $this->items = array();
    foreach ($result as $row) {
      if (!empty($row->{$this->view->base_field})) {
        $nid = $row->{$this->view->base_field};
        $this->items[$nid] = db_query("SELECT COUNT(id) AS count FROM {data_table_node} WHERE nid = :nid", array(':nid' => $nid))->fetchField();
      }
    }
  }

  function render($values) {
    if (!empty($values->{$this->view->base_field})) {
      $nid = $values->{$this->view->base_field};
      if (isset($this->items[$nid])) {
        return check_plain($this->items[$nid]);
      }
    }
    return 0;
  }
}
