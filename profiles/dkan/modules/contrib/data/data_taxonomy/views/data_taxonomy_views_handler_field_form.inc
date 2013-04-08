<?php

/**
 * Provide a form to associate data records with taxonomy terms.
 */
class data_taxonomy_views_handler_field_form extends views_handler_field {
  protected $id;

  /**
   * Describe provided options and default values.
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['vocabularies'] = array('default' => array());
    $options['path'] = array('default' => '');
    return $options;
  }

  /**
   * Suppress other options and provide a selection of vocabularies for which
   * tagging forms will be displayed.
   */
  function options_form(&$form, &$form_state) {
    $options = array();
    $table = data_get_table($this->table);
    $meta = $table->get('meta');
    if (!empty($meta['data_taxonomy']['vocabularies'])) {
      foreach ($meta['data_taxonomy']['vocabularies'] as $vid) {
        $options[$vid] = data_taxonomy_get_vocabulary($vid)->name;
      }
    }
    if (!empty($options)) {
      $form['vocabularies'] = array(
        '#title' => t('Enabled vocabularies'),
        '#description' => t('Display a tagging form for each of the selected vocabularies. Only vocabularies that are enabled for this table are available. Edit this table\'s definition in admin/build/data to enable vocabularies.'),
        '#type' => 'checkboxes',
        '#options' => $options,
        '#default_value' => $this->options['vocabularies'],
      );
    }
    $form['path'] = array(
      '#type' => 'textfield',
      '#title' => t('Term path'),
      '#default_value' => $this->options['path'],
      '#description' => t('Path to link terms with. Use "%" to represent arguments of the current view, use !tid to designate where the term id should be placed, use !term to designate where the term name should be placed. For example "data/%/!tid/%"'),
    );
  }

  /**
   * Add field on which we join.
   */
  function query() {
    $this->ensure_my_table();

    // Add the id field for this table to the query.
    $table = data_get_table($this->table);
    $meta = $table->get('meta');
    if (!empty($meta['data_taxonomy']['vocabularies']) && isset($meta['data_taxonomy']['id'])) {
      $this->id = $this->query->add_field($this->table_alias, $meta['data_taxonomy']['id']);
    }
    $this->add_additional_fields();
  }

  /**
   * Render form.
   */
  function render($values) {
    $output = '';
    $table = data_get_table($this->table);
    $meta = $table->get('meta');
    if (!empty($meta['data_taxonomy']['vocabularies'])) {
      foreach ($meta['data_taxonomy']['vocabularies'] as $vid) {
        if (!empty($this->options['vocabularies'][$vid])) {
          $vocabulary = data_taxonomy_get_vocabulary($vid);
          $output .= drupal_get_form('data_taxonomy_tagging_form', $vocabulary->vid, $values->{$this->id}, $this->table, $this->options['path'], is_array($this->view->args) ? $this->view->args : array());
        }
      }
    }
    return $output;
  }

  /**
   * Omit label output.
   */
  function label() {
    return '';
  }
}
