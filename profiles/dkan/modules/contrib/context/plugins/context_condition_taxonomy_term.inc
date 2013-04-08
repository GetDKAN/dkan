<?php

/**
 * Expose term views/term forms by vocabulary as a context condition.
 */
class context_condition_taxonomy_term extends context_condition {
  function condition_values() {
    $values = array();
    foreach (taxonomy_get_vocabularies() as $vocab) {
      $values[$vocab->machine_name] = check_plain($vocab->name);
    }
    return $values;
  }

  function options_form($context) {
    $defaults = $this->fetch_from_context($context, 'options');
    return array(
      'term_form' => array(
        '#title' => t('Set on term form'),
        '#type' => 'select',
        '#options' => array(
           0 => t('No'),
           1 => t('Yes'),
           2 => t('Only on term form')
        ),
        '#description' => t('Set this context on term forms'),
        '#default_value' => isset($defaults['term_form']) ? $defaults['term_form'] : TRUE,
      ),
    );
  }

  function execute($term, $op) {
    foreach ($this->get_contexts($term->vocabulary_machine_name) as $context) {
      // Check the node form option.
      $options = $this->fetch_from_context($context, 'options');
      if ($op === 'form') {
        $options = $this->fetch_from_context($context, 'options');
        if (!empty($options['term_form']) && in_array($options['term_form'], array(1, 2))) {
          $this->condition_met($context, $term->vocabulary_machine_name);
        }
      }
      elseif (empty($options['term_form']) || $options['term_form'] != 2) {
        $this->condition_met($context, $term->vocabulary_machine_name);
      }
    }
  }
}
