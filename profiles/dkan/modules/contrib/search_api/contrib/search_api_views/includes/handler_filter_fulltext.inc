<?php

/**
 * Views filter handler class for handling fulltext fields.
 */
class SearchApiViewsHandlerFilterFulltext extends SearchApiViewsHandlerFilterText {

  /**
   * Displays the operator form, adding a description.
   */
  public function show_operator_form(&$form, &$form_state) {
    $this->operator_form($form, $form_state);
    $form['operator']['#description'] = t('This operator is only useful when using \'Search keys\'.');
  }

  /**
   * Provide a list of options for the operator form.
   */
  public function operator_options() {
    return array(
      'AND' => t('Contains all of these words'),
      'OR' => t('Contains any of these words'),
      'NOT' => t('Contains none of these words'),
    );
  }

  /**
   * Specify the options this filter uses.
   */
  public function option_definition() {
    $options = parent::option_definition();

    $options['operator']['default'] = 'AND';

    $options['mode'] = array('default' => 'keys');
    $options['fields'] = array('default' => array());

    return $options;
  }

  /**
   * Extend the options form a bit.
   */
  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['mode'] = array(
      '#title' => t('Use as'),
      '#type' => 'radios',
      '#options' => array(
        'keys' => t('Search keys – multiple words will be split and the filter will influence relevance. You can change how search keys are parsed under "Advanced" > "Query settings".'),
        'filter' => t("Search filter – use as a single phrase that restricts the result set but doesn't influence relevance."),
      ),
      '#default_value' => $this->options['mode'],
    );

    $fields = $this->getFulltextFields();
    if (!empty($fields)) {
      $form['fields'] = array(
        '#type' => 'select',
        '#title' => t('Searched fields'),
        '#description' => t('Select the fields that will be searched. If no fields are selected, all available fulltext fields will be searched.'),
        '#options' => $fields,
        '#size' => min(4, count($fields)),
        '#multiple' => TRUE,
        '#default_value' => $this->options['fields'],
      );
    }
    else {
      $form['fields'] = array(
        '#type' => 'value',
        '#value' => array(),
      );
    }
    if (isset($form['expose'])) {
      $form['expose']['#weight'] = -5;
    }
  }

  /**
   * Add this filter to the query.
   */
  public function query() {
    while (is_array($this->value)) {
      $this->value = $this->value ? reset($this->value) : '';
    }
    // Catch empty strings entered by the user, but not "0".
    if ($this->value === '') {
      return;
    }
    $fields = $this->options['fields'];
    $fields = $fields ? $fields : array_keys($this->getFulltextFields());

    // If something already specifically set different fields, we silently fall
    // back to mere filtering.
    $filter = $this->options['mode'] == 'filter';
    if (!$filter) {
      $old = $this->query->getFields();
      $filter = $old && (array_diff($old, $fields) || array_diff($fields, $old));
    }

    if ($filter) {
      $filter = $this->query->createFilter('OR');
      foreach ($fields as $field) {
        $filter->condition($field, $this->value, $this->operator);
      }
      $this->query->filter($filter);
      return;
    }

    // If the operator was set to OR, set it as the conjunction. (AND is set by
    // default.)
    if ($this->operator === 'OR') {
      $this->query->setOption('conjunction', $this->operator);
    }

    $this->query->fields($fields);
    $old = $this->query->getOriginalKeys();
    $this->query->keys($this->value);
    if ($this->operator == 'NOT') {
      $keys = &$this->query->getKeys();
      if (is_array($keys)) {
        $keys['#negation'] = TRUE;
      }
      else {
        // We can't know how negation is expressed in the server's syntax.
      }
    }
    if ($old) {
      $keys = &$this->query->getKeys();
      if (is_array($keys)) {
        $keys[] = $old;
      }
      elseif (is_array($old)) {
        // We don't support such nonsense.
      }
      else {
        $keys = "($old) ($keys)";
      }
    }
  }

  /**
   * Helper method to get an option list of all available fulltext fields.
   */
  protected function getFulltextFields() {
    $fields = array();
    $index = search_api_index_load(substr($this->table, 17));
    if (!empty($index->options['fields'])) {
      $f = $index->getFields();
      foreach ($index->getFulltextFields() as $name) {
        $fields[$name] = $f[$name]['name'];
      }
    }
    return $fields;
  }

}
