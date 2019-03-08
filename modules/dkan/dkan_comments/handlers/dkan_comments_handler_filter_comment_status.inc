<?php
/**
 * @file
 * Definition of dkan_comments_handler_filter_comment_status.
 */

/**
 * Comment Publish Status.
 */
class dkan_comments_handler_filter_comment_status extends views_handler_filter_equality {
  /**
   * The form field to show when this filter is exposed.
   */
  function value_form(&$form, &$form_state) {
    $form['value'] = array(
      '#type' => 'select',
      '#options' => array(
        'published' => 'Published',
        'unpublished' => 'Unpublished',
        'archived' => 'Archived',
      ),
      '#default_value' => $this->value,
    );
  }

  /**
   * Runs the query for this filter.
   */
  public function query() {
    $this->ensure_my_table();
    $this->field = 'entity_id';
    $join_def_match_path = $this->definition;
    $join_def_match_path['table'] = 'field_data_comment_archived';
    $join_def_match_path['field'] = 'entity_id';
    $join_def_match_path['left_table'] = 'comment';
    $join_def_match_path['left_field'] = 'cid';
    $join_def_match_path['type'] = 'LEFT';

    $join_match_path = new views_join();
    $join_match_path->definition = $join_def_match_path;
    $join_match_path->construct();
    $join_match_path->adjusted = true;
    $this->alias = $this->query->add_relationship('field_data_comment_archived', $join_match_path, $this->table, $this->relationship);
    $data_field = "$this->alias.comment_archived_value";

    switch ($this->value) {
      case 'unpublished':
        $this->query->add_where($this->options['group'], 'comment.status', 0, '=');
        $this->query->add_where($this->options['group'], $data_field, NULL, 'IS NULL');

        $this->query->group_operator = 'OR';
        $this->query->add_where(0, $data_field, 0, '=');
        break;
      case 'archived':
        $this->query->add_where($this->options['group'], 'comment.status', 0, '=');
        $this->query->add_where($this->options['group'], $data_field, 1, '=');
        break;
      default:
        // Display approved.
        $this->query->add_where($this->options['group'], 'comment.status', 1, '=');
        break;
    }
  }
}
