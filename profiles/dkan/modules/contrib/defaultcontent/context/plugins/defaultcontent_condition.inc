<?php

class defaultcontent_condition extends context_condition {
  /**
   * Omit condition values. We will provide a custom input form for our conditions.
   */
  function condition_values() {
    return array();
  }

  /**
   * Condition form.
   */
  function condition_form($context) {
    $form = parent::condition_form($context);
    unset($form['#options']);

    $form['#type'] = 'textarea';
    $form['#default_value'] = implode("\n", $this->fetch_from_context($context, 'values'));
    return $form;
  }

  /**
   * Condition form submit handler.
   */
  function condition_form_submit($values) {
    $parsed = array();
    $items = explode("\n", $values);
    if (!empty($items)) {
      foreach ($items as $v) {
        $v = trim($v);
        if (!empty($v)) {
          $parsed[$v] = $v;
        }
      }
    }
    return $parsed;
  }

  /**
   * Execute.
   */
  function execute($node) {
    if (!isset($node->machine_name)) {
      return;
    }
    if ($this->condition_used()) {
      foreach ($this->get_contexts() as $context) {
        $machine_names = $this->fetch_from_context($context, 'values');

        if (in_array($node->machine_name, $machine_names)) {
          $this->condition_met($context);
        }
      }
    }
  }
}
