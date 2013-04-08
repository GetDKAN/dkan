<?php

/**
 * Filter handler that filters results based on the completion status of a multistep node.
 */
class multistep_views_handler_filter_status extends views_handler_filter {
  function get_value_options() {
    if (!isset($this->value_options)) {
      $this->value_options = array('complete' => t('Complete'), 'incomplete' => t('Incomplete'));
    }
  }

  function admin_summary() {
    return 'is ' . check_plain((string) ucfirst($this->value));
  }

  function operator_form(&$form, &$form_state) {
    $form['operator'] = array();
  }

  function value_form(&$form, &$form_state) {
    if (empty($this->value_options)) {
      // Initialize the array of possible values for this filter.
      $this->get_value_options();
    }
    if (!empty($form_state['exposed'])) {
      // Exposed filter: use a select box to save space.
      $filter_form_type = 'select';
    }
    else {
      // Configuring a filter: use radios for clarity.
      $filter_form_type = 'radios';
    }
    $form['value'] = array(
      '#type' => $filter_form_type,
      '#title' => $this->value_value,
      '#options' => $this->value_options,
      '#default_value' => $this->value,
    );
    if (!empty($this->options['exposed'])) {
      $identifier = $this->options['expose']['identifier'];
      if (!isset($form_state['input'][$identifier])) {
        $form_state['input'][$identifier] = $this->value;
      }
      // If we're configuring an exposed filter, add an <Any> option.
      $any_label = variable_get('views_exposed_filter_any_label', 'old_any') == 'old_any' ? t('<Any>') : t('- Any -');
      if ($form['value']['#type'] != 'select') {
        $any_label = check_plain($any_label);
      }
      $form['value']['#options'] = array('All' => $any_label) + $form['value']['#options'];
    }
  }

  function query() {
    $this->ensure_my_table();
    $value = $this->value;
    if ($this->options['exposed']) {
      $value = $this->value[0];
    }
    switch ($value) {
      case 'incomplete':
        $this->query->add_where(0, "multistep.status = 'unsubmitted'");
        break;
      case 'complete':
        $this->query->add_where(0, "multistep.status IS NULL");
        break;
    }
    $this->query->add_groupby('nid');
  }
}
