<?php

/**
 * A handler to run a field through check_markup.
 */
class views_handler_field_data_markup extends views_handler_field {

  function construct() {
    parent::construct();
    $this->format = $this->definition['format'];
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $options = array('default' => t('<Default>'));
    $result = db_query("SELECT format, name FROM {filter_formats}");
    while ($row = db_fetch_object($result)) {
      $options[$row->format] = $row->name;
    }
    $form['format'] = array(
      '#type' => 'select',
      '#options' => $options,
      '#title' => t('Format'),
      '#default_value' => isset($this->options['format']) ? $this->options['format'] : '',
      '#description' => t('Choose the filter format to use for this field.'),
    );
  }

  function render($values) {
    $value = $values->{$this->field_alias};
    if ($value) {
      $format = is_numeric($this->options['format']) ? $this->options['format'] : variable_get('filter_default_format', 1);
      return check_markup($value, $format, $langcode = '' /* TODO Set this variable. */, FALSE);
    }
  }
}
