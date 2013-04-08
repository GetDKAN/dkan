<?php
/**
 * @file
 * Class defition for data_entity_views_handler_field_edit_link.
 */

/**
 * Provides a field handler that links to a data table entity's edit form.
 */
class data_entity_views_handler_field_edit_link extends views_handler_field {
  function query() {
    // Fake field, do nothing.
  }

  /**
   * Grant access to the field if the user can access the page it links to.
   */
  function access() {
    $table = $this->view->base_table;
    return user_access('edit data in table ' . $table);
  }

  function render($values) {
    $table = $this->view->base_table;
    $base_field = $this->view->base_field;

    $this->options['alter']['make_link'] = TRUE;
    $this->options['alter']['path'] = 'admin/content/data/entity/' . $table . '/' . $values->$base_field;
    $this->options['alter']['query'] = drupal_get_destination();

    return t('edit item');
  }
}
