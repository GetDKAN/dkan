<?php
/**
 * @file
 * Class defition for data_node_views_handler_field_add_remove.
 */

class data_node_views_handler_field_add_remove extends views_handler_field {
  protected $table_name, $id;

  function allow_advanced_render() {
    return FALSE;
  }

  function query() {
    $this->ensure_my_table();

    // Add the id field for this table to the query.
    $table = data_get_table($this->view->base_table);
    $meta = $table->get('meta');
    if ($meta['data_node']['content_type']) {
      // Copy table name, we will use it in render().
      $this->table_name = $table->get('name');
      // Add id field to query.
      $this->id = $this->query->add_field($this->table_alias, $meta['data_node']['id']);
    }
    $this->add_additional_fields();
  }

  function render($values) {
    if (user_access('manage data relations')) {
      $id = $values->{$this->id};
      $table = data_get_table($this->table_name);
      $node_list = data_node_get_nids($table, $id);
      if ($nid = data_node_get_active($this->table_name)) {
        return in_array($nid, $node_list) ? data_node_render_remove_link($table, $id, $nid) : data_node_render_add_link($table, $id, $nid);
      }
      else {
        return data_node_render_placeholder_link($table, $id);
      }
    }
  }
}
