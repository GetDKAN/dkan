<?php
/**
 * @file
 * Class defition for data_node_views_handler_field_node_list.
 */

class data_node_views_handler_field_node_list extends views_handler_field {
  function allow_advanced_render() {
    return FALSE;
  }

  function query() {
    return;
  }

  function render($values) {
    if (user_access('manage data relations')) {
      $id = $values->{$this->view->base_field};
      $table = data_get_table($this->view->base_table);
      return data_node_render_labels($table, $id);
    }
  }
}
