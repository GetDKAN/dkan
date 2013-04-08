<?php

/**
 * @file
 * Contains the group filter for field group audience - state.
 */

/**
 * Field group audience - "state" filter handler.
 */
class og_handler_filter_group_audience_state extends views_handler_filter_in_operator {
  function get_value_options() {
    if (!isset($this->value_options)) {
      $this->value_title = t('Group content state');
      $this->value_options = og_group_content_states();
    }
  }

}
