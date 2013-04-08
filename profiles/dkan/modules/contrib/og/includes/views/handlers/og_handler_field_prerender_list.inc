<?php

/**
 * Field handler to provide a list of group permissions.
 */
class og_handler_field_prerender_list extends views_handler_field_prerender_list {
  /**
   * We override this function as $this->items is an array keyed by:
   * - Group ID
   * -- User ID
   * --- Permission/ Role
   */
  function advanced_render($values) {
    if ($this->allow_advanced_render() && method_exists($this, 'render_item')) {
      $raw_items = $this->get_items($values);
    }
    else {
      $this->last_render = $value = $this->render($values);
      $this->original_value = $value;
    }

    if ($this->allow_advanced_render()) {
      $tokens = NULL;
      if (method_exists($this, 'render_item')) {
        $items = array();
        // Override parent logic -- make sure we get an item per group, per
        // user, and not try to get the roles per group only.
        $uid = $values->{$this->aliases['uid']};
        if (!empty($raw_items[$uid])) {
          foreach ($raw_items[$uid] as $count => $item) {
            $this->last_render = $this->render_item($count, $item);
            $this->original_value = $this->last_render;

            $alter = $item + $this->options['alter'];
            $alter['phase'] = VIEWS_HANDLER_RENDER_TEXT_PHASE_SINGLE_ITEM;
            $items[] = $this->render_text($alter);
          }
        }

        $value = $this->render_items($items);
      }
      else {
        $value = $this->render_text($this->options['alter']);
      }

      // This happens here so that render_as_link can get the unaltered value of
      // this field as a token rather than the altered value.
      $this->last_render = $value;
    }

    if (empty($this->last_render)) {
      if (($this->last_render !== 0 && $this->last_render !== '0') || !empty($this->options['empty_zero'])) {
        $this->last_render = $this->options['empty'];
      }
    }

    return $this->last_render;
  }
}
