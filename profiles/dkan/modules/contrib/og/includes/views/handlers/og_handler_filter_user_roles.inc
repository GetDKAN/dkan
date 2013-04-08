<?php

/**
 * Field handler to provide a list of OG roles of a user.
 */
class og_handler_filter_user_roles extends views_handler_filter_many_to_one {

  function get_value_options() {
    $this->value_options = array();
    // Get all role names.
    $results = db_select('og_role', 'r')
      ->fields('r')
      ->groupBy('r.name')
      ->execute();
    foreach ($results as $result) {
      $this->value_options[$result->name] = $result->name;
    }
  }

  function query() {
    // Get the og_membership table from relations.
    foreach ($this->query->relationships as $alias => $info) {
      if ($info['table'] == 'og_membership') {
        // If there is a og_membership table present. Add some conditions.
        $rel = $this->relationship;
        $this->query->add_where_expression(NULL, $alias . '.gid = ' . $rel . '.gid');
        $this->query->add_where_expression(NULL, $alias . '.group_type = ' . $rel . '.group_type');
        break;
      }
    }

    parent::query();
  }
}
