<?php

/**
 * @file
 * Contains the group field handler for field group audience - state.
 */

/**
 * Field group audience - "state" field handler.
 */
class og_handler_field_group_audience_state extends views_handler_field {

  function render($values) {
    $states = og_group_content_states();
    return $states[$values->{$this->field_alias}];
  }
}
